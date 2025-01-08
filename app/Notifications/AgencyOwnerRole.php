<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgencyOwnerRole extends Notification
{
    use Queueable;
    public $userName;
    public $password;
    /**
     * Create a new notification instance.
     */
    public function __construct($userName, $password)
    {
        $this->userName = $userName;
        $this->password = $password;
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
        $url = config('app.url');
        return (new MailMessage)
            ->subject('حسابك الشخصى')
            ->line(__('api.agencyOwner', ["userName" => $this->userName, 'password' => $this->password], 'ar'))
            ->action('Visit Website', $url)
            ->line('شكرا لاستخدامك ' . $appNameAr . ' !')
            ->line('أطيب التمنيات لكم')
            ->salutation("$appNameEn - $appNameAr");
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
