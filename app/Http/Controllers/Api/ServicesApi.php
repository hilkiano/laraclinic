<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Http\Controllers\PrivilegeController;
use App\Http\Requests\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicesApi extends Controller
{
    private $privilegeController;

    public function __construct()
    {
        $this->privilegeController = new PrivilegeController();
    }

    public function list(Request $request)
    {
        try {
            $dataPerPage = $request->input("limit");
            $page = $request->input("page") + 1;
            $filterVal = $request->has("filter_val") ? $request->input("filter_val") : null;
            $filterCol = $request->has("filter_col") ? $request->input("filter_col") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;

            $model = Services::withTrashed()
                ->when($filterVal && $filterCol, function ($query) use ($filterVal, $filterCol) {
                    $query->where($filterCol, 'ILIKE', "%$filterVal%");
                });
            $count = $model->count();
            $model = $model->limit($dataPerPage)
                ->offset($offset);
            $data = $model->get();

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
     * Saves a Services object based on the provided request payload.
     *
     * @param ServiceRequest $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the saved Services object or an error message.
     * @throws \Exception If an unexpected error occurs.
     */
    public function save(ServiceRequest $request)
    {
        try {
            $privileges = $this->privilegeController->__invoke($request)->original["data"];
            // Business logic to create or update a Medicine object based on the request payload.
            if ($request->has("id")) {
                if (!in_array("MEDICINE_SERVICE_UPDATE", $privileges)) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }

                $service = Services::find($request->input('id'));
            } else {
                if (!in_array("MEDICINE_SERVICE_CREATE", $privileges)) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'You did not have permission to do this action.'
                    ], 403);
                }

                $service = new Services();
            }

            $service->label = $request->input("label");
            $service->buy_price = $request->input("buy_price");
            $service->sell_price = $request->input("sell_price");
            $service->description = $request->has("description") ? $request->input("description") : null;

            $service->save();

            // Return a JSON response with the saved Services object.
            return response()->json([
                'status'    => true,
                'data'      => $service,
                'message'   => 'Service saved.'
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
     * Delete a service record from the database.
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

            $service = Services::withTrashed()->find($request->input('id'));
            if ($service->trashed()) {
                $service->restore();
                $message = "Service $service->label restored.";
            } else {
                $service->delete();
                $message = "Service $service->label deleted.";
            }

            return response()->json([
                'status'    => true,
                'data'      => $service,
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
