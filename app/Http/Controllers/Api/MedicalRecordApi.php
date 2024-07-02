<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateOnlineTrxRequest;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class MedicalRecordApi extends Controller
{
    private $userData;

    public function __construct()
    {
        $this->userData = auth()->user();
    }

    /**
     * Retrieves a Prescription object from the database based on a given ID
     * @param int $id The ID of the Prescription object to retrieve
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the Prescription object's data if the retrieval is successful
     * or a JSON response indicating that an error occurred if the retrieval failed.
     * @throws \Exception If an unexpected error occurs during the retrieval process
     */
    public function getPrescription(int $id)
    {
        try {
            return response()->json([
                'status'    => true,
                'message'   => 'Prescription copied.',
                'data'      => Prescription::select('list')->find($id)->list
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        try {
            // Parse the pagination and filtering parameters from the request.
            $dataPerPage = $request->input("limit");
            $page = $request->input("page") + 1;
            $filterVal = $request->has("filter_val") ? $request->input("filter_val") : null;
            $filterCol = $request->has("filter_col") ? $request->input("filter_col") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;

            $model = Prescription::with(['patient', 'medicalRecord'])
                ->when($filterVal && $filterCol, function ($query) use ($filterVal, $filterCol) {
                    if ($filterCol === "name") {
                        $query->whereHas('patient', function ($subquery) use ($filterVal) {
                            $subquery->where('name', 'ILIKE', "%$filterVal%")
                                ->whereNull('deleted_at');
                        });
                    }
                    if ($filterCol === "id") {
                        $query->whereHas('patient', function ($subquery) use ($filterVal) {
                            $subquery->where('id', $filterVal);
                        });
                    }
                    if ($filterCol === "code") {
                        $query->whereHas('patient', function ($subquery) use ($filterVal) {
                            $subquery->where('code', 'ILIKE', "%$filterVal%");
                        });
                    }
                });
            $count = $model->count();
            $model = $model->orderBy('prescriptions.created_at', 'desc');
            $model = $model->limit($dataPerPage)
                ->offset($offset);
            $data = $model->get();

            // Return a JSON response with the list of Medicine objects and pagination data.
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

    public function updateOnlineTrx(UpdateOnlineTrxRequest $request)
    {
        try {
            $model = Prescription::find($request->id);
            $modelTrx = Transaction::find($request->trx_id);
            $arrSku = explode(",", $request->sku);

            $modifiedList = Arr::where($model->list, function ($value, $key) use ($arrSku) {
                return !in_array($value["sku"], $arrSku);
            });
            $modifiedTrxList = Arr::where($model->list, function ($value, $key) use ($arrSku) {
                return !in_array($value["sku"], $arrSku);
            });

            $model->list = array_values($modifiedList);
            $model->update();

            $modelTrx->prescription = array_values($modifiedTrxList);
            $modelTrx->update();

            return response()->json([
                'status'        => true,
                'message'       => "Medical records has been updated."
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
