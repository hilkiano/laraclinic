<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\PrivilegeController;
use App\Models\Patients;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionsApi extends Controller
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
            $patientName = $request->has("patient_name") ? $request->input("patient_name") : null;
            $offset = ($page === 1) ? 0 : ($page * $dataPerPage) - $dataPerPage;
            $startDate = $request->has("startDate") ? $request->input("startDate") : null;
            $endDate = $request->has("endDate") ? $request->input("endDate") : null;

            $model = Transaction::query()
                ->when($patientName, function ($query) use ($patientName) {
                    return $query->whereHas('patient', function ($query) use ($patientName) {
                        $query->where('name', 'ILIKE', '%' . $patientName . '%');
                    });
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->where('created_at', '>=', $startDate)
                        ->where('created_at', '<=', $endDate);
                });
            $count = $model->count();
            $model = $model->limit($dataPerPage)
                ->offset($offset);
            $data = $model->get();

            foreach ($data as $k => $v) {
                $patientName = null;
                if ($v->patient_id) {
                    $patientName = Patients::select('name')->find($v->patient_id)->name;
                }
                $v->patient_name = $patientName;
            }

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
