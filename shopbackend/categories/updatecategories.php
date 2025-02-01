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
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$image = $_FILES['image'] ?? null;

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

    // تحديث البيانات
    $updateFields = [];
    $updateParams = ['id' => $categoryId];

    if (!empty($name)) {
        $updateFields[] = "name = :name";
        $updateParams['name'] = $name;
    }

    if (!empty($description)) {
        $updateFields[] = "description = :description";
        $updateParams['description'] = $description;
    }

    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        // تنظيف اسم الصورة والتحقق
        $uploadDir = '../uploads/categories/img/';
        $imageName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', str_replace(' ', '_', $image['name']));
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($image['type'], $allowedTypes) || $image['size'] > $maxSize) {
            echo json_encode([
                "status" => false,
                "message" => "Invalid image type or size exceeds 2MB."
            ]);
            exit;
        }

        $imagePath = $uploadDir . $imageName;

        // حذف الصورة القديمة
        if (!empty($category['image']) && file_exists($category['image'])) {
            unlink($category['image']);
        }

        // رفع الصورة
        if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
            echo json_encode([
                "status" => false,
                "message" => "Failed to upload image"
            ]);
            exit;
        }

        $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
        $imageFullPath = $baseUrl . "/uploads/categories/img/" . $imageName;

        $updateFields[] = "image = :image";
        $updateParams['image'] = $imageFullPath;
    }

    if (empty($updateFields)) {
        echo json_encode([
            "status" => false,
            "message" => "No fields to update"
        ]);
        exit;
    }

    $updateQuery = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = :id";
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute($updateParams);

    echo json_encode([
        "status" => true,
        "message" => "Category updated successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
