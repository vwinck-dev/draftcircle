<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case ReservationAvailable = 'reservation_available';
    case OverdueReminder = 'overdue_reminder';
    case NewBook = 'new_book';
    case FineGenerated = 'fine_generated';
    case CommentReply = 'comment_reply';
}
