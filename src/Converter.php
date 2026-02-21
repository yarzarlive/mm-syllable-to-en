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

            $consonant = $this->getSyllableConsonant($syllable);

            if ($consonant !== '') {
                $this->loadDataForConsonant($consonant);
            }

            // Look up in dictionary, fallback to the original if not found.
            // Depending on requirements, we could just drop or keep the original. 
            // We'll keep the original if unknown so data isn't lost.
            $englishParts[] = $this->dictionary[$syllable] ?? $syllable;
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