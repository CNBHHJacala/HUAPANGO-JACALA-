<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$folio = trim((string)($_GET['folio'] ?? ''));
if ($folio === '') {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("SELECT * FROM parejas WHERE folio=? LIMIT 1");
$stmt->execute([$folio]);
$pareja = $stmt->fetch();

if (!$pareja) {
    http_response_code(404);
    exit('Folio no encontrado.');
}

if ($pareja['estado_registro'] === 'confirmado') {
    header('Location: exito.php?folio=' . urlencode($folio));
    exit;
}

$error = null;
$checkoutUrl = null;

try {
    require_once __DIR__ . '/vendor/autoload.php';

    \MercadoPago\MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);
    \MercadoPago\MercadoPagoConfig::setRuntimeEnviroment(\MercadoPago\MercadoPagoConfig::SERVER);

    $client = new \MercadoPago\Client\Preference\PreferenceClient();

    $request = [
        'back_urls' => [
            'success' => siteUrl('exito.php?folio=' . rawurlencode($folio)),
            'pending' => siteUrl('exito.php?folio=' . rawurlencode($folio)),
            'failure' => siteUrl('pagar.php?folio=' . rawurlencode($folio))
        ],
        'notification_url' => siteUrl('webhook.php'),
        'external_reference' => $folio,
        'auto_return' => 'approved',
        'items' => [[
            'title' => 'Inscripción Concurso Nacional de Huapango - ' . $folio,
            'quantity' => 1,
            'currency_id' => 'MXN',
            'unit_price' => (float)INSCRIPTION_PRICE
        ]]
    ];

    $preference = $client->create($request);
    $checkoutUrl = $preference->init_point ?? null;

    if (!empty($preference->id)) {
        $u = db()->prepare("UPDATE parejas SET mp_preference_id=? WHERE folio=?");
        $u->execute([(string)$preference->id, $folio]);
    }

    if (!$checkoutUrl) {
        $error = 'Mercado Pago no devolvió la URL de pago.';
    }
} catch (Throwable $e) {
    error_log('[HUAPANGO_MP] ' . get_class($e) . ': ' . $e->getMessage());
    $error = 'No se pudo preparar el pago. Consulta el registro de errores del servidor.';
}

$pageTitle = 'Pago · ' . $folio;
include __DIR__ . '/header.php';
?>
<main class="container">
<div class="result">
  <div class="card">
    <div class="card-body center">
      <div class="eyebrow">Registro generado</div>
      <h1 class="folio"><?=e($folio)?></h1>
      <p><strong>Número de pareja: <?=e((string)$pareja['numero_pareja'])?></strong></p>
      <span class="status">PENDIENTE DE PAGO</span>

      <?php if ($error): ?>
        <div class="error" style="text-align:left;margin-top:20px"><?=e($error)?></div>
        <a class="btn btn-light" href="index.php">Regresar al registro</a>
      <?php elseif ($checkoutUrl): ?>
        <div class="notice" style="text-align:left;margin-top:25px">
          Tu registro ya tiene folio. Realiza el pago para confirmar oficialmente tu participación.
        </div>
        <div class="price">$<?=number_format(INSCRIPTION_PRICE,2)?> MXN</div>
        <br>
        <a class="btn btn-primary" href="<?=e($checkoutUrl)?>">Pagar inscripción con Mercado Pago →</a>
        <p class="hint" style="margin-top:15px">El sistema confirmará el registro después de recibir y validar la notificación del pago.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
