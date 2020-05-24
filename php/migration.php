<?php

$db = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USERNAME"], $_ENV["DB_PASSWORD"], $_ENV["DB_DATABASE"]);

if (!$db) {
    echo "\033[31m" . "Error: Unable to connect to MySQL." . "\033[37m\r" . PHP_EOL;
    echo "\033[31m" . "Debugging errno: " . mysqli_connect_errno() . "\033[37m\r" . PHP_EOL;
    echo "\033[31m" . "Debugging error: " . mysqli_connect_error() . "\033[37m\r" . PHP_EOL;
    exit;
}

echo "\033[32m" . "Success connect to database !" . "\033[37m\r" . PHP_EOL;

try {
    $result = $db->query("SELECT * FROM information_schema.tables WHERE table_schema = '" . $_ENV['DB_DATABASE'] . "' AND table_name = 'website' LIMIT 1");
    print_r($result);
} catch (Error $error) {
    throw new ErrorException($error);
}
