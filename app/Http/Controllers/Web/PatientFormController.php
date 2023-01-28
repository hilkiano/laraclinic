<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Models\PatientPotraits;
use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class PatientFormController extends Controller
{
    private $userData;
    private $menuController;
    private $privilegeController;

    public function __construct()
    {
        $this->userData = auth()->user();
        $this->menuController = new MenuController();
        $this->privilegeController = new PrivilegeController();
    }

    /**
     * Form with empty value for create new patient
     *
     * @param $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $viewData = $this->getAddData('add');
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $viewData
            ];

            return view('/patient/form', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error.'
            ], 500);
        }
    }

    /**
     * Get data for blade view
     *
     * @param  string $type
     * @return array
     */
    private function getAddData(string $type = 'add', int $id = 0)
    {
        $data = [
            'title' => $type === "add" ? 'Register New Patient' : 'Update Patient'
        ];

        if ($type === "update" && $id !== 0) {
            $model = Patients::find($id);
            $data["patient"] = $model;
        }

        return $data;
    }

    /**
     * Patient form save action
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        try {
            if ($request->input("id")) {
                $validator = Validator::make($request->all(), [
                    'name'          => 'required',
                    'birth_date'    => 'required',
                    'weight'        => 'nullable|numeric',
                    'height'        => 'nullable|numeric',
                    'phone_number'  => 'nullable|digits_between:8,15'
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                $modelPatient = Patients::find($request->input('id'));
                if ($modelPatient) {
                    $modelPatient->name = $request->name;
                    $modelPatient->birth_date = Carbon::parse($request->birth_date);
                    $modelPatient->weight = $request->weight;
                    $modelPatient->height = $request->height;
                    $modelPatient->address = $request->address;
                    $modelPatient->phone_number = $request->phone_number;
                    $modelPatient->additional_note = $request->additional_note;

                    $modelPatient->save();

                    return response()->json([
                        'status'    => true,
                        'message'   => 'Patient info <strong>' . $modelPatient->name . '</strong> has been updated.',
                        'data'      => $modelPatient
                    ], 200);
                }
            } else {
                $validator = Validator::make($request->all(), [
                    'name'          => 'required|unique:patients',
                    'birth_date'    => 'required',
                    'weight'        => 'nullable|numeric',
                    'height'        => 'nullable|numeric',
                    'phone_number'  => 'nullable|digits_between:8,15'
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                $newPatient = Patients::create([
                    'name'          => $request->name,
                    'birth_date'    => Carbon::parse($request->birth_date),
                    'weight'        => $request->weight,
                    'height'        => $request->heigth,
                    'address'       => $request->address,
                    'phone_number'  => $request->phone_number,
                    'additional_note' => $request->additional_note
                ]);
                if ($newPatient) {
                    return response()->json([
                        'status'    => true,
                        'data'      => $newPatient,
                        'message'   => 'New patient: <strong>' . $newPatient->name . '</strong>, was created.'
                    ], 200);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error.'
            ], 500);
        }
    }

    /**
     * Form with patient's value for update info
     *
     * @param  integer $id
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        try {
            $viewData = $this->getAddData('update', $id);
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "data"  => $viewData
            ];

            return view('/patient/form', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error.'
            ], 500);
        }
    }

    /**
     * Upload potrait image of patient
     *
     * @param  mixed $request
     * @return void
     */
    public function addPotrait(Request $request)
    {
        try {
            $patientData = Patients::find($request->get('id'));
            $now = Carbon::now()->setTimezone('Asia/Jakarta')->isoFormat('YYYY-MM-DD_hh-mm-ss');
            $image = Image::make($request->get('img'));
            $dir = public_path("/patients/$patientData->id");
            $imageName = "$now.jpg";
            $path = $dir . "/" . $imageName;
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 493, true);
            }
            $image->save($path);

            $newPotrait = PatientPotraits::create([
                'patient_id'    => $patientData->id,
                'url'           => "/patients/$patientData->id/$now.jpg"
            ]);

            return response()->json([
                'status'    => true,
                'message'   => 'Image saved.',
                'data'      => $newPotrait
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => 'Unexpected error.'
            ], 500);
        }
    }
}
