<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicesApi extends Controller
{
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
}
