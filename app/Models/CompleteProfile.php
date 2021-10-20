<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class CompleteProfile extends Model
{
    use HasFactory,HasApiTokens;
    
    protected $fillable = [
        'name',
        'user_bio',
        'user_image_uploade' => 'array',
        'job_title',
        'univercity_name',
        'gender',
        'user_id',
        'don’t_show_my_age',
        'distance_invisible',
    ];
    
}
