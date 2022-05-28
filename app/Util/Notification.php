<?php

namespace App\Util;

class Notification
{
    private array $messages;

    public function add(string $message): self
    {
        $this->messages[] = $message;
        session()->flash('notification', $this->messages);
        return $this;
    }

    public static function flash(string $message): void
    {
        session()->flash('notification', $message);
    }
}
