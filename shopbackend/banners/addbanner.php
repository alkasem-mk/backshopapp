<?php
include_once('../connect.php');
header("Content-Type: application/json");

session_start();

// التحقق من الجلسة ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access. Admin privileges are required."
    ]);
    exit;
}

// التأكد من وجود البيانات في الطلب
$title = $_POST['title'] ?? '';
$position = $_POST['position'] ?? '';

// التأكد من وجود الصورة في البيانات المرسلة
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageName = $_FILES['image']['name'];
    $imageSize = $_FILES['image']['size'];
    $imageType = $_FILES['image']['type'];

    // تنظيف اسم الصورة
    $imageName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', str_replace(' ', '_', $imageName));

    // التأكد من أن الصورة هي من النوع المدعوم (مثل jpeg, png)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($imageType, $allowedTypes)) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid image type. Only JPEG, JPG, and PNG are allowed."
        ]);
        exit;
    }

    // التحقق من حجم الصورة
    $maxSize = 2 * 1024 * 1024; // 2 ميجابايت
    if ($imageSize > $maxSize) {
        echo json_encode([
            "status" => false,
            "message" => "Image size exceeds the maximum allowed size of 2MB."
        ]);
        exit;
    }

    // تحديد المجلد الذي سيتم حفظ الصورة فيه
    $uploadDir = '../uploads/banners/img/';
    $imagePath = $uploadDir . basename($imageName);

    // التأكد من أن المجلد موجود أو إنشاؤه
    if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to create directory for uploads."
        ]);
        exit;
    }

    // تحريك الصورة إلى المجلد المحدد
    if (!move_uploaded_file($imageTmpName, $imagePath)) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to upload image."
        ]);
        exit;
    }

    // توليد رابط الصورة الكامل
    $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
    $imageFullPath = $baseUrl . "/uploads/banners/img/" . basename($imageName);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Image is required."
    ]);
    exit;
}

// التأكد من أن كل البيانات الأخرى موجودة
if (empty($title) || empty($position)) {
    echo json_encode([
        "status" => false,
        "message" => "Title and position are required"
    ]);
    exit;
}

try {
    // إدخال البيانات في قاعدة البيانات مع رابط الصورة الكامل
    $stmt = $conn->prepare("INSERT INTO banners (title, image, position, created_at) VALUES (:title, :image, :position, NOW())");
    $stmt->execute([
        'title' => $title,
        'image' => $imageFullPath, // تخزين رابط الصورة الكامل في قاعدة البيانات
        'position' => $position
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Banner added successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
