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

// إعداد عنوان الموقع (Base URL)
$baseURL = "http://192.168.1.7/shopbackend/"; // قم بتعديله حسب بيئتك

// الحصول على البيانات من الطلب
$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;
$old_price = $_POST['old_price'] ?? null;
$stock = $_POST['stock'] ?? null;
$category_id = $_POST['category_id'] ?? null;

// التحقق من صحة البيانات
if (!$name || !$description || !$price || !$stock || !$category_id) {
    echo json_encode([
        "status" => false,
        "message" => "All fields (name, description, price, stock, category_id) are required"
    ]);
    exit;
}

// إعداد مجلد رفع الصور
$uploadDir = '../uploads/product/img/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// رفع الصور
$main_image = null;
$detail_images = [];

$allowedTypes = ['image/jpeg', 'image/png'];
$maxSize = 2 * 1024 * 1024; // 2 ميجابايت

// رفع الصورة الرئيسية
if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    if (!in_array($_FILES['main_image']['type'], $allowedTypes) || $_FILES['main_image']['size'] > $maxSize) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid main image type or size exceeds 2MB"
        ]);
        exit;
    }

    $main_image_name = uniqid() . '_' . basename($_FILES['main_image']['name']);
    $main_image_path = $uploadDir . $main_image_name;

    if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to upload main image"
        ]);
        exit;
    }

    $main_image = $baseURL . ltrim($main_image_path, '../');
}

// رفع الصور التفصيلية
if (isset($_FILES['detail_images']) && is_array($_FILES['detail_images']['tmp_name'])) {
    foreach ($_FILES['detail_images']['tmp_name'] as $index => $tmp_name) {
        if ($_FILES['detail_images']['error'][$index] === UPLOAD_ERR_OK) {
            if (!in_array($_FILES['detail_images']['type'][$index], $allowedTypes) || $_FILES['detail_images']['size'][$index] > $maxSize) {
                echo json_encode([
                    "status" => false,
                    "message" => "Invalid detail image type or size exceeds 2MB"
                ]);
                exit;
            }

            $detail_image_name = uniqid() . '_' . basename($_FILES['detail_images']['name'][$index]);
            $detail_image_path = $uploadDir . $detail_image_name;

            if (move_uploaded_file($tmp_name, $detail_image_path)) {
                $detail_images[] = $baseURL . ltrim($detail_image_path, '../');
            }
        }
    }
}

// تحويل صور التفاصيل إلى JSON
$detail_images_json = json_encode($detail_images);

try {
    // إدخال المنتج في قاعدة البيانات
    $stmt = $conn->prepare("
        INSERT INTO products (name, description, price, old_price, stock, main_image, detail_images, category_id, created_at) 
        VALUES (:name, :description, :price, :old_price, :stock, :main_image, :detail_images, :category_id, NOW())
    ");
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'old_price' => $old_price,
        'stock' => $stock,
        'main_image' => $main_image,
        'detail_images' => $detail_images_json,
        'category_id' => $category_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Product added successfully",
        "data" => [
            "id" => $conn->lastInsertId(),
            "name" => $name,
            "description" => $description,
            "price" => $price,
            "old_price" => $old_price,
            "stock" => $stock,
            "main_image" => $main_image,
            "detail_images" => $detail_images,
            "category_id" => $category_id
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
