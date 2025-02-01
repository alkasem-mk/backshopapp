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

// الحصول على بيانات الطلب
$product_id = $_POST['product_id'] ?? null;
$new_quantity = $_POST['quantity'] ?? null;

// التحقق من صحة البيانات
if (empty($product_id) || empty($new_quantity)) {
    echo json_encode([
        "status" => false,
        "message" => "Product ID and quantity are required"
    ]);
    exit;
}

try {
    // التحقق من وجود المنتج في السلة
    $stmt = $conn->prepare("SELECT * FROM carts WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute([
        'user_id' => $user_id,
        'product_id' => $product_id
    ]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart_item) {
        echo json_encode([
            "status" => false,
            "message" => "Product not found in cart"
        ]);
        exit;
    }

    // التحقق من الكمية الجديدة
    if ($new_quantity <= 0) {
        // حذف المنتج إذا كانت الكمية الجديدة صفر أو أقل
        $deleteStmt = $conn->prepare("DELETE FROM carts WHERE user_id = :user_id AND product_id = :product_id");
        $deleteStmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);
        echo json_encode([
            "status" => true,
            "message" => "Product removed from cart"
        ]);
        exit;
    }

    // تحديث الكمية والسعر الإجمالي
    $new_total_price = $new_quantity * $cart_item['price'];
    $updateStmt = $conn->prepare("UPDATE carts SET quantity = :quantity, total_price = :total_price WHERE user_id = :user_id AND product_id = :product_id");
    $updateStmt->execute([
        'quantity' => $new_quantity,
        'total_price' => $new_total_price,
        'user_id' => $user_id,
        'product_id' => $product_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Cart updated successfully",
        "data" => [
            "product_id" => $product_id,
            "quantity" => $new_quantity,
            "price" => $cart_item['price'],
            "total_price" => $new_total_price
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
