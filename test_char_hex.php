<?php
$str = "၎င်း";
for ($i = 0; $i < mb_strlen($str, 'UTF-8'); $i++) {
    $char = mb_substr($str, $i, 1, 'UTF-8');
    echo implode('', unpack('H*', $char)) . "\n";
}
