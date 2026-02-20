<?php
declare(strict_types=1);
session_start();
require_once "conexion.php";

$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página principal</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;

            background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fbc2eb, #a6c1ee);
            background-size: 400% 400%;
            animation: gradientMove 12s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .menu {
            list-style: none;
            padding: 0;
            width: 250px;
            margin: 0 auto;
        }

        .menu li {
            background: rgba(255, 255, 255, 0.7);
            margin: 10px 0;
            padding: 12px;
            border-radius: 6px;
            transition: all 0.4s ease;
            opacity: 0.85;
        }

        .menu li:hover {
            background: rgba(255, 255, 255, 0.95);
            transform: scale(1.05);
            opacity: 1;
        }

        .menu a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Bienvenido a Sprint 3</h1>

<?php if ($usuario): ?>
    <p>Hola, <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>.</p>
<?php else: ?>
    <p>No has iniciado sesión.</p>
<?php endif; ?>

<h2>Menú principal</h2>

<ul class="menu">
    <li><a href="index.php">Ver libros</a></li>

    <?php if (!$usuario): ?>
        <li><a href="login.html">Iniciar sesión</a></li>
        <li><a href="register.html">Registrarse</a></li>
    <?php else: ?>
        <li><a href="logout.php">Cerrar sesión</a></li>
	<li><a href="change_password.html">Cambiar Contraseña</a></li>
    <?php endif; ?>
</ul>

</body>
</html>
