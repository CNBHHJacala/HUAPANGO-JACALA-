<?php
require_once __DIR__ . '/config.php';
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="brand">
    <div class="brand-mark">✦</div>
    <div>
      <strong>CONCURSO NACIONAL DE HUAPANGO</strong>
      <span>JACALA DE LEDEZMA</span>
    </div>
  </div>
  <nav>
    <a href="index.php">Registro</a>
    <a href="validar.php">Validar folio</a>
  </nav>
</header>
