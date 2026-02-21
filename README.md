# mm-syllable-to-en

A lightweight PHP library designed to convert Burmese names into standardized English spellings. It accurately parses Burmese syllables and maps them to English romanizations using optimized, per-consonant `.tsv` dictionary files.

**Note:** This library is built for performance. It dynamically loads only the required dictionary files for specific consonants into memory, keeping the footprint minimal. Independent vowels (ဣ, ဤ, ဥ, ဦ, ဧ, ဨ, ဩ, ဪ) are automatically mapped to the default `အ.tsv` definitions.

## Requirements

* **PHP:** 8.0 or higher.
* **Extensions:** `mbstring` PHP extension.

## Installation

### 1. Via Composer (Recommended)

While the package is being prepared for the main Packagist repository, you can install it directly from GitHub by adding the repository to your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/yarzarlive/mm-syllable-to-en.php"
    }
],
"require": {
    "yarzarlive/mm-syllable-to-en": "dev-main"
}
```

Then run:
```bash
composer update
```

### 2. Manual Installation

If you prefer not to use Composer, you can integrate the library manually:

1.  **Download the source:** Clone the repository or download and extract the `.zip` file from GitHub.
    ```bash
    git clone https://github.com/yarzarlive/mm-syllable-to-en.php
    ```
2.  **Include the Converter:** Manually `require` the `Converter.php` class in your project:
    ```php
    require_once __DIR__ . '/path/to/mm-syllable-to-en/src/Converter.php';
    ```

## Usage

### Standard Usage (with Autoloader)

Once installed, instantiate the `Converter` class. By default, it uses the bundled `./data` directory for translations.

```php
use MmNames\Converter;

// Initialize the converter
$converter = new Converter();

// Convert Burmese names
echo $converter->convert("ခင်မောင်သိန်းထွန်းဝင်း"); // Outputs: "Khin Maung Thein Htun Win"
echo $converter->convert("တာရာပွကြီး");     // Outputs: "Tar Ra Pwa Gyi"
```

### Using a Custom Dictionary Directory

You can provide a custom path to your own `.tsv` dictionary files through the constructor:

```php
$converter = new Converter('/var/www/my-custom-dictionaries');
echo $converter->convert("ကျော်");
```

## Running Tests

To run the provided PHPUnit testing suite, install the development dependencies and execute `phpunit`:

```bash
composer install
vendor/bin/phpunit tests/ConverterTest.php
```

## License

This project is open-source and released under the [MIT License](LICENSE).