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

// الحصول على بيانات الطلب
$id = $_POST['id'] ?? '';
$title = $_POST['title'] ?? '';
$position = $_POST['position'] ?? '';

// التحقق من وجود ID الإعلان
if (empty($id)) {
    echo json_encode([
        "status" => false,
        "message" => "Banner ID is required"
    ]);
    exit;
}

try {
    // التحقق من وجود الإعلان في قاعدة البيانات
    $stmt = $conn->prepare("SELECT * FROM banners WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        echo json_encode([
            "status" => false,
            "message" => "Banner not found"
        ]);
        exit;
    }

    // تحديث الصورة إذا تم إرسال صورة جديدة
    $imagePath = $banner['image']; // الاحتفاظ بمسار الصورة القديم
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageType = $_FILES['image']['type'];

        // إزالة المسافات والرموز غير المسموح بها من اسم الصورة
        $imageName = str_replace(' ', '_', $imageName); // استبدال المسافات بـ "_"
        $imageName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $imageName); // إزالة الرموز غير المسموح بها

        // التحقق من نوع الصورة
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($imageType, $allowedTypes)) {
            echo json_encode([
                "status" => false,
                "message" => "Invalid image type. Only JPEG, JPG, and PNG are allowed."
            ]);
            exit;
        }

        // تحديد المجلد ومسار الصورة
        $uploadDir = '../uploads/banners/img/';
        $relativePath = $uploadDir . $imageName;
        $imagePath = 'http://localhost/shopbackend/uploads/banners/img/' . $imageName;

        // التأكد من وجود المجلد
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // نقل الصورة الجديدة إلى المجلد
        if (!move_uploaded_file($imageTmpName, $relativePath)) {
            echo json_encode([
                "status" => false,
                "message" => "Failed to upload image."
            ]);
            exit;
        }

        // حذف الصورة القديمة إذا كانت موجودة
        $oldImagePath = str_replace('http://localhost/shopbackend/', '../', $banner['image']);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    // إعداد الاستعلام للتحديث
    $updates = [];
    $params = ['id' => $id];

    if (!empty($title)) {
        $updates[] = "title = :title";
        $params['title'] = $title;
    }

    if (!empty($position)) {
        $updates[] = "position = :position";
        $params['position'] = $position;
    }

    if ($imagePath !== $banner['image']) {
        $updates[] = "image = :image";
        $params['image'] = $imagePath;
    }

    if (!empty($updates)) {
        $sql = "UPDATE banners SET " . implode(", ", $updates) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        echo json_encode([
            "status" => true,
            "message" => "Banner updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "No updates were made"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
