<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    protected $order;


    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        // 只使用 email
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        return (new MailMessage)
            ->subject("訂單已成立 (#{$order->id})")
            ->greeting("您好 {$notifiable->name}")
            ->line("感謝您的訂單，我們已收到訂單 #{$order->id}。")
            ->line("總金額： $" . number_format($order->total_price, 2))
            ->action('查看訂單', url(route('orders.show', $order->id)))
            ->line('如有問題請聯絡客服。');
    }
}
