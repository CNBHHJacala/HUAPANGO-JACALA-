<?php
require_once __DIR__ . '/config.php';

$folio = trim((string)($_GET['folio'] ?? $_GET['external_reference'] ?? ''));

if ($folio === '') {
    $folio = trim((string)($_GET['external_reference'] ?? ''));
}

$pareja = null;
if ($folio !== '') {
    $stmt = db()->prepare("SELECT * FROM parejas WHERE folio=? LIMIT 1");
    $stmt->execute([$folio]);
    $pareja = $stmt->fetch();
}

$pageTitle = 'Resultado del registro';
include __DIR__ . '/header.php';
?>
<main class="container">
<div class="result">
<div class="card">
<div class="card-body center">
<?php if (!$pareja): ?>
  <h2>Registro no encontrado</h2>
  <p>Verifica el folio.</p>
  <a class="btn btn-primary" href="validar.php">Validar folio</a>
<?php elseif ($pareja['estado_registro'] === 'confirmado'): ?>
  <div class="success-icon">✓</div>
  <div class="eyebrow">Registro confirmado</div>
  <h1 class="folio"><?=e($pareja['folio'])?></h1>
  <span class="status">PAGO APROBADO</span>
  <p>Tu participación ha sido confirmada.</p>

  <div class="summary">
    <div>Número de pareja</div><div><?=e((string)$pareja['numero_pareja'])?></div>
    <div>Categoría</div><div><?=e($pareja['categoria'])?></div>
    <div>Estilo</div><div><?=e($pareja['estilo'])?></div>
    <div>Representación</div><div><?=e($pareja['representacion'])?></div>
    <div>Huapanguera</div><div><?=e($pareja['huapanguera_nombre'])?></div>
    <div>Huapanguero</div><div><?=e($pareja['huapanguero_nombre'])?></div>
  </div>

  <div id="qrcode" class="qrbox"></div>
  <p class="hint">Código QR para validar este folio.</p>
  <br>
  <a class="btn btn-primary" href="boleta.php?folio=<?=urlencode($pareja['folio'])?>">Descargar boleta de inscripción PDF</a>
  <button class="btn btn-light" onclick="window.print()">Imprimir comprobante</button>
  <p class="hint" style="margin-top:12px">La boleta PDF se descargará automáticamente. Si tu navegador bloquea la descarga, usa el botón anterior.</p>

  <iframe id="autoPdf" title="Descarga automática de boleta" style="display:none"></iframe>
  <script>
    window.addEventListener('load', function () {
      setTimeout(function () {
        document.getElementById('autoPdf').src = <?=json_encode('boleta.php?folio=' . rawurlencode($pareja['folio']))?>;
      }, 1200);
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <script>
    new QRCode(document.getElementById("qrcode"), {
      text: <?=json_encode(siteUrl('validar.php?folio=' . $pareja['folio']))?>,
      width: 150,
      height: 150
    });
  </script>
<?php else: ?>
  <div class="notice">El pago todavía no aparece como aprobado. Si acabas de pagar, espera unos segundos y consulta nuevamente.</div>
  <h1 class="folio"><?=e($pareja['folio'])?></h1>
  <p>Estado actual: <strong><?=e($pareja['estado_pago'])?></strong></p>
  <a class="btn btn-primary" href="validar.php?folio=<?=urlencode($pareja['folio'])?>">Consultar nuevamente</a>
<?php endif; ?>
</div>
</div>
</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
