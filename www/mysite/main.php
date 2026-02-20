<?php
declare(strict_types=1);

ini_set('display_errors', '1');   // En producción: '0'
error_reporting(E_ALL);
session_start();

// --- Config DB ---
$host   = 'localhost';
$user   = 'darwin';
$pass   = '1234';
$dbname = 'mysitedb';
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// --- Conexión PDO segura ---
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Error al conectar con la base de datos.');
}

// --- Consulta de libros (sin input del usuario) ---
$sql  = "SELECT id, nombre, autor, `año_publicacion`, url_imagen FROM tLibros ORDER BY id DESC";
$stmt = $pdo->query($sql);
$libros = $stmt ? $stmt->fetchAll() : [];

// --- Helper de escape seguro ---
function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Biblioteca · Sprint 3</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
      --bg:#ffffff; --ink:#222; --muted:#666; --border:#ddd;
      --hover-shadow:0 8px 18px rgba(0,0,0,0.15);
    }
    *{box-sizing:border-box}
    body{
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      background:var(--bg);color:var(--ink);
      margin:0;padding:16px;
    }
    header{
      max-width:1080px;margin:0 auto 16px auto;
      display:flex;gap:12px;align-items:center;justify-content:space-between;
    }
    .brand{font-weight:700;font-size:20px}
    .userbar{
      font-size:14px;background:#f7f7f7;
      border:1px solid var(--border);
      padding:8px 12px;border-radius:10px
    }
    .userbar a{margin-left:8px}
    .actions a{margin-right:10px}
    .grid{
      max-width:1080px;margin:0 auto;
      display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
      gap:14px;
    }
    .card{
      border:1px solid var(--border);
      border-radius:12px;
      padding:12px;text-align:center;
      box-shadow:2px 2px 6px rgba(0,0,0,0.06);
      transition:all .35s ease;       /* transición suave */
      opacity:0;                      /* para fade-in */
      transform:translateY(15px);     /* para desplazamiento inicial */
      animation:appear .6s ease forwards;
    }
    @keyframes appear {
      to {opacity:1;transform:translateY(0);}
    }
    .card:hover{
      transform:scale(1.05) translateY(-4px); /* efecto zoom + elevación */
      box-shadow:var(--hover-shadow);
    }
    .card img{
      width:160px;height:210px;
      object-fit:cover;border-radius:6px;margin:8px 0;
      transition:transform .3s ease;
    }
    .card:hover img{transform:scale(1.08);} /* zoom a la imagen */
    .card h3{margin:6px 0 4px;font-size:18px}
    .card p{margin:2px 0;color:var(--muted);font-size:14px}
    .card a.btn{
      display:inline-block;margin-top:8px;padding:8px 10px;
      border:1px solid var(--border);border-radius:8px;text-decoration:none;
      transition:background .3s ease,color .3s ease;
    }
    .card a.btn:hover{
      background:#222;color:#fff;border-color:#222;
    }
    footer{
      max-width:1080px;margin:20px auto 0 auto;
      font-size:13px;color:#888;text-align:center;
      opacity:0;animation:fadeFooter 1s ease 0.3s forwards;
    }
    @keyframes fadeFooter{
      to{opacity:1;}
  </style>
</head>
<body>
  <header>
    <div class="brand">📚 Biblioteca · Sprint 3</div>
    <div class="actions">
      <?php if (!empty($_SESSION['user'])): ?>
        <span class="userbar">
          Hola, <strong><?= h($_SESSION['user']['nombre'].' '.$_SESSION['user']['apellidos']) ?></strong>
            <a href="/change_password.html">Cambiar contraseña</a>
            <a href="/logout.php">Salir</a>
        </span>
      <?php else: ?>
        <span class="userbar">
          <a href="/login.html">Iniciar sesión</a>
          ·
          <a href="/register.html">Registrarme</a>
        </span>
      <?php endif; ?>
    </div>
  </header>

  <section class="grid">
    <?php if ($libros && count($libros) > 0): ?>
      <?php foreach ($libros as $row): ?>
        <?php
          $id    = (int)($row['id'] ?? 0);
          $img   = h($row['url_imagen'] ?? '');
          $nom   = h($row['nombre'] ?? '');
          $autor = h($row['autor'] ?? '');
          $anio  = h((string)($row['año_publicacion'] ?? ''));
        ?>
        <article class="card">
          <?php if ($img !== ''): ?>
            <img src="<?= $img ?>" alt="Portada">
          <?php endif; ?>
          <h3><?= $nom ?></h3>
          <p><strong>Autor:</strong> <?= $autor ?></p>
          <p><strong>Año:</strong> <?= $anio ?></p>
          <a class="btn" href="detail.php?id=<?= urlencode((string)$id) ?>">Ver más</a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay libros disponibles.</p>
    <?php endif; ?>
  </section>

  <footer>
    <?php if (!empty($_SESSION['user'])): ?>
      Estás logueado como <?= h($_SESSION['user']['email']) ?>.
    <?php else: ?>
      No has iniciado sesión.
    <?php endif; ?>
  </footer>
</body>
</html>
