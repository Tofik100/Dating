<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_gallery extends Model
{
    use HasFactory;

    protected $table = 'user_gallerys';
    
    protected $fillable = [
        'image_gallery',
        'user_id',
     ];
}
