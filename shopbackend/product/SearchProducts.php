<?php
include_once('../connect.php');
header("Content-Type: application/json");

// الحصول على البيانات من الطلب POST
$data = json_decode(file_get_contents("php://input"), true); // لاستقبال البيانات بصيغة JSON

$searchTerm = $data['searchTerm'] ?? ''; // البحث بواسطة الاسم أو الوصف أو أي معيار آخر
$category_id = $data['category_id'] ?? null; // البحث حسب الفئة
$min_price = $data['min_price'] ?? null; // البحث حسب الحد الأدنى للسعر
$max_price = $data['max_price'] ?? null; // البحث حسب الحد الأقصى للسعر

// بناء استعلام SQL مع معايير البحث
$sql = "SELECT * FROM products WHERE 1"; // 1 يعني أن الاستعلام سيكون صحيح دائمًا، ونضيف إليه شروط البحث حسب الحاجة

$params = [];

if (!empty($searchTerm)) {
    $sql .= " AND (name LIKE :searchTerm OR description LIKE :searchTerm)";
    $params['searchTerm'] = "%" . $searchTerm . "%"; // بحث عن الاسم أو الوصف
}

if ($category_id) {
    $sql .= " AND category_id = :category_id";
    $params['category_id'] = $category_id;
}

if ($min_price) {
    $sql .= " AND price >= :min_price";
    $params['min_price'] = $min_price;
}

if ($max_price) {
    $sql .= " AND price <= :max_price";
    $params['max_price'] = $max_price;
}

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        echo json_encode([
            "status" => true,
            "message" => "Products found",
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
        "data" => null
    ]);
}
