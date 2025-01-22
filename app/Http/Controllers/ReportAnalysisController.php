<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Models\Listing;
use Illuminate\Http\Request;

class ReportAnalysisController extends Controller
{

    public function totalUsers(){

        $totalUsers = User::count();

        if($totalUsers == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'No Users Found'
            ]);
        }

        return response()->json([
            'totalUsers'=>$totalUsers,
            'status' => 'success',
            'message' => 'Total Users has '.$totalUsers
        ]);

    }

    public function totalListing(){

        $totalListing = Listing::count();

        if($totalListing == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'No Listing Found'
            ]);
        }

        return response()->json([
            'totalListing'=>$totalListing,
            'status' => 'success',
            'message' => 'Total Listing has '.$totalListing
        ]);

    }

    public function totalBookings(){

        $totalBooking = Booking::count();

        if($totalBooking == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'No Booking Found'
            ]);
        }

        return response()->json([
            'totalBooking'=>$totalBooking,
            'status' => 'success',
            'message' => 'Total Booking has '.$totalBooking
        ]);

    }

    public function totalRevenue(){

        $totalRevenue = Booking::sum('total_price');

        if($totalRevenue == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'No Revenue Found'
            ]);
        }

        return response()->json([
            'totalRevenue'=>$totalRevenue,
            'status' => 'success',
            'message' => 'Total Revenue has '.$totalRevenue
        ]);

    }

    public function totalMonthlyRevenue(){

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();

        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totalMonthlyRevenue = Booking::whereBetween('created_at',[$startOfMonth,$endOfMonth])->sum('total_price');

        if($totalMonthlyRevenue == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'No Revenue Found'
            ]);
        }

        return response()->json([
            'totalMonthlyRevenue'=>$totalMonthlyRevenue,
            'status' => 'success',
            'message' => 'Total Monthly Revenue has '.$totalMonthlyRevenue
        ]);

    }

}
