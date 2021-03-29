<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batik extends Model
{
    use HasFactory;

    protected $fillable = [
        'qr_code',
        'name',
        'description'
    ];

}
