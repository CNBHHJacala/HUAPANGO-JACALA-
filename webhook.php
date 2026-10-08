<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

/*
 * Mercado Pago envía el webhook a esta URL.
 * No se marca un registro como pagado solamente porque el usuario
 * regresó al sitio: se valida la notificación y luego se consulta
 * el pago en la API.
 */

function getHeaderValue(string $name): string {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return $_SERVER[$key] ?? '';
}

function validateWebhookSignature(): bool {
    if (MP_WEBHOOK_SECRET === '') {
        return false;
    }

    $xSignature = getHeaderValue('x-signature');
    $xRequestId = getHeaderValue('x-request-id');
    $dataId = $_GET['data.id'] ?? '';

    if (!$xSignature || !$xRequestId || !$dataId) {
        return false;
    }

    $parts = [];
    foreach (explode(',', $xSignature) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k !== '') $parts[$k] = $v;
    }

    $ts = $parts['ts'] ?? '';
    $v1 = $parts['v1'] ?? '';

    if ($ts === '' || $v1 === '') {
        return false;
    }

    $manifest = 'id:' . $dataId . ';request-id:' . $xRequestId . ';ts:' . $ts . ';';
    $computed = hash_hmac('sha256', $manifest, MP_WEBHOOK_SECRET);

    return hash_equals($v1, $computed);
}

try {
    if (!validateWebhookSignature()) {
        http_response_code(401);
        exit('invalid signature');
    }

    $type = $_GET['type'] ?? '';
    $paymentId = $_GET['data.id'] ?? '';

    if ($type !== 'payment' || $paymentId === '') {
        http_response_code(200);
        exit('ignored');
    }

    require_once __DIR__ . '/vendor/autoload.php';

    \MercadoPago\MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);
    \MercadoPago\MercadoPagoConfig::setRuntimeEnviroment(\MercadoPago\MercadoPagoConfig::SERVER);

    $client = new \MercadoPago\Client\Payment\PaymentClient();
    $payment = $client->get($paymentId);

    $status = (string)($payment->status ?? '');
    $reference = (string)($payment->external_reference ?? '');

    if ($reference === '') {
        http_response_code(200);
        exit('no reference');
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM parejas WHERE folio=? LIMIT 1");
    $stmt->execute([$reference]);
    $pareja = $stmt->fetch();

    if (!$pareja) {
        http_response_code(200);
        exit('folio not found');
    }

    $newPaymentStatus = match ($status) {
        'approved' => 'aprobado',
        'rejected' => 'rechazado',
        'cancelled', 'refunded', 'charged_back' => 'cancelado',
        default => 'pendiente'
    };

    $newRegistrationStatus = $status === 'approved' ? 'confirmado' : $pareja['estado_registro'];

    $u = $pdo->prepare("
        UPDATE parejas
        SET mp_payment_id=?, mp_status=?, estado_pago=?, estado_registro=?
        WHERE folio=?
    ");
    $u->execute([
        (string)$paymentId,
        $status,
        $newPaymentStatus,
        $newRegistrationStatus,
        $reference
    ]);

    http_response_code(200);
    echo 'ok';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error';
}
