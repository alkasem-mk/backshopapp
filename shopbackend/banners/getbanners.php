<?php
include_once('../connect.php');
header("Content-Type: application/json");

try {
    // استعلام لجلب جميع الإعلانات من قاعدة البيانات
    $stmt = $conn->prepare("SELECT id, title, image, position, created_at FROM banners ORDER BY created_at DESC");
    $stmt->execute();
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // التحقق من وجود إعلانات
    if (empty($banners)) {
        echo json_encode([
            "status" => true,
            "message" => "No banners found",
            "data" => []
        ]);
        exit;
    }

    // إرجاع البيانات
    echo json_encode([
        "status" => true,
        "message" => "Banners retrieved successfully",
        "data" => $banners
    ]);
} catch (PDOException $e) {
    // معالجة الأخطاء
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => []
    ]);
}
