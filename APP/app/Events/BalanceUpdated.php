<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $accountId;
    public float $oldBalance;
    public float $newBalance;

    /**
     * Create a new event instance.
     */
    public function __construct(int $accountId, float $oldBalance, float $newBalance)
    {
        $this->accountId = $accountId;
        $this->oldBalance = $oldBalance;
        $this->newBalance = $newBalance;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
