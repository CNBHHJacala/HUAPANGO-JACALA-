<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$folio = trim((string)($_GET['folio'] ?? ''));
if ($folio === '') {
    http_response_code(400);
    exit('Folio requerido.');
}

$stmt = db()->prepare("SELECT * FROM parejas WHERE folio=? LIMIT 1");
$stmt->execute([$folio]);
$pareja = $stmt->fetch();

if (!$pareja) {
    http_response_code(404);
    exit('Registro no encontrado.');
}

if ($pareja['estado_registro'] !== 'confirmado' || $pareja['estado_pago'] !== 'aprobado') {
    http_response_code(403);
    exit('La boleta solo está disponible para registros confirmados y pagados.');
}

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;

function fechaMx(string $date): string {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
}

$validationUrl = siteUrl('validar.php?folio=' . rawurlencode($pareja['folio']));
$qrDataUri = (new QRCode())->render($validationUrl);

$html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><style>
@page{margin:18px 24px 20px}body{font-family:DejaVu Sans,sans-serif;color:#2c2020;font-size:9.7px;margin:0}
.frame{border:4px solid #721c35;padding:5px}.inner{border:1.5px solid #d5a33a;padding:11px 14px 10px;position:relative}
.ornament{text-align:center;color:#d5a33a;font-size:12px;letter-spacing:6px;line-height:1}.header{text-align:center;border-bottom:2px solid #0f8f8a;padding-bottom:7px;margin-bottom:8px}.kicker{font-size:8px;letter-spacing:2px;color:#0f8f8a;font-weight:bold}.title{font-family:DejaVu Serif,serif;font-size:21px;color:#4b1223;font-weight:bold;margin:3px 0 0}.subtitle{font-size:10px;color:#6d6460;margin-top:2px}.badge{display:inline-block;background:#721c35;color:#fff;padding:4px 9px;margin-top:5px;font-weight:bold;font-size:8px;letter-spacing:.6px}
.meta{width:100%;border-collapse:collapse;margin:6px 0 7px}.meta td{border:1px solid #e1d1bf;padding:5px}.label{background:#fff7e8;font-weight:bold;color:#721c35;width:22%}.section{font-size:11px;font-weight:bold;color:#721c35;border-left:5px solid #0f8f8a;padding:3px 6px;margin:7px 0 4px;background:#fff9ef}.data{width:100%;border-collapse:collapse}.data td{border:1px solid #e1d1bf;padding:5px;vertical-align:top}.data .label{width:24%}
.photos{width:100%;border-collapse:separate;border-spacing:10px 3px;margin-top:4px}.photo{height:108px;border:2px dashed #9a8a7d;text-align:center;vertical-align:middle;color:#7a6d64;font-weight:bold;background:#fffdfa}.photo small{font-weight:normal}.note{text-align:center;font-size:7.5px;color:#6d6460;margin-top:3px}
.verify{width:100%;margin-top:7px;border-collapse:collapse}.verify td{vertical-align:middle}.qr{width:76px;text-align:center}.qr img{width:68px;height:68px}.verifytext{padding-left:8px;font-size:8px;color:#5f5550;line-height:1.45}.verifytext b{color:#721c35;font-size:9px}.signatures{width:100%;border-collapse:separate;border-spacing:15px 0;margin-top:12px}.signatures td{width:50%;text-align:center;vertical-align:bottom}.line{border-top:1px solid #4b1223;padding-top:4px;font-size:8px;font-weight:bold}.stamp{height:38px;color:#8b817a;font-size:7.5px;text-align:center;padding-top:4px}.footer{margin-top:5px;border-top:1px solid #d5a33a;padding-top:4px;text-align:center;font-size:7px;color:#746963}.folio{font-size:13px;color:#721c35;font-weight:bold}
</style></head><body><div class="frame"><div class="inner">
<div class="ornament">◆ ✦ ◆ ✦ ◆</div><div class="header"><div class="kicker">JACALA DE LEDEZMA, HIDALGO</div><div class="title">CONCURSO NACIONAL DE HUAPANGO</div><div class="subtitle">BOLETA OFICIAL DE INSCRIPCIÓN DE PAREJA</div><div class="badge">REGISTRO CONFIRMADO · PAGO APROBADO</div></div>
<table class="meta"><tr><td class="label">Folio</td><td class="folio">'.e($pareja['folio']).'</td><td class="label">No. de pareja</td><td><b>'.e((string)$pareja['numero_pareja']).'</b></td></tr><tr><td class="label">Categoría</td><td>'.e($pareja['categoria']).'</td><td class="label">Estilo</td><td>'.e($pareja['estilo']).'</td></tr><tr><td class="label">Representación</td><td colspan="3">'.e($pareja['representacion']).'</td></tr></table>
<div class="section">Huapanguera</div><table class="data"><tr><td class="label">Nombre completo</td><td>'.e($pareja['huapanguera_nombre']).'</td></tr><tr><td class="label">Fecha de nacimiento</td><td>'.e(fechaMx($pareja['huapanguera_fecha_nacimiento'])).'</td><td class="label">Teléfono</td><td>'.e($pareja['huapanguera_telefono']).'</td></tr><tr><td class="label">Domicilio</td><td colspan="3">'.e($pareja['huapanguera_domicilio']).'</td></tr></table>
<div class="section">Huapanguero</div><table class="data"><tr><td class="label">Nombre completo</td><td>'.e($pareja['huapanguero_nombre']).'</td></tr><tr><td class="label">Fecha de nacimiento</td><td>'.e(fechaMx($pareja['huapanguero_fecha_nacimiento'])).'</td><td class="label">Teléfono</td><td>'.e($pareja['huapanguero_telefono']).'</td></tr><tr><td class="label">Domicilio</td><td colspan="3">'.e($pareja['huapanguero_domicilio']).'</td></tr></table>
<div class="section">Fotografías de la pareja</div><table class="photos"><tr><td class="photo">PEGAR FOTO<br>HUAPANGUERA<br><small>Fotografía reciente</small></td><td class="photo">PEGAR FOTO<br>HUAPANGUERO<br><small>Fotografía reciente</small></td></tr></table><div class="note">Pegue una fotografía de cada participante dentro del recuadro correspondiente.</div>
<table class="verify"><tr><td class="qr"><img src="'.e($qrDataUri).'" alt="QR"></td><td class="verifytext"><b>VERIFICACIÓN DIGITAL</b><br>Escanee este código QR para comprobar la autenticidad y el estado de la inscripción.<br>Folio de validación: <b>'.e($pareja['folio']).'</b></td></tr></table>
<table class="signatures"><tr><td><div class="stamp">ESPACIO PARA SELLO<br>DEL COMITÉ ORGANIZADOR</div><div class="line">SELLO / VALIDACIÓN</div></td><td><div class="stamp"></div><div class="line">FIRMA DEL COMITÉ ORGANIZADOR</div></td></tr></table>
<div class="footer">Documento generado por el sistema de registro del Concurso Nacional de Huapango · La autenticidad se verifica mediante el código QR.</div>
</div></div></body></html>';

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream('Boleta_Inscripcion_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $pareja['folio']) . '.pdf', ['Attachment' => true]);
