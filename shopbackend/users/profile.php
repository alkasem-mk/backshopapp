<?php
include_once '../connect.php';

header("Content-Type: application/json");

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email, role,status,created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            "status" => true,
            "data" => $user
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
