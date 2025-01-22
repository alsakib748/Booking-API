<?php

namespace App\Models;

use App\Models\Listing;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasApiTokens;

    protected $guarded = [];

    public function listing(){
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
