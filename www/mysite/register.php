<?php
declare(strict_types=1);
ini_set('display_errors', '1'); // En prod: '0'
error_reporting(E_ALL);

// ---- Config DB (igual que el resto del proyecto) ----
$host   = 'localhost';
$user   = 'darwin';
$pass   = '1234';
$dbname = 'mysitedb';
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  exit('Error de conexión.');
}

// ---- Recoger y validar ----
$nombre     = trim((string)($_POST['nombre'] ?? ''));
$apellidos  = trim((string)($_POST['apellidos'] ?? ''));
$email      = trim((string)($_POST['email'] ?? ''));
$password   = (string)($_POST['password'] ?? '');
$password2  = (string)($_POST['password2'] ?? '');

$errores = [];

// Campos vacíos
if ($nombre === '' || $apellidos === '' || $email === '' || $password === '' || $password2 === '') {
  $errores[] = 'Hay campos obligatorios vacíos.';
}

// Email válido
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errores[] = 'El email no tiene un formato válido.';
}

// Contraseñas iguales + longitud mínima
if ($password !== $password2) {
  $errores[] = 'Las contraseñas no coinciden.';
}
if (strlen($password) < 6) {
  $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
}

// Si hay errores de validación, muéstralos
if ($errores) {
  echo '<h3>Errores en el registro</h3><ul>';
  foreach ($errores as $e) {
    echo '<li>' . htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
  }
  echo '</ul><p><a href="register.html">Volver al formulario</a></p>';
  exit;
}

// ---- Comprobar si el email ya existe ----
$check = $pdo->prepare('SELECT id FROM tUsuarios WHERE email = :email LIMIT 1');
$check->execute([':email' => $email]);
if ($check->fetch()) {
  echo '<p>El correo ya existe en la base de datos.</p>';
  echo '<p><a href="register.html">Volver al formulario</a></p>';
  exit;
}

// ---- Insertar usuario con contraseña hasheada ----
$hash = password_hash($password, PASSWORD_DEFAULT);

$ins = $pdo->prepare('INSERT INTO tUsuarios (nombre, apellidos, email, contraseña)
                      VALUES (:nombre, :apellidos, :email, :pwd)');
$ins->execute([
  ':nombre'    => $nombre,
  ':apellidos' => $apellidos,
  ':email'     => $email,
  ':pwd'       => $hash
]);

// ---- Redirigir a la principal si todo OK ----
header('Location: /main.php');  // o "Location: http://localhost:8085/main.php"
exit;
