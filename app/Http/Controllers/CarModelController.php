<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CarModelController extends Controller
{
    public function index()
    {
        $data = Cache::remember('car-model', 30, function () {
            return CarModel::all();
        });

        $count = count($data);

        if($count == 0) {
            return response()->json(
                [
                    'httpStatus' => 404, 
                    'message' => 'no_data',
                    'count' => 0,
                    'data' => null
                ]
            );
        } else {
            return response()->json(
                [
                    'httpStatus' => 200, 
                    'message' => 'success',
                    'count' => $count,
                    'data' => $data
                ]
            );
        }
    }

    public function add(Request $request)
    {
        $nama_model = $request->post('nama_model');

        $insert = CarModel::create([
            'nama_model' => $nama_model
        ]);

        if($insert) {
            return response()->json(
                [
                    'httpStatus' => 200, 
                    'message' => 'success',
                    'count' => 1,
                    'data' => $nama_model
                ]
            );
        } else {
            return response()->json(
                [
                    'httpStatus' => 200, 
                    'message' => 'failed',
                    'count' => 0,
                    'data' => null
                ]
            );
        }
    }
}