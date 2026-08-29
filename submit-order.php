<?php
header('Content-Type: application/json; charset=utf-8');
$host = 'localhost'; $db = 'testorder'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name && $phone) {
        $stmt = $pdo->prepare("INSERT INTO orders (name, phone, status) VALUES (?, ?, 'new')");
        $stmt->execute([$name, $phone]);
        echo json_encode(['status'=>'success','message'=>'Заявка успешно отправлена!','order_id'=>$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Пожалуйста, заполните все поля'], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Ошибка сервера'], JSON_UNESCAPED_UNICODE);
}
?>