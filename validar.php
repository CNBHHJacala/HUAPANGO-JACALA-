<?php
require_once __DIR__ . '/config.php';

$folio = trim((string)($_GET['folio'] ?? ''));
$pareja = null;

if ($folio !== '') {
    $stmt = db()->prepare("
        SELECT numero_pareja, folio, categoria, estilo, representacion,
               huapanguera_nombre, huapanguero_nombre, estado_pago, estado_registro, created_at
        FROM parejas WHERE folio=? LIMIT 1
    ");
    $stmt->execute([$folio]);
    $pareja = $stmt->fetch();
}

$pageTitle = 'Validar registro';
include __DIR__ . '/header.php';
?>
<section class="hero">
  <div class="eyebrow">Consulta pública</div>
  <h1>Validar <span style="color:var(--turquesa)">registro</span></h1>
  <p>Ingresa el folio de la pareja para comprobar si su inscripción está confirmada.</p>
</section>
<main class="container">
<div class="card">
<div class="card-body">
<form class="search" method="get">
  <input name="folio" value="<?=e($folio)?>" placeholder="Ej. HJP-2026-0001" required>
  <button class="btn btn-primary">Consultar</button>
</form>

<?php if ($folio !== ''): ?>
  <?php if ($pareja && $pareja['estado_registro'] === 'confirmado'): ?>
    <div class="notice" style="margin-top:25px;background:#e9f8f1;border-left-color:var(--turquesa);color:#185c43">
      ✓ Registro válido y pago aprobado.
    </div>
    <div class="summary">
      <div>Folio</div><div><?=e($pareja['folio'])?></div>
      <div>Número de pareja</div><div><?=e((string)$pareja['numero_pareja'])?></div>
      <div>Categoría</div><div><?=e($pareja['categoria'])?></div>
      <div>Estilo</div><div><?=e($pareja['estilo'])?></div>
      <div>Representación</div><div><?=e($pareja['representacion'])?></div>
      <div>Huapanguera</div><div><?=e($pareja['huapanguera_nombre'])?></div>
      <div>Huapanguero</div><div><?=e($pareja['huapanguero_nombre'])?></div>
      <div>Estado</div><div><span class="status">CONFIRMADO</span></div>
    </div>
  <?php elseif ($pareja): ?>
    <div class="notice" style="margin-top:25px">
      El folio existe, pero la inscripción todavía no está confirmada porque el pago no aparece como aprobado.
    </div>
  <?php else: ?>
    <div class="error" style="margin-top:25px">No se encontró un registro con ese folio.</div>
  <?php endif; ?>
<?php endif; ?>
</div>
</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
