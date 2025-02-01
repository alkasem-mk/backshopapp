<?php
// include_once('../connect.php');
// header("Content-Type: application/json");

// // الحصول على البيانات من الـ form-data
// $email = $_POST['email'] ?? '';
// $password = $_POST['password'] ?? '';

// // التحقق من البيانات المدخلة
// if (empty($email) || empty($password)) {
//     echo json_encode([
//         "status" => false,
//         "message" => "Email and password are required",
//         "data" => null
//     ]);
//     exit;
// }

// // التحقق من صحة البريد الإلكتروني
// if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//     echo json_encode([
//         "status" => false,
//         "message" => "Invalid email format",
//         "data" => null
//     ]);
//     exit;
// }

// try {
//     // التحقق من وجود المستخدم
//     $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
//     $stmt->execute(['email' => $email]);
//     $user = $stmt->fetch(PDO::FETCH_ASSOC);

//     if (!$user) {
//         echo json_encode([
//             "status" => false,
//             "message" => "User not found",
//             "data" => null
//         ]);
//         exit;
//     }

//     // التحقق من حالة الحساب
//     if ($user['status'] !== 'active') {
//         echo json_encode([
//             "status" => false,
//             "message" => "Account is not active",
//             "data" => null
//         ]);
//         exit;
//     }

//     // التحقق من صحة كلمة المرور
//     if (!password_verify($password, $user['password'])) {
//         echo json_encode([
//             "status" => false,
//             "message" => "Invalid password",
//             "data" => null
//         ]);
//         exit;
//     }

//     // إنشاء جلسة للمستخدم
//     session_start();
//     $_SESSION['user_id'] = $user['id'];
//     $_SESSION['username'] = $user['username'];
//     $_SESSION['email'] = $user['email'];
//     $_SESSION['role'] = $user['role'];
//     $_SESSION['status'] = $user['status'];

//     echo json_encode([
//         "status" => true,
//         "message" => "Login successful",
//         "data" => [
//             "id" => $user['id'],
//             "username" => $user['username'],
//             "email" => $user['email'],
//             "role" => $user['role'],
//             "status" => $user['status'],
//             "created_at" => $user['created_at']
//         ]
//     ]);
// } catch (PDOException $e) {
//     echo json_encode([
//         "status" => false,
//         "message" => "An error occurred: " . $e->getMessage(),
//         "data" => null
//     ]);
// }

include_once('../connect.php');
header("Content-Type: application/json");

// قراءة بيانات JSON من جسم الطلب
$data = json_decode(file_get_contents("php://input"), true);

// الحصول على القيم من JSON
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// التحقق من البيانات المدخلة
if (empty($email) || empty($password)) {
    echo json_encode([
        "status" => false,
        "message" => "Email and password are required",
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
    // التحقق من وجود المستخدم
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "status" => false,
            "message" => "User not found",
            "data" => null
        ]);
        exit;
    }

    // التحقق من حالة الحساب
    if ($user['status'] !== 'active') {
        echo json_encode([
            "status" => false,
            "message" => "Account is not active",
            "data" => null
        ]);
        exit;
    }

    // التحقق من صحة كلمة المرور
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid password",
            "data" => null
        ]);
        exit;
    }

    // إنشاء جلسة للمستخدم
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['status'] = $user['status'];

    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "data" => [
            "id" => $user['id'],
            "username" => $user['username'],
            "email" => $user['email'],
            "role" => $user['role'],
            "status" => $user['status'],
            "created_at" => $user['created_at']
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => null
    ]);
}
