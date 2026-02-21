<?php

require_once __DIR__ . '/../src/Converter.php';

use MmNames\Converter;

$converter = new Converter();

$names = [
    "နိုင်ဝင်းထွန်း",
    "ခင်မောင်သိန်းထွန်းဝင်း",
    "ကျော်စွာ",
    "သတ္တိ",
    "သင်္ဘော",
    "ကိုဟိန်းဇော်",
    "မင်းခန့်"
];

foreach ($names as $name) {
    echo $name . " => " . $converter->convert($name) . "\n";
}