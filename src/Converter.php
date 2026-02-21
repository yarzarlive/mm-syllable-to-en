<?php

namespace MmNames;

use InvalidArgumentException;
use RuntimeException;

class Converter
{
    /**
     * @var array<string, string>
     */
    private array $dictionary = [];

    /**
     * @var array<string, bool>
     */
    private array $loadedFiles = [];

    /**
     * Converter constructor.
     *
     * @param string|null $dataDir The directory containing the unbundled TSV lists.
     *                             If null, the default data/ directory bundled with the library will be used.
     * @throws RuntimeException
     */
    public function __construct(private ?string $dataDir = null)
    {
        $this->dataDir = $dataDir ? rtrim($dataDir, '/') : dirname(__DIR__) . '/data';

        if (!is_dir($this->dataDir)) {
            throw new RuntimeException("Data directory not found or invalid: {$this->dataDir}");
        }
    }

    /**
     * Converts a Burmese name string to standardized English spelling.
     *
     * @param string $burmeseName
     * @return string
     */
    public function convert(string $burmeseName): string
    {
        $syllables = $this->splitIntoSyllables($burmeseName);
        $englishParts = [];

        foreach ($syllables as $syllable) {
            $syllable = trim($syllable);
            if ($syllable === '') {
                continue;
            }

            // Prepare sub-syllables based on the presence of stacked consonants (Patsint ္)
            $subSyllables = [];
            if (strpos($syllable, '္') !== false) {
                if (preg_match('/^(.*?)(္)([က-အ])(.*)$/u', $syllable, $matches)) {
                    $p1 = $matches[1];
                    // If the first part already ends with an Asat (์) before the Patsint (like သင်္ဘော)
                    if (mb_substr($p1, -1, 1, 'UTF-8') === '်') {
                        $part1 = mb_substr($p1, 0, mb_strlen($p1, 'UTF-8') - 1, 'UTF-8') . 'င်'; // Standardize the င် block if an Asat is explicitly detected before Patsint. Actually wait, let's keep original $p1 which has Asat.
                        $part1 = $p1;
                    } else {
                        $part1 = $p1 . '်';
                    }
                    $part2 = $matches[3] . $matches[4];
                    $subSyllables = [$part1, $part2];
                } else {
                    $subSyllables = [$syllable];
                }
            } else {
                $subSyllables = [$syllable];
            }

            $currentEnglishParts = [];
            foreach ($subSyllables as $index => $subSyllable) {
                // Determine consonant and ensure TSV dictionary is loaded into memory
                $consonant = $this->getSyllableConsonant($subSyllable);
                if ($consonant !== '') {
                    $this->loadDataForConsonant($consonant);
                }

                // Look up in dictionary, fallback to the original if not found
                $translated = $this->dictionary[$subSyllable] ?? $subSyllable;

                if ($index > 0) {
                    $translated = strtolower($translated);
                }

                $currentEnglishParts[] = $translated;
            }

            // Combine parts back into string for this actual syllable and append to total results.
            $englishParts[] = implode('', $currentEnglishParts);
        }

        return implode(' ', $englishParts);
    }

    /**
     * Determines the primary consonant file to load for a syllable.
     * Maps independent vowels to 'အ'.
     *
     * @param string $syllable
     * @return string
     */
    private function getSyllableConsonant(string $syllable): string
    {
        $firstChar = mb_substr($syllable, 0, 1, 'UTF-8');

        return match ($firstChar) {
            'ဣ', 'ဤ', 'ဥ', 'ဦ', 'ဧ', 'ဨ', 'ဩ', 'ဪ' => 'အ',
            default => $firstChar,
        };
    }

    /**
     * Loads the TSV dictionary file for a specific consonant into the internal array.
     *
     * @param string $consonant
     * @throws RuntimeException
     */
    private function loadDataForConsonant(string $consonant): void
    {
        // Skip if already loaded
        if (isset($this->loadedFiles[$consonant])) {
            return;
        }

        $filePath = $this->dataDir . '/' . $consonant . '.tsv';

        // If file doesn't exist, simply mark as loaded to avoid spamming file_exists checks
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->loadedFiles[$consonant] = true;
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            foreach ($lines as $line) {
                $parts = explode("\t", $line);
                if (count($parts) >= 2) {
                    $burmese = trim($parts[0]);
                    $english = trim($parts[1]);
                    if ($burmese !== '') {
                        $this->dictionary[$burmese] = $english;
                    }
                }
            }
        }

        $this->loadedFiles[$consonant] = true;
    }

    /**
     * Splits a Burmese word into an array of syllables using regular expressions.
     * Based on standard Myanmar syllable segmentation rules.
     *
     * @param string $text
     * @return string[]
     */
    public function splitIntoSyllables(string $text): array
    {
        // Remove whitespace and clean up input
        $text = preg_replace('/\s+/u', '', $text) ?? '';

        // Use zero-width assertion to find syllabus boundaries.
        // It matches the position right before a consonant that starts a new syllable.
        // i.e., not preceded by Patsint (္) and followed by a consonant that is not followed by Asat (်) or stacked (္).
        $pattern = '/(?<!္)(?=[က-အ](?![်္]))/u';

        // Split the string into an array of syllables.
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        return $parts ?: [];
    }
}