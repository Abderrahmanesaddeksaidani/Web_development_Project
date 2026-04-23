<?php
$host = "localhost";
$user = "root";     // اسم المستخدم الافتراضي في XAMPP
$pass = "";         // كلمة المرور الافتراضية تكون فارغة
$dbname = "library_db";

// إنشاء الاتصال باستخدام PDO (أكثر أماناً من mysqli)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // تفعيل وضع الأخطاء لرؤية المشاكل أثناء البرمجة
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>