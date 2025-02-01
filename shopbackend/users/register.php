<?php
include_once('../connect.php');
header("Content-Type: application/json");

// الحصول على البيانات من الطلب
$inputData = json_decode(file_get_contents("php://input"), true);
$username = $inputData['username'] ?? '';
$email = $inputData['email'] ?? '';
$password = $inputData['password'] ?? '';
$role = $inputData['role'] ?? 'customer'; // افتراضيًا، المستخدم سيكون عميل
$status = 'active'; // الحالة الافتراضية هي "نشط"

// التحقق من البيانات المدخلة
if (empty($username) || empty($email) || empty($password)) {
    echo json_encode([
        "status" => false,
        "message" => "Username, email, and password are required",
        "data" => null
    ]);
    exit;
}

// التحقق من صحة البريد الإلكتروني
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email format",
        "data" => null
    ]);
    exit;
}

try {
    // التحقق من وجود البريد الإلكتروني مسبقًا
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "status" => false,
            "message" => "Email already exists",
            "data" => null
        ]);
        exit;
    }

    // تشفير كلمة المرور
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // إدخال بيانات المستخدم الجديد في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (:username, :email, :password, :role, :status, NOW())");
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role,
        'status' => $status
    ]);

    // جلب بيانات المستخدم المسجل حديثًا
    $userData = [
        "id" => $conn->lastInsertId(),
        "username" => $username,
        "email" => $email,
        "role" => $role,
        "status" => $status,
        "created_at" => date('Y-m-d H:i:s')
    ];

    echo json_encode([
        "status" => true,
        "message" => "User registered successfully",
        "data" => $userData
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => null
    ]);
}
