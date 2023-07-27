<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    public function index(string $gender)
    {
        $services = null;
        if ($gender == 'all') {
            $services = Service::all();
        } else {
            $services = Service::where('gender', $gender)->get();
        }

        return response()->json(['services' => $services]);
    }

    public function barberServices(User $barber)
    {
        return response()->json(['services' => $barber->services]);
    }

    public function addToBarber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price' => ['required', 'numeric', 'gte:50000', 'lte:50000000'],
            'duration' => ['required', 'numeric', 'gte:10', 'lte:300'], // minute
            'image' => ['required', 'mimes:jpg,bmp,png,jpeg,gif,svg'],
            'service_id' => ['required', 'numeric', 'exists:services,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        if (auth()->user()->role != 'barber') {
            return response()->json(['error' => 'انتخاب سرویس فقط برای آرایشگرها فعال است.'], Response::HTTP_FORBIDDEN);
        }

        $isAddedToBarber = auth()->user()->services()->where('service_id', $request->service_id)->get()->count();

        if ($isAddedToBarber != 0) {
            return response()->json(['error' => 'سرویس مورد نظر از قبل ایجاد شده است.'], Response::HTTP_FORBIDDEN);
        }

        $service = Service::find($request->service_id);

        if (
            $service->gender != 'no-gender' &&
            $service->gender != auth()->user()->gender
        ) {
            return response()->json(['error' => 'سرویس مورد نظر برای شما در دسترس نخواهد بود.'], Response::HTTP_FORBIDDEN);
        }

        // save image to public/images/service folder
        $imagePath = Hash::make(time()) . '.' . request()->image->getClientOriginalExtension();
        $request->image->move(public_path('images/services'), $imagePath);

        $pivotData = [
            'duration' => $request->duration,
            'price' => $request->price,
            'image_path' => "images/services/$imagePath",
        ];
        auth()->user()->services()->attach($request->service_id, $pivotData);

        return response()->json(['message' => 'عملیات با موفقیت انجام شد.']);
    }
}
