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

try {
    // جلب جميع الفئات من قاعدة البيانات
    $stmt = $conn->prepare("SELECT id, name, description, image, created_at FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($categories) === 0) {
        echo json_encode([
            "status" => false,
            "message" => "No categories found"
        ]);
        exit;
    }

    echo json_encode([
        "status" => true,
        "data" => $categories
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
