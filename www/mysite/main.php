<?php
declare(strict_types=1);
session_start();

$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página principal</title>
</head>
<body>

<h1>Bienvenido a Sprint 3</h1>

<?php if ($usuario): ?>
    <p>Hola, <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>. Has iniciado sesión correctamente.</p>
<?php else: ?>
    <p>No has iniciado sesión.</p>
    <p><a href="login.html">Iniciar sesión</a></p>
    <p><a href="register.html">Registrarse</a></p>
<?php endif; ?>

<p><a href="index.php">Ver libros</a></p>

<p><a href="logout.php">Cerrar sesión</a></p>
</body>
</html>
