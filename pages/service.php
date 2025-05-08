<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$service_id = $_GET['id']??'';
if(!is_numeric($service_id)) die('ID inválido');

$stmt=$db->prepare("
  SELECT s.*, c.category_name, u.username
  FROM services s
  JOIN categories c ON s.category_id = c.category_id
  JOIN users u ON s.user_id = u.user_id
  WHERE s.service_id = :sid
");
$stmt->execute([':sid'=>$service_id]);
$svc=$stmt->fetch();
if(!$svc) die('Serviço não encontrado');
?>

<div class="service-container">
    <h2><?=htmlspecialchars($svc['title'])?></h2>
    <p><b>Freelancer:</b> <?=htmlspecialchars($svc['username'])?></p>
    <p><b>Categoria:</b> <?=htmlspecialchars($svc['category_name'])?></p>
    <p><b>Preço:</b> <?=htmlspecialchars($svc['price'])?> €</p>
    <p><b>Entrega:</b> <?=htmlspecialchars($svc['delivery_time'])?> dias</p>

    <h3>Galeria</h3>
    <?php
    $med=$db->prepare("
    SELECT file_name,media_type FROM service_media
    WHERE service_id = :sid ORDER BY media_id
    ");
    $med->execute([':sid'=>$service_id]);
    $any=false;
    foreach($med as $m){
        $any=true;
        $src='../uploads/'.htmlspecialchars($m['file_name']);
        if($m['media_type']=='image'){
            echo "<img src=\"$src\" style=\"max-width:250px;margin:5px\">";
        }else{
            echo "<video src=\"$src\" controls style=\"max-width:250px;margin:5px\"></video>";
        }
    }
    /* compatibilidade – mostra imagem antiga se não houver media */
    if(!$any && $svc['image']){
        $src='../uploads/'.htmlspecialchars($svc['image']);
        echo "<img src=\"$src\" style=\"max-width:250px;margin:5px\">";
    }
    ?>

    <h3>Descrição</h3>
    <p><?=nl2br(htmlspecialchars($svc['description']))?></p>

    <?php if(isset($_SESSION['user_id'])&&$_SESSION['user_id']!=$svc['user_id']):?>
    <form action="../actions/hire_service_action.php" method="post">
    <input type="hidden" name="service_id" value="<?=$service_id?>">
    <button type="submit">Contratar</button>
    </form>
    <a href="messages_chat.php?user=<?=$svc['user_id']?>">Enviar Mensagem</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
