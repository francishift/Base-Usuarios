<?php

/**
 * Bootstrap personalizado para PHPUnit.
 *
 * Suprime la advertencia de open_basedir que genera el paquete
 * laravel/agent-detector al intentar acceder a /opt/.devin en
 * entornos con restricciones (como Plesk), donde ese path está
 * fuera de los permitidos por open_basedir.
 *
 * Sin este handler, PHP convierte el E_WARNING en ErrorException
 * y el runner de tests falla antes de ejecutar ningún test.
 */
set_error_handler(function (int $errno, string $errstr): bool {
    // Suprimir únicamente advertencias de open_basedir de vendor
    if ($errno === E_WARNING && str_contains($errstr, 'open_basedir')) {
        return true; // Advertencia ignorada
    }

    // El resto de errores los gestiona el manejador por defecto
    return false;
});

require __DIR__ . '/../vendor/autoload.php';

// Restaurar el manejador después de la carga del autoloader
restore_error_handler();
