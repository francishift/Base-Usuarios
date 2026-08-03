<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BienvenidaUsuario extends Notification
{
    use Queueable;

    public function __construct(
        protected string $passwordTemporal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Bienvenido/a a {$appName} — Tus credenciales de acceso")
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Tu cuenta en **{$appName}** ha sido creada correctamente.")
            ->line('Tus credenciales de acceso son:')
            ->line("**Correo electrónico:** {$notifiable->email}")
            ->line("**Contraseña temporal:** {$this->passwordTemporal}")
            ->action('Acceder al panel', url('/login'))
            ->line('Por seguridad, te recomendamos cambiar tu contraseña tras el primer acceso.')
            ->line('Si no esperabas este mensaje, ignóralo o contacta con el administrador.')
            ->salutation("El equipo de {$appName}");
    }
}
