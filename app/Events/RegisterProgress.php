<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegisterProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $progress;
    public $errorMsg;
    public $isFinished;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id, $progress, $errorMsg, $isFinished)
    {
        $this->id = $id;
        $this->progress = $progress;
        $this->errorMsg = $errorMsg;
        $this->isFinished = $isFinished;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("register_progress_{$this->id}");
    }

    public function broadcastWith()
    {
        return [
            "id" => $this->id,
            "progress" => $this->progress,
            "errorMsg" => $this->errorMsg,
            "isFinished" => $this->isFinished
        ];
    }
}
