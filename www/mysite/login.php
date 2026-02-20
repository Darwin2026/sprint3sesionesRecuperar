<?php
declare(strict_types=1);
ini_set('display_errors', '1'); // En prod: '0'
error_reporting(E_ALL);
session_start();

// --- Config BD ---
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

// --- Recoger y validar inputs ---
$email    = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$errores = [];
if ($email === '' || $password === '') {
  $errores[] = 'Email y contraseña son obligatorios.';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errores[] = 'El email no tiene formato válido.';
}

if ($errores) {
  echo '<h3>Errores</h3><ul>';
  foreach ($errores as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
  echo '</ul><p><a href="login.html">Volver al login</a></p>';
  exit;
}

// --- Buscar usuario por email ---
$stmt = $pdo->prepare('SELECT id, nombre, apellidos, email, contraseña FROM tUsuarios WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch();

if (!$usuario) {
  echo '<p>El email no existe.</p><p><a href="login.html">Volver al login</a></p>';
  exit;
}

// --- Verificar contraseña ---
if (!password_verify($password, $usuario['contraseña'])) {
  echo '<p>Contraseña incorrecta.</p><p><a href="login.html">Volver al login</a></p>';
  exit;
}

// --- Login OK: iniciar sesión segura y redirigir ---
session_regenerate_id(true);
$_SESSION['user'] = [
  'id'        => (int)$usuario['id'],
  'nombre'    => (string)$usuario['nombre'],
  'apellidos' => (string)$usuario['apellidos'],
  'email'     => (string)$usuario['email'],
];

header('Location: /main.php'); // o "Location: http://localhost:8085/main.php"
exit;
