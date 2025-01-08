<?php

namespace App\Http\Controllers\Api;

use App\Exports\StockTemplate;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PrivilegeController;
use App\Http\Requests\Api\StockHistoryRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Api\StockListRequest;
use App\Http\Requests\Api\StockSaveRequest;
use App\Http\Requests\Api\StockUpdateRequest;
use App\Imports\RegisterStock;
use App\Jobs\HandleStockRegistration;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    private $privilegeController;

    public function __construct()
    {
        $this->privilegeController = new PrivilegeController();
    }

    public function list(StockListRequest $request)
    {
        try {
            // Parse the pagination and filtering parameters from the request.
            $dataPerPage = $request->input("limit");
            $page = $request->input("page") + 1;
            $filterVal = $request->has("filter_val") ? $request->input("filter_val") : null;
            $filterCol = $request->has("filter_col") ? $request->input("filter_col") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;

            // Query the database for Stock objects matching the provided filters.
            $model = Stock::with("histories")
                ->with(['medicine' => function ($query) {
                    $query->select('id', 'label');
                }])
                ->whereHas("medicine", function ($query) use ($filterVal, $filterCol) {
                    if ($filterVal && $filterCol) {
                        $query->where("label", "ILIKE", "%{$filterVal}%");
                    }
                });

            $count = $model->count();
            $model = $model->limit($dataPerPage)
                ->offset($offset)
                ->orderBy("created_at", "desc");
            $data = $model->get();

            // Get stock out
            foreach ($data as $row) {
                $row->quantity_out = $this->checkHistories($row->id);
            }

            // Return a JSON response with the list of Stock objects and pagination data.
            return response()->json([
                'status'        => true,
                'data'          => $data,
                'count'         => $count,
                'pagination'    => [
                    'offset'    => $offset,
                    'rowCount'  => $dataPerPage,
                    'page'      => $page,
                    'pageCount' => ceil($count / $dataPerPage)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function save(StockUpdateRequest $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            // Business logic to create a Stock object based on the request payload.
            if (!in_array("STOCK_SERVICE_CREATE", $privileges)) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'You did not have permission to do this action.'
                ], 403);
            }

            $stock = Stock::find($request->input("id"));

            if ($request->input("base_quantity") <= 0) {
                throw new \Exception("Base quantity must be greater than 0.");
            }

            $stockOut = $this->checkHistories($request->input("id"));

            if ($stockOut > 0 && $stock->medicine_id !== $request->input("medicine_id")) {
                throw new \Exception("This item cannot be modified since some of the stock already out.");
            }

            if ($stockOut > $request->input("base_quantity")) {
                throw new \Exception("Stock out is bigger than requested quantity.");
            }

            $stock->medicine_id = $request->input("medicine_id");
            $stock->base_quantity = $request->input("base_quantity");

            $stock->save();

            // Return a JSON response with the saved Stock object.
            return response()->json([
                'status'    => true,
                'data'      => $stock,
                'message'   => 'Stock saved.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function makeHistories(StockHistoryRequest $request)
    {
        try {
            $history = new StockHistory();
            $history->stock_id = $request->input("stock_id");
            $history->type = $request->input("type");
            $history->quantity = $request->input("quantity");
            $history->description = $request->filled("description") ? $request->input("description") : null;
            $history->transaction_id = $request->filled("transaction_id") ? $request->input("transaction_id") : null;

            $history->save();

            return response()->json([
                'status'    => true,
                'data'      => $history,
                'message'   => 'Stock saved.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function getRegistrationTemplate()
    {
        try {
            return Excel::download(
                new StockTemplate(),
                "stock_registrations.xlsx",
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function registerStock(StockSaveRequest $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (!in_array("STOCK_SERVICE_CREATE", $privileges)) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'You did not have permission to do this action.'
                ], 403);
            }

            $import = (new RegisterStock())->toArray($request->file("file"));
            $data = $import[0];

            HandleStockRegistration::dispatch($data, auth()->id());

            return response()->json([
                'status' => true,
                'message' => 'Stock registration job dispatched.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function getMedicineList($query)
    {
        try {
            $data = Medicine::select('id', 'label')
                ->where('label', 'ILIKE', "%$query%")->get();

            return response()->json([
                'status'    => true,
                'data'      => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function checkHistories($id)
    {
        try {
            $histories = StockHistory::where("stock_id", $id)->get();
            $stockOut = 0;

            foreach ($histories as $row) {
                if ($row->type === "OUT") {
                    $stockOut += $row->quantity;
                }
            }

            return $stockOut;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentStock(Request $request)
    {
        try {
            $prescription = json_decode($request->input("prescription"));
            $response = [];

            foreach ($prescription as $rx) {
                $response[$rx->sku] = null;
                $med = Medicine::with("stocks")->withTrashed()->where("sku", $rx->sku)->first();
                if ($med) {
                    if (count($med->stocks) > 0) {
                        $response[$rx->sku] = 0;
                        foreach ($med->stocks as $stock) {
                            $stockOut = $this->checkHistories($stock->id);
                            $response[$rx->sku] = $response[$rx->sku] + ($stock->base_quantity - $stockOut);
                        }
                    }
                }
            }

            return response()->json([
                'status'    => true,
                'data'      => $response
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
