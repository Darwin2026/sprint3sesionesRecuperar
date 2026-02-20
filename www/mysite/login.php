<?php
declare(strict_types=1);
session_start();

require_once "conexion.php";

if (empty($_POST['email']) || empty($_POST['password'])) {
    echo "Debes rellenar todos los campos.";
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM tUsuarios WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "El email no existe.";
    exit;
}

if (!password_verify($password, $usuario['contraseña'])) {
    echo "Contraseña incorrecta.";
    exit;
}

$_SESSION['usuario'] = [
    'id' => $usuario['id'],
    'nombre' => $usuario['nombre'],
    'email' => $usuario['email']
];

header("Location: main.php");
exit;
