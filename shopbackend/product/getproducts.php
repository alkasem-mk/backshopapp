<?php
include_once('../connect.php');
header("Content-Type: application/json");

try {
    // الحصول على معرّف الفئة (اختياري)
    $category_id = $_GET['category_id'] ?? null;

    // استعلام SQL الأساسي
    $sql = "SELECT id, name, description, price, old_price, stock, main_image, category_id, created_at 
            FROM products";

    // إضافة شرط إذا تم توفير معرّف الفئة
    $params = [];
    if ($category_id) {
        $sql .= " WHERE category_id = :category_id";
        $params['category_id'] = $category_id;
    }

    // ترتيب النتائج بحسب التاريخ الأحدث
    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // جلب النتائج
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        echo json_encode([
            "status" => true,
            "message" => "Products retrieved successfully",
            "data" => $products
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "No products found",
            "data" => []
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => []
    ]);
}
