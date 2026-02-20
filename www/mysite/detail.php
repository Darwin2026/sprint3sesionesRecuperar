<?php
declare(strict_types=1);

ini_set('display_errors', '1');  // En prod: '0'
error_reporting(E_ALL);
session_start(); // ✅ necesario para saber si hay usuario logueado

// --- Config DB ---
$host   = 'localhost';
$user   = 'darwin';
$pass   = '1234';
$dbname = 'mysitedb';
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// --- Conexión PDO ---
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Error al conectar con la base de datos.');
}

// --- Validar id ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === null || $id === false) { 
    http_response_code(400); 
    exit('ID inválido.'); 
}

// --- Obtener información del libro ---
$stmt = $pdo->prepare('SELECT id, nombre, autor, `año_publicacion`, url_imagen 
                       FROM tLibros 
                       WHERE id = :id');
$stmt->execute([':id' => $id]);
$libro = $stmt->fetch();

if (!$libro) { 
    exit('Libro no encontrado.'); 
}

// --- Escapar valores antes de mostrar ---
$nombre = htmlspecialchars($libro['nombre'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$autor  = htmlspecialchars($libro['autor'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$anio   = htmlspecialchars((string)($libro['año_publicacion'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$imagen = htmlspecialchars($libro['url_imagen'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

if (!empty($_SESSION['user'])) {
  $nombreFull = htmlspecialchars($_SESSION['user']['nombre'].' '.$_SESSION['user']['apellidos'], ENT_QUOTES, 'UTF-8');
  echo "<div style='background:#f6f6f6;border:1px solid #ddd;padding:8px;border-radius:6px;margin:8px 0'>
          Logueado como {$nombreFull} · 
          <a href=\"/change_password.html\">Cambiar contraseña</a> · 
            <a href=\"/logout.php\">Salir</a>
        </div>";
} else {
  echo '<div style="margin:8px 0"><a href="/login.html">Iniciar sesión</a> · <a href="/register.html">Registrarme</a></div>';
}

// --- Mostrar información del libro ---
echo "<h2>{$nombre}</h2>";
if ($imagen !== '') {
    echo "<img src='{$imagen}' style='width:200px;height:300px;'><br>";
}
echo "<p><strong>Autor:</strong> {$autor}</p>";
echo "<p><strong>Año:</strong> {$anio}</p>";

// --- Comentarios (JOIN con tUsuarios) ---
$csql = "SELECT c.comentario, c.fecha, u.nombre, u.apellidos
         FROM tComentarios c
         JOIN tUsuarios u ON c.usuario_id = u.id
         WHERE c.libro_id = :id
         ORDER BY c.fecha DESC";
$cst = $pdo->prepare($csql);
$cst->execute([':id' => $id]);
$comentarios = $cst->fetchAll();

// --- Mostrar comentarios ---
echo "<h3>Comentarios:</h3>";
if ($comentarios) {
    foreach ($comentarios as $row) {
        $usuario = trim(($row['nombre'] ?? '') . ' ' . ($row['apellidos'] ?? ''));
        $usuario = htmlspecialchars($usuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $fecha   = htmlspecialchars((string)($row['fecha'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $texto   = htmlspecialchars($row['comentario'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<strong>{$usuario}</strong>" . ($fecha ? " ({$fecha})" : "") . ":<br>{$texto}";
        echo "</div>";
    }
} else {
    echo "<p>No hay comentarios aún.</p>";
}
?>

<!-- ✅ Formulario dinámico según si hay sesión iniciada -->
<?php $isLogged = !empty($_SESSION['user']); ?>
<h3>Añadir comentario</h3>
<form method="POST" action="comment.php">
    <input type="hidden" name="libro_id" 
           value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">

    <?php if (!$isLogged): ?>
      <label>Tu nombre:</label><br>
      <input type="text" name="nombre" required><br><br>
    <?php else: ?>
      <p>Publicarás como <strong>
        <?php echo htmlspecialchars($_SESSION['user']['nombre'].' '.$_SESSION['user']['apellidos'], ENT_QUOTES, 'UTF-8'); ?>
      </strong></p>
    <?php endif; ?>

    <label>Comentario:</label><br>
    <textarea name="comentario" rows="4" required></textarea><br><br>

    <button type="submit">Enviar comentario</button>
</form>
