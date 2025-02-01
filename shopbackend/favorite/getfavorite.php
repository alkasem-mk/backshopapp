<?php
include_once '../connect.php';

header("Content-Type: application/json");
session_start();

// التحقق من جلسة المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode([
        "status" => false,
        "message" => "Access denied. Only customers can access favorites."
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // جلب قائمة المنتجات المفضلة
    $stmt = $conn->prepare("
        SELECT 
            products.id,
            products.name,
            products.description,
            products.price,
            products.main_image,
            products.category_id,
            products.created_at
        FROM favorite
        JOIN products ON favorite.product_id = products.id
        WHERE favorite.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Favorite products fetched successfully",
        "data" => $favorites
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
