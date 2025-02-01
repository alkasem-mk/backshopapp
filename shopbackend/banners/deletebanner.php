<?php
include_once('../connect.php');
header("Content-Type: application/json");

session_start();

// التحقق من الجلسة ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access. Admin privileges are required."
    ]);
    exit;
}

$id = $_POST['id'] ?? '';

// التحقق من وجود ID الإعلان
if (empty($id)) {
    echo json_encode([
        "status" => false,
        "message" => "Banner ID is required"
    ]);
    exit;
}

try {
    // التحقق من وجود الإعلان في قاعدة البيانات
    $stmt = $conn->prepare("SELECT * FROM banners WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        echo json_encode([
            "status" => false,
            "message" => "Banner not found"
        ]);
        exit;
    }

    // حذف الصورة المرتبطة بالإعلان إذا كانت موجودة
    if (file_exists($banner['image'])) {
        unlink($banner['image']);
    }

    // حذف الإعلان من قاعدة البيانات
    $deleteStmt = $conn->prepare("DELETE FROM banners WHERE id = :id");
    $deleteStmt->execute(['id' => $id]);

    echo json_encode([
        "status" => true,
        "message" => "Banner deleted successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
