<?php
declare(strict_types=1);



const DB_HOST = 'localhost';
const DB_NAME = 'jacala_huapango';
const DB_USER = 'jacala_huapango';
const DB_PASS = 'Jacala26@';

const SITE_URL = 'https://jacala.gob.mx/CNBHH';

/*
 * Precio de inscripción por pareja.
 
 */
const INSCRIPTION_PRICE = 250.00;

/*
 * configuracion banco Mercado Pago

 */
const MP_ACCESS_TOKEN = 'APP_USR-907687927421610-100617-00b5fcfc35e3fe8f00dde36cc0b2ab8e-517657724';
const MP_WEBHOOK_SECRET = 'https://jacala.gob.mx/CNBHH/webhook.php';

/*
 * Credenciales de administración.

 */
const ADMIN_USER = 'admin';
const ADMIN_PASSWORD = '123';

const CATEGORIES = [
    'Pequeños Huapangueros',
    'Infantil',
    'Juvenil',
    'Adulto'
];

const STYLES = [
    'Queretano',
    'Tamaulipeco',
    'Veracruzano',
    'Hidalguense',
    'Potosino',
    'Poblano'
];

function db(): PDO {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    return $pdo;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function siteUrl(string $path = ''): string {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function generateFolio(int $numero): string {
    return 'HJP-' . date('Y') . '-' . str_pad((string)$numero, 4, '0', STR_PAD_LEFT);
}
