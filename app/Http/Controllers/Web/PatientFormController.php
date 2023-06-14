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
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

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
            $model = Patients::with('patientPotrait')->find($id);
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
     * Add a portrait image to the patient potraits and save it to storage.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating whether the image was saved or not.
     */
    public function addPotrait(Request $request)
    {
        try {
            $patientId = $request->input('id');
            $dataUrl = $request->input('image');
            $image = Image::make($dataUrl);
            $image = $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('jpg', 90);
            $timestamp = Carbon::now()->timestamp;
            $imageName = "photo$timestamp.jpg";

            $upload = Storage::disk(env('FILESYSTEM_DISK', 's3'))->put('patients/' . $imageName, $image);
            if ($upload) {
                $url = Storage::disk(env('FILESYSTEM_DISK', 's3'))->url('patients/' . $imageName);
            }

            // Save to patient potraits
            $potraits = PatientPotraits::where('patient_id', $patientId)->first();
            if ($potraits) {
                // Update existing list of URLs
                $list = $potraits->url;
                array_push($list, $url);

                $potraits->url = $list;
            } else {
                // Create new row
                $potraits = new PatientPotraits();
                $potraits->patient_id = $patientId;
                $potraits->url = [$url];
            }
            $potraits->save();

            return response()->json([
                'status'    => true,
                'message'   => 'Image saved.',
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
     * Retrieve the URLs of the portraits of a specific patient.
     *
     * @param int $patientId The ID of the patient whose portraits to retrieve.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the status of the operation and the retrieved data.
     * The status will be "true" if the operation was successful, and "false" otherwise.
     * The data will contain an array of URLs of the patient's portraits if they exist, and an empty array otherwise.
     * If an error occurs, the status will be "false", and the message will be returned either in the message field (in non-production environments),
     * or in the log (in production environments).
     */
    public function getPotraits(int $patientId)
    {
        try {
            $urls = [];
            $patientPotraits = PatientPotraits::select('url')->where('patient_id', $patientId)->first();
            if ($patientPotraits) {
                $urls = $patientPotraits->url;
            }

            return response()->json([
                'status'    => true,
                'data'      => $urls
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function show(int $patientId)
    {
        try {
            $urls = [];
            $patient = Patients::with('patientPotrait')->find($patientId);

            return response()->json([
                'status'    => true,
                'data'      => $patient
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $patient = Patients::find($request->patient_id);
            $status = "";
            if ($request->method === "remove") {
                $patient->deleted_at = Carbon::now();
                $patient->deleted_by = auth()->id();
                $status = "removed";
            } else {
                $patient->deleted_at = null;
                $patient->deleted_by = null;
                $status = "restored";
            }

            $patient->save();

            return response()->json([
                'status'    => true,
                'message'   => "Patient $status."
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTrace());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
