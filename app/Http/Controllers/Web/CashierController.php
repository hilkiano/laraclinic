<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class CashierController extends Controller
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
     * Display the cashier view page.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request.
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse The rendered view or a JSON response.
     */
    public function index(Request $request)
    {
        try {
            // Get user, menus, and privileges data.
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "today" => Carbon::now()->setTimezone(env('APP_TIME_ZONE'))->isoFormat('dddd, DD MMMM YYYY')
            ];

            // Render the cashier view page with the retrieved data.
            return view('cashier', $data);
        } catch (\Exception $e) {
            // Log any exceptions thrown during the process.
            Log::error($e->getMessage());

            // Return a JSON response with error details, with message hidden in production mode.
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
