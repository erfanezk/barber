<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BarberShop;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{
    public function clientAppointment(User $user)
    {
        $appointments = Appointment::where('client_id', $user->id)->get();
        return response()->json(['appointments' => $appointments]);
    }

    public function barbershopAppointment(BarberShop $barberShop)
    {
        // TODO: change barber_shops_id to Barber_shop_id
        $appointments = Appointment::where('barber_shops_id', $barberShop->id)->get();
        return response()->json(['appointments' => $appointments]);
    }

    public function appointmentList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:Y-m-d'], //2022-11-05
            'barber_shop_id' => ['required', 'numeric', 'exists:barber_shops,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $barbersFreeTime = null;
        $barberShop = BarberShop::find($request->barber_shop_id);
        $barbers = $barberShop->barbers;

        $carbonStartBarbershop = Carbon::createFromFormat('H:i', $barberShop->start_time); //08:30
        $carbonEndBarbershop = Carbon::createFromFormat('H:i', $barberShop->end_time); //21:51

        foreach ($barbers as $barber) {
            $appointments = Appointment::where([
                ['date', $request->date],
                ['barber_id', $barber->id]
            ])->get()->sortBy('start_time');
            $i = 0;
            /*
            barbers=[ 
                1:[//barber id
                    1:[start,end],
                    2:[start,end],
                    3:[start,end],
                    4:[start,end]
                ],
                 2:[//barber id
                    1:[start,end],
                    2:[start,end],
                    3:[start,end],
                    4:[start,end]
                ]
            ]             
            */
            $barbersFreeTime[$barber->id][$i][0] = $carbonStartBarbershop;
            foreach ($appointments as $appointment) {
                $barbersFreeTime[$barber->id][$i][1] = Carbon::createFromFormat('H:i', $appointment->start_time);//end time
                $barbersFreeTime[$barber->id][++$i][0] = Carbon::createFromFormat('H:i', $appointment->end_time);//start time
            }
            $barbersFreeTime[$barber->id][$i][1] = $carbonEndBarbershop;
        }
        $barbersFreeTime = $this->removeDuplicateAndChangeLocale($barbersFreeTime);//when start time is reserved or end time is reserevd
        $this->addBarberToAppointment($barbersFreeTime);

        return response()->json(['barbers-free-times' => $barbersFreeTime]);
    }

    public function reserveAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'datetime' => ['required', 'date_format:Y-m-d H:i'],
            'service_id' => ['required', 'numeric', 'exists:services,id'],
            'barber_id' => ['required', 'numeric', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $barber = User::find($request->barber_id);
        $barberShop = $barber->barberShop;
        [$date, $startTimeService] = explode(' ', $request->datetime);//startTimeService 08:55

        $service = Service::find($request->service_id)->barbers()->find($request->barber_id);
        $serviceDuration = $service->pivot->duration;

        $nowDatetime = (new Carbon());
        $appointmentDatetime = (new Carbon($request->datetime));
        //appointmentDatetime 23
        //now 24

        if ($nowDatetime->gt($appointmentDatetime)) {
            return response()->json(['error' => 'زمان انتخاب شده نامعتبر است.'], Response::HTTP_FORBIDDEN);
        }

        $endTimeService = substr(Carbon::createFromFormat('H:i', $startTimeService)->addMinutes($serviceDuration), 11, 5);

        if (
            $barberShop->start_time > $startTimeService ||
            $barberShop->end_time < $endTimeService
        ) {
            return response()->json(['error' => 'آرایشگاه در این زمان تعطیل است.'], Response::HTTP_FORBIDDEN);
        }

        $appointments = Appointment::where('date', $date)->where('barber_id', $barber->id)->get()->sortBy('start_time');
        foreach ($appointments as $appointment) {
            if (
                ($appointment->start_time <= $startTimeService && $startTimeService < $appointment->end_time) ||
                ($appointment->start_time < $endTimeService && $endTimeService <= $appointment->end_time)
            ) {
                return response()->json(['error' => 'نوبت مورد نظر شما با دیگر نوبت ها تداخل دارد.'], Response::HTTP_FORBIDDEN);
            }
        }

        $newAppointment = Appointment::create([
            'date' => $date,
            'start_time' => $startTimeService,
            'end_time' => $endTimeService,
            'service_id' => $request->service_id,
            'barber_id' => $barber->id,
            'barber_shops_id' => $barberShop->id,
            'client_id' => auth()->id(),
        ]);

        return response()->json(['appointment' => $newAppointment], Response::HTTP_CREATED);
    }

    private function removeDuplicateAndChangeLocale($arr)
    {
        $result = null;
        $keys = array_keys($arr);
        for ($i = 0; $i < count($arr); $i++) {
            for ($j = 0; $j < count($arr[$keys[$i]]); $j++) {
                if ($arr[$keys[$i]][$j][0] != $arr[$keys[$i]][$j][1]) {
                    $result[$keys[$i]][] = [
                        (new Carbon(Carbon::parse($arr[$keys[$i]][$j][0])->toDateTimeLocalString()))->format('Y-m-d H:i'),
                        (new Carbon(Carbon::parse($arr[$keys[$i]][$j][1])->toDateTimeLocalString()))->format('Y-m-d H:i'),
                    ];
                }
            }
        }

        return $result;
    }

    private function addBarberToAppointment(&$items)
    {
        $keys = array_keys($items);//barber
        for ($i = 0; $i < count($keys); $i++) {
            $items[$i]['barber'] = User::find($keys[$i]);
            $items[$i]['free-times'] = [...$items[$keys[$i]]];
            unset($items[$keys[$i]]);
            
            
        }
    }
}
