<?php

namespace App\Http\Controllers;

class InitController extends Controller
{
    public function index()
    {
        return response()->json([
            "error" => false,
            "data" => (object) [
                "available_providers" => [
                    "Dialog", "Hutch", "Airtel",
                ],
            ],
        ], 201);
    }
}
