<?php

namespace MmNames\Tests;

use MmNames\Converter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConverterTest extends TestCase
{
    private Converter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        // Uses the default data file
        $this->converter = new Converter();
    }

    public function testSyllableSplitting()
    {
        $input = "နိုင်ဝင်းထွန်း";
        $expected = ["နိုင်", "ဝင်း", "ထွန်း"];
        $this->assertEquals($expected, $this->converter->splitIntoSyllables($input));

        $input = "အောင်ဆန်းစုကြည်";
        $expected = ["အောင်", "ဆန်း", "စု", "ကြည်"];
        $this->assertEquals($expected, $this->converter->splitIntoSyllables($input));

        $input = "ကျော်စွာ";
        $expected = ["ကျော်", "စွာ"];
        $this->assertEquals($expected, $this->converter->splitIntoSyllables($input));

        $input = "သင်္ဘော";
        // Check if stacked consonant handles correctly
        $expected = ["သင်္ဘော"];
        $this->assertEquals($expected, $this->converter->splitIntoSyllables($input));

        $input = "သတ္တိ";
        $expected = ["သတ္တိ"];
        $this->assertEquals($expected, $this->converter->splitIntoSyllables($input));
    }

    public function testConversion()
    {
        $this->assertEquals("Naing Win Htwon", $this->converter->convert("နိုင်ဝင်းထွန်း"));
        $this->assertEquals("Aung Hsan Su Kyi", $this->converter->convert("အောင်ဆန်းစုကြည်"));
        $this->assertEquals("Kyaw Swa", $this->converter->convert("ကျော်စွာ"));
    }

    public function testUnknownSyllableFallback()
    {
        // "မblah" ("Ma" and an English word) has an unknown syllable, so it should output the original or handled as per dictionary.
        $this->assertEquals("မblah", $this->converter->convert("မblah"));
    }

    public function testCustomDataFile()
    {
        $customDir = __DIR__ . '/test_custom_data';
        if (!is_dir($customDir)) {
            mkdir($customDir);
        }

        $customFile = $customDir . '/စ.tsv';
        file_put_contents($customFile, "စမ်း\tCustom San");

        try {
            $customConverter = new Converter($customDir);
            $this->assertEquals("Custom San", $customConverter->convert("စမ်း"));
        }
        finally {
            $files = glob($customDir . '/*');
            foreach ($files as $file) {
                if (is_file($file))
                    unlink($file);
            }
            rmdir($customDir);
        }
    }

    public function testFileNotFoundException()
    {
        $this->expectException(RuntimeException::class);
        new Converter(__DIR__ . '/does_not_exist_dir');
    }
}