<?php
include_once('../connect.php');
header("Content-Type: application/json");

// التحقق من الجلسة
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // استرجاع العناصر الموجودة في السلة الخاصة بالمستخدم
    $stmt = $conn->prepare("
        SELECT 
            c.product_id, 
            c.quantity, 
            c.total_price, 
            p.id AS product_id, 
            p.name, 
            p.description, 
            p.price, 
            p.old_price, 
            p.main_image, 
            p.category_id, 
            p.created_at 
        FROM carts c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);

    $cart_items = [];
    $total_price = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cart_items[] = [
            "product_id" => $row['product_id'],
            "quantity" => $row['quantity'],
            "product" => [
                "id" => $row['product_id'],
                "name" => $row['name'],
                "description" => $row['description'],
                "price" => $row['price'],
                "old_price" => $row['old_price'],
                "main_image" => $row['main_image'],
                "category_id" => $row['category_id'],
                "created_at" => $row['created_at']
            ]
        ];
        $total_price += $row['total_price'];
    }

    echo json_encode([
        "status" => true,
        "message" => "Cart retrieved successfully",
        "data" => [
            "cart_items" => $cart_items,
            "total" => $total_price
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
