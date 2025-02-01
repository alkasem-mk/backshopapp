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

// الحصول على البيانات من الطلب
$product_id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;
$old_price = $_POST['old_price'] ?? null;
$stock = $_POST['stock'] ?? null;
$category_id = $_POST['category_id'] ?? null;

// التحقق من الحقل المطلوب
if (!$product_id) {
    echo json_encode([
        "status" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

// رفع الصور (اختياري)
$main_image = null;
$detail_images = [];

if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $main_image_name = uniqid() . '_' . $_FILES['main_image']['name'];
    $main_image_path = '../uploads/img/' . $main_image_name;
    move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path);
    $main_image = $main_image_path;
}

if (isset($_FILES['detail_images']) && is_array($_FILES['detail_images']['tmp_name'])) {
    foreach ($_FILES['detail_images']['tmp_name'] as $index => $tmp_name) {
        if ($_FILES['detail_images']['error'][$index] === UPLOAD_ERR_OK) {
            $detail_image_name = uniqid() . '_' . $_FILES['detail_images']['name'][$index];
            $detail_image_path = '../uploads/img/' . $detail_image_name;
            move_uploaded_file($tmp_name, $detail_image_path);
            $detail_images[] = $detail_image_path;
        }
    }
}

// تحويل صور التفاصيل إلى JSON
$detail_images_json = $detail_images ? json_encode($detail_images) : null;

try {
    // بناء استعلام SQL الديناميكي
    $updates = [];
    $params = ['id' => $product_id];

    if ($name) {
        $updates[] = "name = :name";
        $params['name'] = $name;
    }
    if ($description) {
        $updates[] = "description = :description";
        $params['description'] = $description;
    }
    if ($price) {
        $updates[] = "price = :price";
        $params['price'] = $price;
    }
    if ($old_price) {
        $updates[] = "old_price = :old_price";
        $params['old_price'] = $old_price;
    }
    if ($stock) {
        $updates[] = "stock = :stock";
        $params['stock'] = $stock;
    }
    if ($category_id) {
        $updates[] = "category_id = :category_id";
        $params['category_id'] = $category_id;
    }
    if ($main_image) {
        $updates[] = "main_image = :main_image";
        $params['main_image'] = $main_image;
    }
    if ($detail_images_json) {
        $updates[] = "detail_images = :detail_images";
        $params['detail_images'] = $detail_images_json;
    }

    if (empty($updates)) {
        echo json_encode([
            "status" => false,
            "message" => "No data provided for update"
        ]);
        exit;
    }

    $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        "status" => true,
        "message" => "Product updated successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
