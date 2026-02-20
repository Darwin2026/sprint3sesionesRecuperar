<?php
declare(strict_types=1);
session_start();
require_once "conexion.php";

// 1. Validar datos recibidos
if (empty($_POST['comentario']) || empty($_POST['libro_id'])) {
    echo "Faltan datos para enviar el comentario.";
    exit;
}

$comentario = trim($_POST['comentario']);
$libro_id = (int) $_POST['libro_id'];

// 2. Comprobar si el usuario está logueado
$usuario = $_SESSION['usuario'] ?? null;

if ($usuario) {
    // Usuario logueado → insertar con usuario_id
    $stmt = $pdo->prepare("
        INSERT INTO tComentarios (libro_id, comentario, usuario_id)
        VALUES (:libro_id, :comentario, :usuario_id)
    ");
    $stmt->execute([
        ':libro_id' => $libro_id,
        ':comentario' => $comentario,
        ':usuario_id' => $usuario['id']
    ]);
} else {
    // Usuario NO logueado → insertar sin usuario_id
    $stmt = $pdo->prepare("
        INSERT INTO tComentarios (libro_id, comentario)
        VALUES (:libro_id, :comentario)
    ");
    $stmt->execute([
        ':libro_id' => $libro_id,
        ':comentario' => $comentario
    ]);
}

// 3. Redirigir de vuelta al detalle del libro
header("Location: detail.php?id=" . $libro_id);
exit;
