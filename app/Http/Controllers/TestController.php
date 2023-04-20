<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\Printer;

class TestController extends Controller
{
    public function print()
    {
        try {
            $profile = CapabilityProfile::load('simple');
            $connector = new WindowsPrintConnector("TMU220");
            $printer = new Printer($connector, $profile);

            $printer->text('Hello World');
            $printer->feed(4);

            $printer->cut();
            $printer->close();

            return response()->json([
                'status'    => true,
                'message'   => 'Printed successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
