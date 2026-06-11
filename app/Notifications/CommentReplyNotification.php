<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\BookComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CommentReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly BookComment $reply)
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
        $book = $this->reply->book()->firstOrFail();
        $replier = $this->reply->user()->firstOrFail();

        return (new MailMessage)
            ->subject(trans('ui.mail.comment_reply_subject'))
            ->view([
                'html' => 'emails.library-notification',
                'text' => 'emails.library-notification-text',
            ], [
                'recipientName' => $notifiable instanceof User ? $notifiable->name : trans('ui.brand.name'),
                'eyebrow' => trans('ui.mail.comment_reply_subject'),
                'heading' => trans('ui.mail.comment_reply_heading', ['title' => $book->title]),
                'body' => trans('ui.mail.comment_reply_body', ['name' => $replier->name]),
                'detailLabel' => trans('ui.mail.comment_label'),
                'detailValue' => $this->reply->body,
                'imageUrl' => $book->cover_url,
                'actionText' => trans('ui.mail.view_book'),
                'actionUrl' => route('books.show', $book),
            ]);
    }

    /** @return array<string, array<string, string>|int|string> */
    public function toArray(object $notifiable): array
    {
        $replier = $this->reply->user()->firstOrFail();

        return [
            'type' => NotificationType::CommentReply->value,
            'comment_id' => $this->reply->id,
            'book_id' => $this->reply->book_id,
            'message_key' => 'mail.comment_reply_body',
            'message_params' => ['name' => $replier->name],
        ];
    }
}
