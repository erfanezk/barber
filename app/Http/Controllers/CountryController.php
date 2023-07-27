<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;

class CountryController extends Controller
{
    public function provinces()
    {
        return response()->json(['provinces' => Province::all()]);
    }

    public function provinceCities(Province $province)
    {
        return response()->json(['cities' => $province->cities]);
    }

    // public function barberShopOfProvince(Province $province)
    // {
    //     return response()->json(['barber-shops' => $province->barberShops]);
    // }

    // public function barberShopOfCity(City $city)
    // {
    //     return response()->json(['barber-shops' => $city->barberShops]);
    // }
}
