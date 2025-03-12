<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Town;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getCities()
    {
        $cities = City::all();
        return response()->json(
            $cities
        );
    }
    public function getTowns(Request $request)
    {
        $city_id = $request->query('city_id');

        if (!$city_id) {
            return response()->json(['error' => 'city_id is required'], 400);
        }

        return response()->json(Town::where('city_id', $city_id)->get(['id', 'name']));
    }
}
