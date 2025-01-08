<?php

namespace App\Jobs;

use App\Events\RegisterProgress;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Throwable;

class HandleStockRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $id)
    {
        $this->data = $data;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        broadcast(new RegisterProgress($this->id, 0, null, false));

        $data = [];

        foreach ($this->data as $row) {
            array_push($data, [
                "label" => $row[0],
                "quantity" => $row[1]
            ]);
        }

        $id = $this->id;

        Bus::batch([
            Arr::map($data, function ($row, $index) use ($id, $data) {
                $total = count($data);
                return new InsertStock($row, $index + 1, $id, $total);
            })
        ])->then(function (Batch $batch) use ($id) {
            broadcast(new RegisterProgress($id, $batch->progress(), null, true));
        })->catch(function (Batch $batch, Throwable $e) use ($id) {
            broadcast(new RegisterProgress($id, $batch->progress(), $e->getMessage(), true));
        })->dispatch();
    }
}
