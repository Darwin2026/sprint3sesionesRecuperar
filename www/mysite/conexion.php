<?php
declare(strict_types=1);

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=mysitedb;charset=utf8mb4",
        "darwin",
        "1234",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
