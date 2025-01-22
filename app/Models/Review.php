<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    use HasApiTokens;

    protected $guarded = [];

}
