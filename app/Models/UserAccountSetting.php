<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccountSetting extends Model
{
    use HasFactory;

    protected $fillable = [
       'numbers',
       'current_location',
       'latitude',
       'longitude',
       'gender',
       'Job_title',
       'maximum_distance',
       'age_range' => 'array',
       'user_id',
    ];
}
