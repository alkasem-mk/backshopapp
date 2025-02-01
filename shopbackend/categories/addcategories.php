<?php
include_once '../connect.php';
header("Content-Type: application/json");

// التحقق من أن الطلب يحتوي على بيانات
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// التحقق من الحقول المطلوبة
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$image = $_FILES['image'] ?? null;

if (empty($name)) {
    echo json_encode([
        "status" => false,
        "message" => "Category name is required"
    ]);
    exit;
}

if (!$image || $image['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => false,
        "message" => "Image upload failed"
    ]);
    exit;
}

try {
    // رفع الصورة
    $uploadDir = '../uploads/categories/img';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode([
                "status" => false,
                "message" => "Failed to create upload directory"
            ]);
            exit;
        }
    }

    // تنظيف اسم الصورة والتحقق من النوع والحجم
    $imageName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', str_replace(' ', '_', $image['name']));
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2 ميجابايت
    if (!in_array($image['type'], $allowedTypes) || $image['size'] > $maxSize) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid image type or size exceeds 2MB."
        ]);
        exit;
    }

    // توليد اسم فريد للملف
    $uniqueName = uniqid() . '-' . $imageName;
    $imagePath = $uploadDir . '/' . $uniqueName;

    if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to move uploaded file"
        ]);
        exit;
    }

    // توليد رابط الصورة الكامل
    $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
    $imageFullPath = $baseUrl . "/uploads/categories/img/" . $uniqueName;

    // إدخال الفئة في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO categories (name, description, image, created_at) VALUES (:name, :description, :image, NOW())");
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'image' => $imageFullPath
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Category added successfully",
        "data" => [
            "name" => $name,
            "description" => $description,
            "image" => $imageFullPath
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
