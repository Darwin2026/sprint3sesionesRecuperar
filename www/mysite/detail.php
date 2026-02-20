<?php
declare(strict_types=1);
session_start();
require_once "conexion.php";

// 1. Comprobar que llega el ID del libro
if (!isset($_GET['id'])) {
    echo "No se ha especificado ningún libro.";
    exit;
}

$libro_id = (int) $_GET['id'];

// 2. Obtener los datos del libro
$stmt = $pdo->prepare("SELECT * FROM tLibros WHERE id = :id");
$stmt->execute([':id' => $libro_id]);
$libro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$libro) {
    echo "El libro no existe.";
    exit;
}

// 3. Obtener los comentarios del libro
$stmt = $pdo->prepare("
    SELECT c.comentario, c.fecha, u.nombre AS usuario
    FROM tComentarios c
    LEFT JOIN tUsuarios u ON c.usuario_id = u.id
    WHERE c.libro_id = :id
    ORDER BY c.fecha DESC
");
$stmt->execute([':id' => $libro_id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Saber si el usuario está logueado
$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($libro['titulo']) ?></title>
</head>
<body>

<p><a href="main.php">Volver a la página principal</a></p>


<h1><?= htmlspecialchars($libro['titulo']) ?></h1>

<p><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></p>

<?php if (!empty($libro['imagen'])): ?>
    <img src="img/<?= htmlspecialchars($libro['imagen']) ?>" width="200">
<?php endif; ?>

<p><?= nl2br(htmlspecialchars($libro['descripcion'])) ?></p>

<hr>

<h2>Comentarios</h2>

<?php if (empty($comentarios)): ?>
    <p>No hay comentarios todavía.</p>
<?php else: ?>
    <?php foreach ($comentarios as $c): ?>
        <p>
            <strong><?= htmlspecialchars($c['usuario'] ?? "Anónimo") ?>:</strong><br>
            <?= nl2br(htmlspecialchars($c['comentario'])) ?><br>
            <small><?= $c['fecha'] ?></small>
        </p>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<h3>Añadir un comentario</h3>

<form action="comment.php" method="POST">
    <textarea name="comentario" required></textarea><br><br>
    <input type="hidden" name="libro_id" value="<?= $libro_id ?>">
    <button type="submit">Enviar comentario</button>
</form>

<?php if ($usuario): ?>
    <p><a href="logout.php">Cerrar sesión</a></p>
<?php endif; ?>


</body>
</html>
