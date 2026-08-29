<?php
// submit_order.php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'testorder';  // замени на название твоей БД
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные из POST
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

    if ($name && $phone) {
        $stmt = $pdo->prepare("INSERT INTO orders (name, phone) VALUES (?, ?)");
        $stmt->execute([$name, $phone]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Заявка успешно отправлена!',
            'order_id' => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Пожалуйста, заполните все поля'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
?>