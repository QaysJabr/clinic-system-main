<?php

namespace App\Services\Reminders;

use App\Contracts\Reminders\ReminderChannelSender;
use App\Models\AppointmentReminder;
use InvalidArgumentException;

final class ReminderChannelRegistry
{
    /** @var array<string, ReminderChannelSender> */
    private array $senders = [];

    public function register(ReminderChannelSender $sender): void
    {
        $this->senders[$sender->channel()] = $sender;
    }

    public function send(AppointmentReminder $reminder): void
    {
        $sender = $this->senders[$reminder->channel] ?? null;
        if (! $sender) {
            throw new InvalidArgumentException("No reminder sender registered for channel [{$reminder->channel}].");
        }

        $sender->send($reminder);
    }
}
