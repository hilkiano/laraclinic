<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MenuController;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private $userData;
    private $menuController;

    public function __construct()
    {
        if (auth()->user()) {
            $this->userData = Users::with('group')->find(auth()->user()->id);
        }
        $this->menuController = new MenuController();
    }

    public function index(Request $request)
    {
        try {
            $this->userData->logged_in_at = Carbon::make($this->userData->logged_in_at)->setTimezone(env('APP_TIME_ZONE'))->isoFormat('DD MMMM YYYY HH:mm:ss');

            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original
            ];

            return view('/admin/dashboard', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
