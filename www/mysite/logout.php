<?php
declare(strict_types=1);
session_start();

/*
  Logout seguro:
  - Vacía el array de sesión
  - Borra la cookie de sesión si existe
  - Destruye la sesión
  - Regenera el ID para evitar fijación
*/
$_SESSION = [];

if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
session_start();
session_regenerate_id(true);

// Redirige a la principal
header('Location: /main.php'); // o "http://localhost:8085/main.php"
exit;
