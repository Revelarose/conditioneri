<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$host = 'localhost';
$db = 'testorder';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT NULL,
        price DECIMAL(12,2) NOT NULL DEFAULT 0,
        image VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, name, description, price, image, created_at, updated_at FROM products ORDER BY id DESC');
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        http_response_code(405);
        echo json_encode(['error' => 'Метод не поддерживается'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) throw new RuntimeException('Не указан ID товара');
        $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) throw new RuntimeException('Товар не найден');
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        if ($product['image']) {
            $path = __DIR__ . '/' . ltrim($product['image'], '/');
            if (is_file($path)) @unlink($path);
        }
        echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);

    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $body);
        $name = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $price = (float)($body['price'] ?? 0);
    }

    if ($name === '') throw new RuntimeException('Название товара обязательно');
    if ($price < 0) throw new RuntimeException('Цена не может быть отрицательной');

    $uploadDir = __DIR__ . '/uploads/products';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $imagePath = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Не удалось загрузить изображение');
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) throw new RuntimeException('Изображение должно быть не больше 5 МБ');
        $info = @getimagesize($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$info || !isset($allowed[$info['mime']])) throw new RuntimeException('Разрешены только JPG, PNG и WebP');
        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$info['mime']];
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $filename)) throw new RuntimeException('Не удалось сохранить изображение');
        $imagePath = 'uploads/products/' . $filename;
    }

    if ($method === 'POST') {
        $stmt = $pdo->prepare('INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $description, $price, $imagePath]);
        echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!$id) throw new RuntimeException('Не указан ID товара');
    $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $old = $stmt->fetch();
    if (!$old) throw new RuntimeException('Товар не найден');

    if ($imagePath) {
        $stmt = $pdo->prepare('UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?');
        $stmt->execute([$name, $description, $price, $imagePath, $id]);
        if ($old['image']) { $oldPath = __DIR__ . '/' . ltrim($old['image'], '/'); if (is_file($oldPath)) @unlink($oldPath); }
    } else {
        $stmt = $pdo->prepare('UPDATE products SET name=?, description=?, price=? WHERE id=?');
        $stmt->execute([$name, $description, $price, $id]);
    }
    echo json_encode(['status' => 'success', 'id' => $id], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>