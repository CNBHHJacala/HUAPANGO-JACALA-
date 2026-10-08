
CREATE TABLE parejas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    numero_pareja INT UNSIGNED NULL,
    folio VARCHAR(40) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    estilo VARCHAR(100) NOT NULL,
    representacion VARCHAR(150) NOT NULL,

    huapanguera_nombre VARCHAR(150) NOT NULL,
    huapanguera_fecha_nacimiento DATE NOT NULL,
    huapanguera_telefono VARCHAR(30) NOT NULL,
    huapanguera_domicilio VARCHAR(255) NOT NULL,

    huapanguero_nombre VARCHAR(150) NOT NULL,
    huapanguero_fecha_nacimiento DATE NOT NULL,
    huapanguero_telefono VARCHAR(30) NOT NULL,
    huapanguero_domicilio VARCHAR(255) NOT NULL,

    estado_pago ENUM('pendiente','aprobado','rechazado','cancelado') NOT NULL DEFAULT 'pendiente',
    estado_registro ENUM('pendiente_pago','confirmado','cancelado') NOT NULL DEFAULT 'pendiente_pago',

    mp_preference_id VARCHAR(100) NULL,
    mp_payment_id VARCHAR(100) NULL,
    mp_status VARCHAR(50) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_numero_pareja (numero_pareja),
    UNIQUE KEY uq_folio (folio),
    KEY idx_mp_payment_id (mp_payment_id),
    KEY idx_estado_registro (estado_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
