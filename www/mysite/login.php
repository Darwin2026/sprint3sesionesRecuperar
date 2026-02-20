<?php
declare(strict_types=1);
session_start();

require_once "conexion.php"; // si tienes un archivo de conexión, si no, pon aquí tu PDO

// 1. Comprobar que llegan los datos
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo "Debes rellenar todos los campos.";
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// 2. Buscar el usuario por email
$stmt = $pdo->prepare("SELECT * FROM tUsuarios WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Si no existe el email
if (!$usuario) {
    echo "El email no existe.";
    exit;
}

// 4. Verificar contraseña
if (!password_verify($password, $usuario['contraseña'])) {
    echo "Contraseña incorrecta.";
    exit;
}

// 5. Si todo está bien → iniciar sesión
$_SESSION['usuario'] = [
    'id' => $usuario['id'],
    'nombre' => $usuario['nombre'],
    'email' => $usuario['email']
];

// 6. Redirigir a la página principal
header("Location: main.php");
exit;
