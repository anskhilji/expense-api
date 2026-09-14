<?php

namespace App\Notifications;

use App\Models\Invitation;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Invitation $invitation,
        private readonly Organization $organization,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = rtrim(env('FRONTEND_URL'), '/').'/invitations/'.$this->invitation->token;

        return (new MailMessage)
            ->subject("You've been invited to join {$this->organization->name} on Home Expense")
            ->line("You've been invited to join \"{$this->organization->name}\" on Home Expense, a household expense tracker.")
            ->action('Accept Invitation', $url)
            ->line('If you were not expecting this invitation, you can safely ignore this email.');
    }
}