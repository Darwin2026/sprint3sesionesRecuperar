<?php
declare(strict_types=1);
session_start();

// Destruir toda la sesión
session_unset();
session_destroy();

// Redirigir a la página principal
header("Location: main.php");
exit;
