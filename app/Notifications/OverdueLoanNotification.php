<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OverdueLoanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Loan $loan)
    {
        $this->onQueue('notifications');
        $locale = $this->loan->user()->value('locale');
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
        $book = $this->loan->book()->firstOrFail();

        return (new MailMessage)
            ->subject(trans('ui.mail.overdue_subject'))
            ->view([
                'html' => 'emails.library-notification',
                'text' => 'emails.library-notification-text',
            ], [
                'recipientName' => $notifiable instanceof User ? $notifiable->name : trans('ui.brand.name'),
                'eyebrow' => trans('ui.mail.overdue_subject'),
                'heading' => trans('ui.mail.overdue_subject'),
                'body' => trans('ui.mail.overdue_line', ['title' => $book->title]),
                'detailLabel' => trans('ui.mail.book_label'),
                'detailValue' => $book->title,
                'imageUrl' => $book->cover_url,
                'actionText' => trans('ui.mail.view_loans'),
                'actionUrl' => route('loans.index'),
            ]);
    }

    /** @return array<string, array<string, string>|int|string> */
    public function toArray(object $notifiable): array
    {
        $book = $this->loan->book()->firstOrFail();

        return [
            'type' => NotificationType::OverdueReminder->value,
            'loan_id' => $this->loan->id,
            'message_key' => 'mail.overdue_line',
            'message_params' => ['title' => $book->title],
        ];
    }
}
