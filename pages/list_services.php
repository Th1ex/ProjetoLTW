<?php
// pages/list_services.php

// Inclui o header, que já inicia session_start() e exibe o menu
include_once '../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

// Conexão com o banco
$db = getConnection();

// Opcional: Poderíamos pegar um filtro de categoria via GET, ex.: ?category=1
// Para isso, algo como:
// $filterCategory = $_GET['category'] ?? null;

// Monta a query principal
// services + join de users (para nome do freelancer) + join de categories
$sql = "
    SELECT
        services.service_id,
        services.title,
        services.price,
        services.description,
        categories.category_name,
        users.username AS freelancer_name
    FROM services
    INNER JOIN users ON services.user_id = users.user_id
    INNER JOIN categories ON services.category_id = categories.category_id
";

// Se quisermos filtrar por categoria, poderíamos adicionar WHERE
// if ($filterCategory) {
//     $sql .= " WHERE services.category_id = :cat";
// }

// Ordena se desejado
// $sql .= " ORDER BY services.created_at DESC";

$stmt = $db->prepare($sql);

// if ($filterCategory) {
//     $stmt->bindValue(':cat', $filterCategory, PDO::PARAM_INT);
// }

$stmt->execute();

// Busca todos os serviços
$services = $stmt->fetchAll();
?>

<h2>Lista de Serviços</h2>

<?php if (count($services) === 0): ?>
    <p>Nenhum serviço encontrado.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Preço</th>
                <th>Categoria</th>
                <th>Freelancer</th>
                <!-- Podemos mostrar uma prévia da descrição, ou linkar para página de detalhes -->
                <th>Mais Detalhes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['title']) ?></td>
                    <td><?= htmlspecialchars($service['price']) ?> €</td>
                    <td><?= htmlspecialchars($service['category_name']) ?></td>
                    <td><?= htmlspecialchars($service['freelancer_name']) ?></td>
                    <td>
                        <!-- Link para detalhes do serviço, que ainda vamos criar, ex: service.php?id= -->
                        <a href="service.php?id=<?= $service['service_id'] ?>">Ver Detalhes</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
// Inclui o footer
include_once '../templates/footer.php';
?>
