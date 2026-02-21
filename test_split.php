<?php
$text = "၎င်းတို့၏၍သို့ဆို၌ဣတ္ထိယ";

// old pattern
$pattern1 = '/(?<!္)(?=[က-အ](?![်္]))/u';
$parts1 = preg_split($pattern1, $text, -1, PREG_SPLIT_NO_EMPTY);
print_r($parts1);

// new pattern
$pattern2 = '/(?<!္)(?=[က-အဣ-ဪ၀-၉၌-၏](?![်္]))/u';
$parts2 = preg_split($pattern2, $text, -1, PREG_SPLIT_NO_EMPTY);
print_r($parts2);

// Let's also handle the fact that characters like ၎င်း ၏ ၍ ၌ might not be followed by [်] or [္].
// In fact, symbols might end a syllable, but if they are followed immediately by another symbol, they need to split correctly.
