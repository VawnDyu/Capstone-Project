<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$e_username = $_ENV['E_USERNAME'];
$e_password = $_ENV['E_PASSWORD'];

?>