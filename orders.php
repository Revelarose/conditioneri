<?php
// orders.php — API для получения заказов
header('Content-Type: application/json');

$host = 'localhost';
$db = 'testorder';  // твоя база
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $stmt = $pdo->query("SELECT id, name, phone, status FROM orders ORDER BY id DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}