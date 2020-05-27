<?php

require __DIR__ . '/spiderman.php';
include __DIR__ . '/env.php';

$url = "https://id.simplesite.com/default.aspx";
$spidey = new Spiderman($url, $_ENV);
$spidey->crawlingPageLinks(null, 3, false, true);
