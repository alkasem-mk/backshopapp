<?php
include_once '../connect.php';
header("Content-Type: application/json");

session_start();

// تحقق إذا كان المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

// تحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// تحقق من وجود معرف الفئة في الطلب
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Category ID is required"
    ]);
    exit;
}

$categoryId = intval($_GET['id']);

try {
    // جلب تفاصيل الفئة من قاعدة البيانات
    $stmt = $conn->prepare("SELECT id, name, description, image, created_at FROM categories WHERE id = :id");
    $stmt->execute(['id' => $categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo json_encode([
            "status" => false,
            "message" => "Category not found"
        ]);
        exit;
    }

    echo json_encode([
        "status" => true,
        "data" => $category
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
