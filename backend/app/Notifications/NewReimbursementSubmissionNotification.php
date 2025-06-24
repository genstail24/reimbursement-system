<?php

namespace App\Notifications;

use App\Models\Reimbursement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Throwable;

class NewReimbursementSubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $reimbursement;

    public function __construct(Reimbursement $reimbursement)
    {
        $this->reimbursement = $reimbursement;
    }

    public function via(object $notifiable): array
    {
        return [
            'mail',
            // 'database',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Reimbursement Submission')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new reimbursement submission has been made by ' . $this->reimbursement->user->name)
            ->line('Title: ' . $this->reimbursement->title)
            ->line('Amount: Rp ' . number_format((int) $this->reimbursement->amount, 0, ',', '.'))
            ->line('Category: ' . $this->reimbursement->category->name)
            ->action('View Details', url('/manager/reimbursements')) // update if frontend route exists
            ->line('Please review it as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reimbursement_id' => $this->reimbursement->id,
            'amount' => $this->reimbursement->amount,
        ];
    }
}
