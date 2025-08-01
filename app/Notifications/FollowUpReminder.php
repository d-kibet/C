<?php
// app/Notifications/FollowUpReminder.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;
use App\Notifications\Channels\RoberSmsChannel;

class FollowUpReminder extends Notification
{
    use Queueable;

    protected $record;
    protected $type;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $record   Carpet or Laundry instance
     * @param  string                              $type     'carpet' or 'laundry'
     */
    public function __construct($record, string $type)
    {
        $this->record = $record;
        $this->type   = $type;
    }

    /**
     * Channels: mail, database, and our custom SMS channel.
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
            'database',
            RoberSmsChannel::class,
        ];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $label = ucfirst($this->type);
        // Use the correct route names:
        $url   = route("edit.{$this->type}", $this->record->id);

        return (new MailMessage)
            ->subject("Follow-Up Reminder: {$label} #{$this->record->uniqueid}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your {$label} order **{$this->record->uniqueid}**, received on **{$this->record->date_received}**, remains unpaid and is due for follow-up today.")
            ->action("View {$label}", $url);
    }

    /**
     * Store the notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'record_id'      => $this->record->id,
            'uniqueid'       => $this->record->uniqueid,
            'type'           => $this->type,
            'date_received'  => Carbon::parse($this->record->date_received)->toDateString(),
            'outstanding'    => $this->record->outstanding ?? $this->record->price,
            'follow_up_date' => $this->record->follow_up_due_at,
        ];
    }

    /**
     * Data for our custom RoberSmsChannel.
     *
     * @return array{message:string, unique_identifier:string}
     */
    public function toRoberSms($notifiable): array
    {
        $cfg      = config('followup');
        $nextStage= $this->record->follow_up_stage + 1;
        $template = $cfg['message'];

        // Build replacements, including the correct link
        $replacements = [
            ':stage'          => $nextStage,
            ':type'           => ucfirst($this->type),
            ':uniqueid'       => $this->record->uniqueid,
            ':date_received'  => Carbon::parse($this->record->date_received)->toDateString(),
            ':outstanding'    => number_format($this->record->outstanding ?? $this->record->price, 2),
            ':client_phone'   => $this->record->phone,
            ':link'           => route("edit.{$this->type}", $this->record->id),
        ];

        $message = strtr($template, $replacements);

        return [
            'message'           => $message,
            'unique_identifier' => $this->record->uniqueid,
        ];
    }
}
