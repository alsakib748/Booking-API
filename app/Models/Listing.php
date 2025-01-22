<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Listing extends Model
{

    use HasApiTokens;

    protected $guarded = [];

}
