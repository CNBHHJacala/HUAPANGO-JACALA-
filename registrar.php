<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$fields = [
    'categoria','estilo','representacion',
    'huapanguera_nombre','huapanguera_fecha_nacimiento','huapanguera_telefono','huapanguera_domicilio',
    'huapanguero_nombre','huapanguero_fecha_nacimiento','huapanguero_telefono','huapanguero_domicilio'
];

$data = [];
foreach ($fields as $f) {
    $data[$f] = trim((string)($_POST[$f] ?? ''));
}

foreach ($data as $key => $value) {
    if ($value === '') {
        die('Falta un dato obligatorio. <a href="index.php">Regresar</a>');
    }
}

if (!in_array($data['categoria'], CATEGORIES, true) || !in_array($data['estilo'], STYLES, true)) {
    die('Categoría o estilo inválido.');
}

foreach (['huapanguera_fecha_nacimiento','huapanguero_fecha_nacimiento'] as $dateField) {
    $d = DateTime::createFromFormat('Y-m-d', $data[$dateField]);
    if (!$d || $d->format('Y-m-d') !== $data[$dateField]) {
        die('Fecha inválida.');
    }
}

$pdo = db();

try {
    $pdo->beginTransaction();

    /*
     * INSERT con AUTO_INCREMENT para obtener un número único.
     * El número de pareja se deriva del ID. Si se requiere reiniciar por edición,
     * se puede cambiar posteriormente a un contador anual.
     */
    $stmt = $pdo->prepare("
        INSERT INTO parejas (
            numero_pareja, folio, categoria, estilo, representacion,
            huapanguera_nombre, huapanguera_fecha_nacimiento, huapanguera_telefono, huapanguera_domicilio,
            huapanguero_nombre, huapanguero_fecha_nacimiento, huapanguero_telefono, huapanguero_domicilio
        ) VALUES (NULL, 'PENDING', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    /*
     * Necesitamos una fila primero para obtener ID. Luego asignamos el folio.
     * numero_pareja se hace igual al ID para garantizar unicidad.
     */
    $stmt->execute([
        $data['categoria'], $data['estilo'], $data['representacion'],
        $data['huapanguera_nombre'], $data['huapanguera_fecha_nacimiento'], $data['huapanguera_telefono'], $data['huapanguera_domicilio'],
        $data['huapanguero_nombre'], $data['huapanguero_fecha_nacimiento'], $data['huapanguero_telefono'], $data['huapanguero_domicilio']
    ]);

    $id = (int)$pdo->lastInsertId();
    $numero = $id;
    $folio = generateFolio($numero);

    $up = $pdo->prepare("UPDATE parejas SET numero_pareja=?, folio=? WHERE id=?");
    $up->execute([$numero, $folio, $id]);

    $pdo->commit();

    header('Location: pagar.php?folio=' . urlencode($folio));
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo 'No fue posible guardar el registro. Revisa la configuración del servidor.';
}
