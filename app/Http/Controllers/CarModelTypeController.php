<?php

namespace App\Http\Controllers;

use App\Models\CarModelType;
use Illuminate\Http\Request;

class CarModelTypeController extends Controller
{
    public function index()
    {
        $data = CarModelType::all();
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
        $id_model = $request->post('id_model');
        $nama_model_type = $request->post('nama_model_type');

        $insert = CarModelType::create([
            'id_model' => $id_model,
            'nama_model_type' => $nama_model_type,
        ]);

        if($insert) {
            return response()->json(
                [
                    'httpStatus' => 200, 
                    'message' => 'success',
                    'count' => 1,
                    'data' => $nama_model_type
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