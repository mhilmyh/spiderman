<?php

$filename = '.env';
$env = fopen($filename, 'r') or die('Cannot open file !');
$arrays = explode("\n", fread($env, filesize($filename)));
foreach ($arrays as $element) {
    list($key, $value) = explode('=', $element);
    $_ENV[$key] = trim($value);
}
echo "\033[32m" . "Environtmet Set" . "\033[37m\r" . PHP_EOL;
