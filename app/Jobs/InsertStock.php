<?php

namespace App\Jobs;

use App\Events\RegisterProgress;
use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class InsertStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $data;
    protected $position;
    protected $id;
    protected $total;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $position, $id, $total)
    {
        $this->data = $data;
        $this->position = $position;
        $this->id = $id;
        $this->total = $total;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;

        $validator = Validator::make($data, [
            'label' => 'required|exists:medicines,label',
            'quantity' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $medicineName = $data["label"];
            $errorDetails = [
                "medicine" => "#{$this->position}: " . $medicineName,
                "errors" => $validator->errors()->all()
            ];
            throw new \Exception(json_encode($errorDetails));
        }

        $stock = new Stock();
        $stock->medicine_id = Medicine::select("id")->where("label", $data["label"])->first()->id;
        $stock->base_quantity = $data["quantity"];
        $stock->created_by = auth()->id();
        $stock->updated_by = auth()->id();
        $stock->save();

        broadcast(new RegisterProgress($this->id, round(($this->position / $this->total) * 100), null, false));
    }
}
