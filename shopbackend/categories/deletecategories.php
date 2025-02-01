<?php
include_once '../connect.php';
header("Content-Type: application/json");

session_start();

// تحقق إذا كان المستخدم مسجل دخول كمسؤول
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

// تحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// الحصول على البيانات من الطلب
$categoryId = $_POST['id'] ?? '';

// التحقق من البيانات المطلوبة
if (empty($categoryId)) {
    echo json_encode([
        "status" => false,
        "message" => "Category ID is required"
    ]);
    exit;
}

try {
    // تحقق إذا كانت الفئة موجودة
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo json_encode([
            "status" => false,
            "message" => "Category not found"
        ]);
        exit;
    }

    // حذف الصورة المرتبطة بالفئة (اختياري)
    if (!empty($category['image']) && file_exists($category['image'])) {
        unlink($category['image']);
    }

    // حذف الفئة
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $categoryId]);

    echo json_encode([
        "status" => true,
        "message" => "Category deleted successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
