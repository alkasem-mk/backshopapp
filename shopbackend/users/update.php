<?php
include_once('../connect.php');

header("Content-Type: application/json");

session_start();

// التحقق من وجود الجلسة
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

// الحصول على البيانات من JSON
$inputData = json_decode(file_get_contents("php://input"), true);

$username = $inputData['username'] ?? null;
$email = $inputData['email'] ?? null;

// التحقق من وجود بيانات للتحديث
if (!$username && !$email) {
    echo json_encode([
        "status" => false,
        "message" => "No data provided for update"
    ]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];

    // تحضير جملة SQL لتحديث البيانات
    $updates = [];
    $params = ['id' => $userId];

    if ($username) {
        $updates[] = "username = :username";
        $params['username'] = $username;
    }
    if ($email) {
        $updates[] = "email = :email";
        $params['email'] = $email;
    }

    // بناء استعلام SQL ديناميكي حسب البيانات المرسلة
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        "status" => true,
        "message" => "Profile updated successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
