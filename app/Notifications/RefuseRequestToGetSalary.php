<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefuseRequestToGetSalary extends Notification
{
    use Queueable;
    public $amount;
    public $reason;
    /**
     * Create a new notification instance.
     */
    public function __construct($amount, $reason)
    {
        $this->amount = $amount;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appNameEn = config('app.name_en');
        $appNameAr = config('app.name_ar');
        return (new MailMessage)
            ->subject('سحب راتب')
            ->line(__('api.denied_request_sallary', ["value" => $this->amount, 'reason' => $this->reason], 'ar'))
            ->line($appNameAr . 'شكرا لاستخدامك  !')
            ->line('أطيب التمنيات لكم')
            ->salutation($appNameEn . " - " . $appNameAr);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
