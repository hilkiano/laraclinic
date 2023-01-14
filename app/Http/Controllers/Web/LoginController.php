<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    private $userData;
    public function __construct()
    {
        $this->userData = auth()->user();
    }

    public function index(Request $request)
    {
        try {
            if ($this->userData) {
                return redirect()->action('App\Http\Controllers\Web\DashboardController@index');
            }

            return view('/login');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
