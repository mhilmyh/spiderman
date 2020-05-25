<?php

include 'spiderman.php';

$options = [
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => true
];

$spidey = new Spiderman("https://stackoverflow.com/questions/28169042/php-curl-web-scraping");
