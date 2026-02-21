<?php

require_once __DIR__ . '/../src/Converter.php';

use MmNames\Converter;

$converter = new Converter();

$names = [
    "နိုင်ဝင်းထွန်း",
    "အောင်ဆန်းစုကြည်",
    "ကျော်စွာ",
    "သတ္တိ",
    "သင်္ဘော",
    "ကိုဟိန်းဇော်",
    "မင်းခန့်",
    "ခင်မောင်သိန်း"
];

foreach ($names as $name) {
    // အသံထွက် syllable များအဖြစ် ခွဲထုတ်ခြင်း
    $syllablesArr = $converter->splitIntoSyllables($name);
    $syllablesStr = implode(' ', $syllablesArr);

    // အင်္ဂလိပ်စာလုံးပေါင်းသို့ ပြောင်းလဲခြင်း
    $englishName = $converter->convert($name);

    // ရလဒ်ကို ပုံစံချ၍ ထုတ်ပေးခြင်း
    echo "{$name} -> {$syllablesStr} -> {$englishName}\n";
}