<?php
declare(strict_types=1);
session_start();
require_once "conexion.php";

// Obtener todos los libros
$stmt = $pdo->query("SELECT id, titulo, autor, imagen FROM tLibros ORDER BY id DESC");
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de libros</title>
</head>
<body>

<h1>Listado de libros</h1>

<?php if (empty($libros)): ?>
    <p>No hay libros en la base de datos.</p>
<?php else: ?>
    <ul>
        <?php foreach ($libros as $libro): ?>
            <li>
                <a href="detail.php?id=<?= $libro['id'] ?>">
                    <?= htmlspecialchars($libro['titulo']) ?>  
                    (<?= htmlspecialchars($libro['autor']) ?>)
                </a>

                <?php if (!empty($libro['imagen'])): ?>
                    <br>
                    <img src="img/<?= htmlspecialchars($libro['imagen']) ?>" width="100">
                <?php endif; ?>

                <br><br>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="main.php">Volver a la página principal</a></p>

</body>
</html>
