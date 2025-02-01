<?php
include_once('../connect.php');
header("Content-Type: application/json");
session_start();

// التأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

// الحصول على بيانات المنتج والكمية
$product_id = $_POST['product_id'] ?? '';
$quantity = $_POST['quantity'] ?? 1; // إذا لم يتم إرسال الكمية، يتم افتراضها 1

if (empty($product_id)) {
    echo json_encode([
        "status" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

// التحقق من صحة الكمية
if ($quantity < 1) {
    echo json_encode([
        "status" => false,
        "message" => "Quantity must be at least 1"
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // جلب سعر المنتج من جدول المنتجات
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            "status" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    $product_price = $product['price'];

    // التحقق مما إذا كان المنتج موجودًا في السلة
    $stmt = $conn->prepare("SELECT * FROM carts WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        // إذا كان المنتج موجودًا، احذفه
        $stmt = $conn->prepare("DELETE FROM carts WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);

        echo json_encode([
            "status" => true,
            "message" => "Product removed from cart"
        ]);
    } else {
        // حساب السعر الإجمالي
        // حساب السعر الإجمالي
        $total_price = $quantity * $product_price;

        // إذا لم يكن موجودًا، قم بإضافته مع الكمية والسعر
        $stmt = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity, price, total_price, created_at) 
VALUES (:user_id, :product_id, :quantity, :price, :total_price, NOW())");
        $stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product_price, // السعر الفردي
            'total_price' => $total_price // السعر الإجمالي
        ]);


        echo json_encode([
            "status" => true,
            "message" => "Product added to cart"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
