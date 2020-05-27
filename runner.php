<?php

require __DIR__ . '/spiderman.php';

$spidey = new Spiderman("http://godata.bemkmipb.org/");
echo $spidey->singleWebHit();
