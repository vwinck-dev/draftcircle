<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Fine;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class FineGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Fine $fine)
    {
        $this->onQueue('notifications');
        $locale = $this->fine->user()->value('locale');
        $this->locale(is_string($locale) ? $locale : app()->getLocale());
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
            ->subject(trans('ui.mail.fine_subject'))
            ->view([
                'html' => 'emails.library-notification',
                'text' => 'emails.library-notification-text',
            ], [
                'recipientName' => $notifiable instanceof User ? $notifiable->name : trans('ui.brand.name'),
                'eyebrow' => trans('ui.mail.fine_subject'),
                'heading' => trans('ui.mail.fine_subject'),
                'body' => trans('ui.mail.fine_line', ['amount' => $this->fine->amount]),
                'detailLabel' => trans('ui.mail.amount_label'),
                'detailValue' => $this->fine->amount,
                'imageUrl' => null,
                'actionText' => trans('ui.mail.view_loans'),
                'actionUrl' => route('loans.index'),
            ]);
    }

    /** @return array<string, array<string, string>|int|string> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationType::FineGenerated->value,
            'fine_id' => $this->fine->id,
            'message_key' => 'mail.fine_message',
            'message_params' => ['amount' => $this->fine->amount],
        ];
    }
}
