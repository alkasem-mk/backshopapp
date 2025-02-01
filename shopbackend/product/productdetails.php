<?php
include_once('../connect.php');
header("Content-Type: application/json");

try {
    // التحقق من وجود معرّف المنتج
    $product_id = $_GET['id'] ?? null;

    if (!$product_id) {
        echo json_encode([
            "status" => false,
            "message" => "Product ID is required",
            "data" => null
        ]);
        exit;
    }

    // جلب تفاصيل المنتج
    $sql = "SELECT id, name, description, price, old_price, stock, main_image, detail_images, category_id, created_at, is_favorit 
            FROM products 
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $product_id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // معالجة الصور التفصيلية إذا كانت موجودة
        $product['detail_images'] = $product['detail_images']
            ? explode(',', $product['detail_images'])
            : [];

        echo json_encode([
            "status" => true,
            "message" => "Product details retrieved successfully",
            "data" => $product
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Product not found",
            "data" => null
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => null
    ]);
}
