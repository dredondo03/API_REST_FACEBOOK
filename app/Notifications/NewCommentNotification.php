<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    // Recibe el comentario que se acaba de crear
    public function __construct(public Comment $comment)
    {
    }

    // Canal de envío: correo electrónico
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    // Contenido del correo
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo comentario en tu publicación')
            ->greeting('¡Hola, ' . $notifiable->name . '!')
            ->line($this->comment->author . ' comentó tu publicación "' . $this->comment->post->title . '":')
            ->line('"' . $this->comment->content . '"')
            ->action('Ver publicación', url('/api/v1/posts/' . $this->comment->post_id))
            ->line('Gracias por usar nuestra red social.');
    }
}