<?php

include 'env.php';

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
    if ($result->num_rows === 0) {
        $query = "CREATE TABLE website(
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            scheme VARCHAR(255),
            host VARCHAR(255),
            enpoint VARCHAR(255),
            storage VARCHAR(255),
            created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $query = preg_replace('/\s+/', ' ', $query);
        $result = $db->query($query);
        if ($result) {
            echo "\033[32m" . "Table created" . "\033[37m\r" . PHP_EOL;
        } else {
            echo "\033[31m" . "Failed to create table" . "\033[37m\r" . PHP_EOL;
        }
    }
} catch (Error $error) {
    throw new ErrorException($error);
}
