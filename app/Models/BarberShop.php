<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'location',
        'phone_number',
        'gender',
        'start_time',
        'end_time',
        'province_id',
        'city_id',
        'owner_id',
    ];

    public function barbers()
    {
        return $this->hasMany(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
