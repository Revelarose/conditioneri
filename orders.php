<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$host = 'localhost'; $db = 'testorder'; $user = 'root'; $pass = '';
$allowedStatuses = ['new', 'contacted', 'confirmed', 'completed', 'cancelled'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT id, name, phone, status FROM orders ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method !== 'PATCH' && $method !== 'POST') {
        http_response_code(405);
        echo json_encode(['status'=>'error','message'=>'Метод не поддерживается'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = (int)($data['id'] ?? 0);
    $status = trim($data['status'] ?? '');
    if (!$id || !in_array($status, $allowedStatuses, true)) throw new RuntimeException('Некорректный заказ или статус');

    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    if ($stmt->rowCount() === 0) throw new RuntimeException('Заказ не найден или статус не изменился');
    echo json_encode(['status'=>'success'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>