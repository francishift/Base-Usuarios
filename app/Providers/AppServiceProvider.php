<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Nota: interceptamos el error handler de Laravel para suprimir las
     * advertencias open_basedir que genera laravel/agent-detector al intentar
     * acceder a /opt/.devin en entornos con restricciones (Plesk).
     *
     * El handler de Laravel se captura y se encadena, de modo que el resto
     * de errores siguen siendo gestionados con normalidad.
     */
    public function register(): void
    {
        // Solo actuar si open_basedir está activo (entornos restringidos como Plesk)
        if (ini_get('open_basedir')) {
            // Capturar el handler actual de Laravel
            $laravelHandler = set_error_handler(null);
            restore_error_handler();

            // Registrar nuestro handler por encima — se ejecuta primero
            set_error_handler(
                function (int $errno, string $errstr, string $errfile, int $errline) use ($laravelHandler): bool {
                    // Suprimir solo advertencias de open_basedir
                    if ($errno === E_WARNING && str_contains($errstr, 'open_basedir')) {
                        return true;
                    }
                    // Delegar cualquier otro error al handler de Laravel
                    if (is_callable($laravelHandler)) {
                        return (bool) $laravelHandler($errno, $errstr, $errfile, $errline);
                    }
                    return false;
                }
            );
        }
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Reglas de seguridad para contraseñas de usuarios
        \Illuminate\Validation\Rules\Password::defaults(function () {
            $rule = \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return app()->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });

        Vite::prefetch(concurrency: 3);
    }
}
