<?php

namespace App\Notifications;

use App\Models\SavingsGoal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SavingsGoalLaggingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected SavingsGoal $goal,
        protected array $progress,
        protected string $message
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $remaining = number_format($this->progress['amount_remaining'] ?? 0, 0);
        $percentage = number_format($this->progress['percentage'] ?? 0, 1);
        $targetDate = $this->goal->target_date?->format('M j, Y') ?? 'your target date';

        return (new MailMessage)
            ->subject('Keep Going on Your Savings Goal')
            ->greeting('Hello ' . ($notifiable->name ?? 'Member'))
            ->line("You're currently at {$percentage}% of your '{$this->goal->title}' savings goal.")
            ->line("You still need UGX {$remaining} before {$targetDate}.")
            ->line($this->message)
            ->action('Review Your Goal', url('/member/savings-goals/' . $this->goal->id))
            ->line('We believe you can reach this goal. Stay consistent and reach out if you need support.');
    }
}
