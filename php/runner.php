<?php

include 'spiderman.php';

$spidey = new Spiderman("https://web.ics.purdue.edu/~gchopra/class/public/pages/webdesign/05_simple.html");
$spidey->singleWebHit();
print_r($spidey->getElementsByTagName('body'));
