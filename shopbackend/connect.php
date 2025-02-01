<?php
$host = "localhost";
$dbname = "online_store";
$user = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
