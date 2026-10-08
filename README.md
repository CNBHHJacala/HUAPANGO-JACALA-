# Registro Concurso de Huapango de Jacala

Sistema PHP + MySQL para:
- Registrar parejas.
- Generar automáticamente número de pareja y folio.
- Cobrar la inscripción mediante Mercado Pago Checkout Pro.
- Recibir Webhooks de Mercado Pago y marcar el pago como aprobado.
- Consultar/validar un folio públicamente.
- Mostrar un QR de validación en la constancia digital.
- Consultar registros desde una zona administrativa sencilla.

## Requisitos
- PHP 8.2 o superior.
- MySQL/MariaDB.
- HTTPS en producción.
- Composer.
- Cuenta de vendedor de Mercado Pago.

El SDK PHP oficial actual de Mercado Pago requiere PHP 8.2+.

## Instalación

1. Copia la carpeta al servidor.
2. Crea una base de datos MySQL.
3. Importa `database.sql`.
4. Ejecuta:
   composer require mercadopago/dx-php:3.16.0
5. Edita `config.php`:
   - datos de MySQL
   - URL pública
   - precio de inscripción
   - categorías
   - estilos
   - Access Token de Mercado Pago
   - Secret de Webhook
   - usuario/contraseña del administrador
6. Abre `index.php`.
7. En Mercado Pago configura Webhooks:
   `https://TU-DOMINIO.com/webhook.php`
   y activa el evento de pagos.
8. Prueba primero con credenciales de prueba.
9. Cuando todo funcione, cambia a credenciales productivas.

## Importante
El pago NO se confirma por la URL de regreso. El sistema confirma el pago mediante el Webhook y consulta el pago en Mercado Pago. Así el participante no puede falsificar una pantalla de "pago aprobado".

## Categorías y estilos
En `config.php` dejé cuatro categorías y seis estilos de ejemplo. Modifícalos según la convocatoria oficial.

## Seguridad
- Las credenciales privadas de Mercado Pago nunca deben ir en HTML/JS.
- Usa HTTPS.
- Cambia la contraseña administrativa.
- No publiques `config.php`.


### Personalización pendiente antes de producción

En `config.php` cambia:
- `INSCRIPTION_PRICE`
- `CATEGORIES`
- `STYLES`
- `SITE_URL`
- datos MySQL
- `MP_ACCESS_TOKEN`
- `MP_WEBHOOK_SECRET`
- `ADMIN_USER`
- `ADMIN_PASSWORD`

El diseño ya está preparado para escritorio y celular.

## Configuración oficial cargada
- Evento: Concurso Nacional de Huapango
- Costo: $250.00 MXN por pareja
- Categorías: Pequeños Huapangueros, Infantil, Juvenil, Adulto
- Estilos: Queretano, Tamaulipeco, Veracruzano, Hidalguense, Potosino, Poblano

## Boleta PDF automática
Al confirmarse el pago, `exito.php` inicia automáticamente la descarga de `boleta.php`.
La boleta solo puede generarse cuando `estado_registro=confirmado` y `estado_pago=aprobado`.
Incluye todos los datos de inscripción y dos recuadros para pegar las fotografías de la huapanguera y del huapanguero.

Instala dependencias con:
```bash
composer install
```
Esto instala Mercado Pago y Dompdf.


## Boleta oficial mejorada
La boleta PDF incluye marco institucional/folklórico, datos completos de la pareja, dos espacios para fotografías, QR de verificación del folio, espacio para sello y firma del comité organizador. El QR se genera localmente en PHP y no depende de un servicio externo.
