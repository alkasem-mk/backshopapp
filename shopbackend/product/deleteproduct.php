<?php
include_once('../connect.php');
header("Content-Type: application/json");

// التحقق من صلاحيات المسؤول
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

// الحصول على معرف المنتج من الطلب
$product_id = $_POST['id'] ?? null;

// التحقق من إدخال معرف المنتج
if (!$product_id) {
    echo json_encode([
        "status" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

try {
    // التحقق من وجود المنتج في قاعدة البيانات
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            "status" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    // حذف المنتج
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);

    echo json_encode([
        "status" => true,
        "message" => "Product deleted successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
