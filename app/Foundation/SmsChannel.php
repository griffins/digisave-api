<?php

namespace App\Foundation;

use App\Foundation\AfricasTalking\Client;
use Illuminate\Notifications\Notification;


class SmsChannel
{
    /**
     * The AfricasTalking client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * The phone number notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new Sms channel instance.
     *
     * @param Client $client
     * @param  string $from
     */
    public function __construct(Client $client, $from)
    {
        $this->from = $from;
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return boolean
     */
    public function send($notifiable, Notification $notification)
    {

        if (!$to = $notifiable->routeNotificationFor('nexmo', $notification)) {
            return false;
        }

        $message = $notification->toSms($notifiable);

        if (is_string($message)) {
            $message = new SmsMessage($message);
        }
        return $this->client->message()->send([
            'type' => $message->type,
            'from' => $message->from ?: $this->from,
            'to' => $to,
            'text' => trim($message->content),
        ]);
    }
}