<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CuentaDesactivada extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $usuario, protected string $mensaje)
    {
        //
    }

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
        return (new MailMessage)
            ->subject(__('Cuenta desactivada'))
            ->greeting(__("Hola :nombre", ['nombre' => $notifiable->nombre()]))
            ->line(__("El usuario :usuario ha desactivado su cuenta.", ['usuario' => $this->usuario->nombre()]))
            ->line(__("El motivo de su baja es la siguiente:"))
            ->line($this->mensaje)
            ->salutation(__('Gracias por usar BeaconsApp'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => __('Un usuario ha desactivado su cuenta'),
            'usuario' => null,
            'mensaje' => [__("El usuario :usuario ha desactivado su cuenta.", ['usuario' => $this->usuario->nombre]), __("El motivo de su baja es la siguiente:"), $this->mensaje],
            'type' => 'light-success', // light-info , light-success, light-danger, light-warning
            'btn' => false,
            'btnTitle' => __('Ir a mi perfil'),
            'url' => ['name' => 'perfil',]
        ];
    }
}
