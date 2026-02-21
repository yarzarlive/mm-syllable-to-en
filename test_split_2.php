<?php
$text = "၎င်းတို့၏၍သို့ဆို၌ဣတ္ထိယ";

$pattern = '/(?<!္)(?=[က-ဪ၀-၉၌-၏](?![်္]))/u';
$parts = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);
print_r($parts);

$text2 = "၀၁၂၃၄၅၆၇၈၉";
$parts2 = preg_split($pattern, $text2, -1, PREG_SPLIT_NO_EMPTY);
print_r($parts2);