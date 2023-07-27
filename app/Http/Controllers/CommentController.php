<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BarberShop;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function index(BarberShop $barberShop)
    {
        $comments = $barberShop->comments()->get();//route model binding

        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => ['required', 'max:64', 'min:3'],
            'score' => ['required', 'numeric', 'gte:1', 'lte:5'],
            'barber_id' => ['sometimes', 'exists:users,id'],
            'barber_shop_id' => ['required', 'exists:barber_shops,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $client = auth()->user();
        $barber = User::find($request->barber_id);
        // $barbershop = BarberShop::find($request->barber_shop_id);
        // $barbers = $barbershop->barbers;
        // $appointments = [];
        // foreach ($barbers as $barber) {
       
            // $appointments = [...$appointments, ...$barberAppointments];
        // }
        $appointments = Appointment::where([
            ['barber_id', $barber->id],
            ['client_id', $client->id],
            ['status', 'done'],
        ])->get();
        if(count($appointments)==0){
            return response()->json(['error' => 'امکان ثبت نظر برای شما وجود ندارد'], Response::HTTP_BAD_REQUEST);
        }

        $newComment = Comment::create([...$request->all(), 'client_id' => auth()->id()]);

        return response()->json(['comment' => $newComment], Response::HTTP_CREATED);
    }
}
