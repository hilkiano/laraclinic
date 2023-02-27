<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UploadImageController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $files = Storage::disk('local')->files('/photo');
            foreach ($files as $fileName) {
                // upload asset
                if (pathinfo($fileName, PATHINFO_EXTENSION) == 'png') {
                    $file = Storage::disk('local')->get($fileName);
                    Storage::put("assets/" . basename($fileName), $file);
                }
                // upload photos
                if (pathinfo($fileName, PATHINFO_EXTENSION) == 'jpg') {
                    $file = Storage::disk('local')->get($fileName);
                    Storage::put("patients/" . basename($fileName), $file);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env("APP_ENV") === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
