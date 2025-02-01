<?php
include_once '../connect.php';

header("Content-Type: application/json");
session_start();

// التحقق من جلسة المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode([
        "status" => false,
        "message" => "Access denied. Only customers can manage favorites."
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;

// التحقق من معرّف المنتج
if (!$product_id) {
    echo json_encode([
        "status" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

try {
    // التحقق من حالة المفضلة الحالية
    $stmt = $conn->prepare("SELECT id FROM favorite WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);

    if ($stmt->rowCount() > 0) {
        // المنتج موجود في المفضلة، قم بإزالته
        $stmt = $conn->prepare("DELETE FROM favorite WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);


        echo json_encode([
            "status" => true,
            "message" => "Product removed from favorites",
        ]);
    } else {
        // المنتج غير موجود في المفضلة، قم بإضافته
        $stmt = $conn->prepare("INSERT INTO favorite (user_id, product_id, created_at) VALUES (:user_id, :product_id, NOW())");
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        echo json_encode([
            "status" => true,
            "message" => "Product added to favorites",
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
