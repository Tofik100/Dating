<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceData extends Model
{
    use HasFactory;
    protected $table = 'device_data';
    protected $fillable = [
        'device_key',
        'device_token',
        'latitude',
        'longitude',
        'user_id',
    ];
}
