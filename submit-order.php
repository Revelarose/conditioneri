<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$pdo=new PDO('mysql:host=localhost;dbname=testorder;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
try{
$name=trim($_POST['name']??'');$phone=trim($_POST['phone']??'');
if(!$name||!$phone)throw new RuntimeException('Пожалуйста, заполните все поля');
$products=$_POST['products']??[];$services=$_POST['services']??[];
if(!is_array($products))$products=[];if(!is_array($services))$services=[];
$hasItems=false;foreach($products as $qty){if((int)$qty>0){$hasItems=true;break;}}if(!$hasItems)foreach($services as $selected){if($selected){$hasItems=true;break;}}
if(!$hasItems)throw new RuntimeException('Добавьте хотя бы один товар или услугу в заявку');
$pdo->exec("CREATE TABLE IF NOT EXISTS order_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id INT UNSIGNED NOT NULL,product_id INT UNSIGNED NULL,service_id INT UNSIGNED NULL,title VARCHAR(255) NOT NULL,price DECIMAL(12,2) NOT NULL DEFAULT 0,quantity INT UNSIGNED NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(order_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->beginTransaction();
$s=$pdo->prepare("INSERT INTO orders(name,phone,status) VALUES(?,?, 'new')");$s->execute([$name,$phone]);$orderId=(int)$pdo->lastInsertId();
$ins=$pdo->prepare('INSERT INTO order_items(order_id,product_id,service_id,title,price,quantity) VALUES(?,?,?,?,?,?)');
foreach($products as $id=>$qty){$id=(int)$id;$qty=max(1,(int)$qty);if(!$id)continue;$q=$pdo->prepare('SELECT name,price FROM products WHERE id=?');$q->execute([$id]);if($x=$q->fetch()){$ins->execute([$orderId,$id,null,$x['name'],(float)$x['price'],$qty]);}}
foreach($services as $id=>$selected){if(!$selected)continue;$id=(int)$id;if(!$id)continue;$q=$pdo->prepare('SELECT name,price FROM services WHERE id=?');$q->execute([$id]);if($x=$q->fetch()){$price=is_numeric($x['price'])?(float)$x['price']:0;$ins->execute([$orderId,null,$id,$x['name'],$price,1]);}}
$pdo->commit();echo json_encode(['status'=>'success','message'=>'Заявка успешно отправлена!','order_id'=>$orderId],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(400);echo json_encode(['status'=>'error','message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
?>