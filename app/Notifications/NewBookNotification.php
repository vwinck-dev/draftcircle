<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Book;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class NewBookNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Book $book)
    {
        $this->onQueue('notifications');
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        if ($notifiable instanceof User && $notifiable->email_notifications) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('ui.mail.new_book_subject'))
            ->view([
                'html' => 'emails.library-notification',
                'text' => 'emails.library-notification-text',
            ], [
                'recipientName' => $notifiable instanceof User ? $notifiable->name : trans('ui.brand.name'),
                'eyebrow' => trans('ui.mail.new_book_subject'),
                'heading' => $this->book->title,
                'body' => trans('ui.mail.new_book_line', ['title' => $this->book->title, 'author' => $this->book->author]),
                'detailLabel' => trans('ui.mail.author_label'),
                'detailValue' => $this->book->author,
                'imageUrl' => $this->book->cover_url,
                'actionText' => trans('ui.mail.view_book'),
                'actionUrl' => route('books.show', $this->book),
            ]);
    }

    /** @return array<string, array<string, string>|int|string> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationType::NewBook->value,
            'book_id' => $this->book->id,
            'message_key' => 'mail.new_book_message',
            'message_params' => ['title' => $this->book->title],
        ];
    }
}
