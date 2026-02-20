<?php
declare(strict_types=1);
ini_set('display_errors', '1'); // En producción: '0'
error_reporting(E_ALL);
session_start();

// Requiere usuario logueado
if (empty($_SESSION['user']['id'])) {
  http_response_code(401);
  exit('Debes iniciar sesión para cambiar la contraseña. <a href="/login.html">Ir a login</a>');
}

// Config BD (mismo patrón que el resto)
$host   = 'localhost';
$user   = 'darwin';
$pass   = '1234';
$dbname = 'mysitedb';
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  exit('Error de conexión.');
}

// Entradas
$old = (string)($_POST['old_password'] ?? '');
$n1  = (string)($_POST['new_password'] ?? '');
$n2  = (string)($_POST['new_password2'] ?? '');

$errores = [];
if ($old === '' || $n1 === '' || $n2 === '') { $errores[] = 'Hay campos vacíos.'; }
if ($n1 !== $n2) { $errores[] = 'Las nuevas contraseñas no coinciden.'; }
if (strlen($n1) < 6) { $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.'; }

if ($errores) {
  echo '<h3>Errores</h3><ul>';
  foreach ($errores as $e) echo '<li>'.htmlspecialchars($e, ENT_QUOTES, 'UTF-8').'</li>';
  echo '</ul><p><a href="change_password.html">Volver</a></p>';
  exit;
}

// Cargar hash actual del usuario
$uid = (int)$_SESSION['user']['id'];
$stmt = $pdo->prepare('SELECT contraseña FROM tUsuarios WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch();
if (!$user) {
  http_response_code(404);
  exit('Usuario no encontrado.');
}

// Verificar contraseña actual
if (!password_verify($old, $user['contraseña'])) {
  echo '<p>La contraseña actual no es correcta.</p><p><a href="change_password.html">Volver</a></p>';
  exit;
}

// Evitar reutilizar la misma
if (password_verify($n1, $user['contraseña'])) {
  echo '<p>La nueva contraseña no puede ser igual a la actual.</p><p><a href="change_password.html">Volver</a></p>';
  exit;
}

// Actualizar hash
$newHash = password_hash($n1, PASSWORD_DEFAULT);
$upd = $pdo->prepare('UPDATE tUsuarios SET contraseña = :h WHERE id = :id');
$upd->execute([':h' => $newHash, ':id' => $uid]);

// Endurecer sesión tras cambio sensible
session_regenerate_id(true);

echo '<p>Contraseña actualizada correctamente.</p><p><a href="/main.php">Volver a la principal</a></p>';
