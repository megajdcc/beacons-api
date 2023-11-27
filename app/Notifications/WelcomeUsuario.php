<?php

namespace App\Notifications;

use App\Mail\UsuarioCreado;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUsuario extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected User $usuario, protected string $url = 'beacons.dev')
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
                ->subject("Bienvenido {$this->usuario->nombre} a BeaconsTravel")
                ->greeting("Hola {$this->usuario->nombre}")
                ->line("Has recibido autorización para ingresar al Sistema de ". env('APP_NAME') )
                ->line("Tus credenciales son:")
                ->line("Usuario:".$this->usuario->email)
                ->action("Establezca una contraseña para poder entrar al sistema", $this->url . '/usuario/' . $this->usuario->id . '/establecer/contrasena')
                ->salutation('Te damos la Bienvenida al equipo de Beacons App');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => __("Bienvenido a :sistema",['sistema' => env('APP_NAME')]),
            'avatar' => null,
            'usuario' => null,
            'mensaje' => [__("Hola :nombre .Bienvenido/a a BeaconsApp. Estamos listos para acompañarte en este viaje.",['nombre' => $notifiable->nombre()])],
            'type' => 'light-success', // light-info , light-success, light-danger, light-warning
            'btn' => true,
            'btnTitle' => __("Ir a mi perfil"),
            'url' => ['name' => 'perfil.editar',]
        ];
    }
}
