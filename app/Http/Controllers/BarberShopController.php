<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BarberShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class BarberShopController extends Controller
{
  

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => [
                'prohibited_unless:city_id,null',
                'numeric',
                'exists:provinces,id',
            ],
            'city_id' => [
                'prohibited_unless:province_id,null',
                'numeric',
                'exists:cities,id',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $rawQuery = BarberShop::withCount(['comments as comments_avg_score' => function ($query) {
            $query->select(DB::raw('avg(score)'));
        }]);

        if ($request->province_id) {
            $rawQuery->where('province_id', $request->province_id);
        } else if ($request->city_id) {
            $rawQuery->where('city_id', $request->city_id);
        }

        $barberShop = $rawQuery->orderByDesc('comments_avg_score')->get();

        return response()->json(['barbershops' => $barberShop]);
    }

    public function barbers(BarberShop $barberShop)
    {
        return response()->json(['barbers' => $barberShop->barbers()->get()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:16', 'min:3'],
            'phone_number' => ['required', 'min:7', 'max:11', 'unique:users,phone_number'], 
            'location' => ['required', 'string'],
            'address' => ['required', 'string', 'min:4', 'max:64'],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'start_time' => ['required', 'string'], 
            'end_time' => ['required', 'string'], 
            'province_id' => ['required', 'numeric', 'exists:provinces,id'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $inputs = $request->all();
        $inputs['owner_id'] = auth()->id();
        $newBarberShop = BarberShop::create($inputs);

      

        return response()->json(['barber-shop' => $newBarberShop], Response::HTTP_CREATED);
    }

    public function show(BarberShop $barberShop)
    {
        return response()->json(['barbershop' => $barberShop]);//route model binding
    }

    public function join(Request $request)
    {
        $authenticatedUser = auth()->user();

        $validator = Validator::make($request->all(), [
            'barber_shop_id' => ['required', 'numeric', 'exists:barber_shops,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        if ($authenticatedUser->role != 'barber') {
            return response()->json(['error' => 'شما آرایشگر نیستید.'], Response::HTTP_BAD_REQUEST);
        }

        if ($authenticatedUser->gender != BarberShop::find($request->barber_shop_id)->gender) {
            return response()->json(['error' => 'امکان پیوستن به آرایشگاه مورد نظر امکان پذیر نیست.'], Response::HTTP_BAD_REQUEST);
        }

        if ($authenticatedUser->barberShop) {
            return response()->json(['error' => 'در حال حاضر شما عضو یک آرایشگاه هستید.'], Response::HTTP_BAD_REQUEST);
        }

        $authenticatedUser->barber_shop_id = $request->barber_shop_id;
        $authenticatedUser->save();

        return response()->json(['barber' => auth()->user()], Response::HTTP_OK);
    }

    public function left(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barber_shop_id' => ['required', 'numeric', 'exists:barber_shops,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $barber = auth()->user();
        if ($barber->role != 'barber') {
            return response()->json(['error' => 'شما آرایشگر نیستید.'], Response::HTTP_FORBIDDEN);
        }

        $undoneAppointment = Appointment::where([
            ['barber_id', $barber->id],
            ['status', '!=', 'done'],
        ])->get()->count();

        if ($undoneAppointment) {
            return response()->json(['error' => 'شما نوبت رزرو شده دارید.'], Response::HTTP_FORBIDDEN);
        }
        
        if ($barber->barber_shop_id != $request->barber_shop_id) {
            return response()->json(['error' => 'شما عضو آرایشگاهی که میخواهید از آن خارج شوید نیستید.'], Response::HTTP_FORBIDDEN);
        }
        $barber->barber_shop_id = null;
        $barber->save();

        return response()->json();
    }
}
