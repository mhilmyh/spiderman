<?php

include 'spiderman.php';

$spidey = new Spiderman("https://stackoverflow.com/questions/28169042/php-curl-web-scraping");

echo $spidey->file_get_html();
