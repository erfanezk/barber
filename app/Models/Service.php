<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'gender',
    ];

    public function appointments()
    {
        return $this->hasMany(Service::class);
    }

    public function barbers()
    {
        $pivot = ['duration', 'price', 'image_path'];

        return $this->belongsToMany(User::class,'service_user', 'service_id', 'barber_id')->withPivot($pivot);
    }
}
