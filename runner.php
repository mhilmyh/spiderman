<?php

include 'spiderman.php';

$spidey = new Spiderman("https://www.w3schools.com/html/html_id.asp");
$spidey->singleWebHit();
print_r($spidey->getResponse());
