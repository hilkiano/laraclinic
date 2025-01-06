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
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Request;
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
                $histories = StockHistory::where("stock_id", $row->id)->get();
                if (count($histories) > 0) {
                    // Count stock out
                } else {
                    $row->quantity_out = 0;
                }
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

            foreach ($data as $row) {
                $stock = new Stock();
                $stock->medicine_id = Medicine::select("id")->where("label", $row[0])->first()->id;
                $stock->base_quantity = $row[1];
                $stock->created_by = auth()->id();
                $stock->updated_by = auth()->id();
                $stock->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Stock registered successfully.'
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
