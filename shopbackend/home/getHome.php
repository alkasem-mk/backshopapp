<?php
include_once('../connect.php');
header("Content-Type: application/json");

try {
    // جلب البيانات الخاصة بـ Banners
    $stmtBanners = $conn->prepare("SELECT id, title, image, position FROM banners ORDER BY created_at DESC");
    $stmtBanners->execute();
    $banners = $stmtBanners->fetchAll(PDO::FETCH_ASSOC);

    // جلب البيانات الخاصة بالفئات (Categories)
    $stmtCategories = $conn->prepare("SELECT id, name, image FROM categories ORDER BY name ASC");
    $stmtCategories->execute();
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

    // جلب المنتجات الحديثة
    $stmtLatestProducts = $conn->prepare("SELECT id, name, main_image, price,old_price, category_id FROM products ORDER BY created_at DESC LIMIT 10");
    $stmtLatestProducts->execute();
    $latestProducts = $stmtLatestProducts->fetchAll(PDO::FETCH_ASSOC);

    // جلب أول 5 منتجات لأول 3 فئات
    $productsByCategory = [];
    $categoryCount = 0;

    foreach ($categories as $category) {
        if ($categoryCount >= 3) {
            break; // التوقف بعد معالجة 3 فئات
        }

        $categoryId = $category['id'];
        $stmtProducts = $conn->prepare("SELECT id, name, main_image, price ,old_price FROM products WHERE category_id = :category_id ORDER BY created_at DESC LIMIT 5");
        $stmtProducts->execute(['category_id' => $categoryId]);
        $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
        $productsByCategory["category_$categoryId"] = $products;

        $categoryCount++;
    }


    // إعداد الاستجابة النهائية
    echo json_encode([
        "status" => true,
        "data" => [
            "banners" => $banners,
            "categories" => $categories,
            "latest_products" => $latestProducts,
            "products_by_category" => $productsByCategory
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
