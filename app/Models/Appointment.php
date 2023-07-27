<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'service_id',
        'barber_shops_id',
        'client_id',
        'barber_id',
        'status',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
