<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Http\Controllers\PrivilegeController;
use App\Http\Requests\MedicineRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicinesApi extends Controller
{
    private $privilegeController;

    public function __construct()
    {
        $this->privilegeController = new PrivilegeController();
    }

    /**
     * Returns a JSON response containing a list of Medicine objects based on the provided request filters.
     *
     * @param Request $request The HTTP request object containing optional filter parameters.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the list of Medicine objects.
     * @throws \Exception If an unexpected error occurs.
     */
    public function list(Request $request)
    {
        try {
            // Parse the pagination and filtering parameters from the request.
            $dataPerPage = $request->input("limit");
            $page = $request->input("page") + 1;
            $filterVal = $request->has("filter_val") ? $request->input("filter_val") : null;
            $filterCol = $request->has("filter_col") ? $request->input("filter_col") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;

            // Query the database for Medicine objects matching the provided filters.
            $model = Medicine::withTrashed()
                ->with("stocks")
                ->when($filterVal && $filterCol, function ($query) use ($filterVal, $filterCol) {
                    $query->where($filterCol, 'ILIKE', "%$filterVal%");
                });
            $count = $model->count();
            $model = $model->limit($dataPerPage)
                ->offset($offset)
                ->orderBy("label", "asc");
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

    /**
     * Saves a Medicine object based on the provided request payload.
     *
     * @param MedicineRequest $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the saved Medicine object or an error message.
     * @throws \Exception If an unexpected error occurs.
     */
    public function save(MedicineRequest $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            // Business logic to create or update a Medicine object based on the request payload.
            if ($request->filled("id")) {
                if (!in_array("MEDICINE_SERVICE_UPDATE", $privileges)) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }

                $medicine = Medicine::withTrashed()->find($request->input('id'));
            } else {
                if (!in_array("MEDICINE_SERVICE_CREATE", $privileges)) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }

                $medicine = new Medicine();
            }

            $medicine->label = $request->input("label");
            $medicine->package = $request->input("package");
            $medicine->buy_price = $request->input("buy_price");
            $medicine->sell_price = $request->input("sell_price");
            $medicine->description = $request->has("description") ? $request->input("description") : null;

            $medicine->save();

            // Return a JSON response with the saved Medicine object.
            return response()->json([
                'status'    => true,
                'data'      => $medicine,
                'message'   => 'Medicine saved.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change active status of a medicine record from the database.
     *
     * @param \Illuminate\Http\Request $request the HTTP request object.
     * @return \Illuminate\Http\JsonResponse the JSON response containing the status, data, and message of the operation.
     */
    public function deleteRestore(Request $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            if (!in_array("MEDICINE_SERVICE_DELETE", $privileges)) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'You did not have permission to do this action.'
                ], 403);
            }

            $medicine = Medicine::withTrashed()->find($request->input('id'));
            if ($medicine->trashed()) {
                $medicine->restore();
                $message = "Medicine $medicine->label restored.";
            } else {
                $medicine->delete();
                $message = "Medicine $medicine->label deleted.";
            }

            return response()->json([
                'status'    => true,
                'data'      => $medicine,
                'message'   => $message
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
