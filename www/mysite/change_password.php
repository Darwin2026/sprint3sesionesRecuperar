<?php
declare(strict_types=1);
session_start();
require_once "conexion.php";

if (!isset($_SESSION['usuario'])) {
    echo "Debes iniciar sesión para cambiar tu contraseña.";
    exit;
}

$usuario = $_SESSION['usuario'];

$actual = $_POST['actual'] ?? '';
$nueva = $_POST['nueva'] ?? '';
$nueva2 = $_POST['nueva2'] ?? '';

if ($nueva !== $nueva2) {
    echo "Las nuevas contraseñas no coinciden.";
    exit;
}

$stmt = $pdo->prepare("SELECT password FROM tUsuarios WHERE id = :id");
$stmt->execute([':id' => $usuario['id']]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos || !password_verify($actual, $datos['password'])) {
    echo "La contraseña actual no es correcta.";
    exit;
}

$nuevaHash = password_hash($nueva, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE tUsuarios SET password = :pass WHERE id = :id");
$stmt->execute([
    ':pass' => $nuevaHash,
    ':id' => $usuario['id']
]);

$_SESSION['usuario']['password'] = $nuevaHash;

header("Location: main.php");
exit;
