<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Notifications\BookingCompleted;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{

    public function bookings(){

        $bookings = Booking::get();

        if($bookings->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No bookings found',
            ],404);
        }

        return response()->json([
            'data' => $bookings,
            'status' => 'success',
            'message' => 'Bookings found successfully',
        ],200);

    }

    public function specificBookingsGet($id){

        $bookings = Booking::where('id',$id)->get();

        if($bookings->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No bookings found',
            ],404);
        }

        return response()->json([
            'data' => $bookings,
            'status' => 'success',
            'message' => 'Bookings found successfully',
        ],200);

    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Booking $booking)
    {
        //
    }

    public function edit($id)
    {

        $booking = Booking::find($id);

        if(!$booking){
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found',
            ],404);
        }

        return response()->json([
            'data' => $booking,
            'status' => 'success',
            'message' => 'Booking found successfully',
        ],200);

    }

    public function update(Request $request)
    {

        $validated = Validator::make($request->all(),
        [
            'booking_id' => 'required|exists:bookings,id',
            'listing_id' => 'required|exists:listings,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'total_price' => 'required|numeric',
        ]);

        if($validated->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ],400);
        }

        $booking = Booking::find($request->booking_id);

        if(!$booking){
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found',
            ],404);
        }

        $booking->update([
            'listing_id' => $request->listing_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'total_price' => $request->total_price,
            'updated_at' => now(),
        ]);

        $notification = new Notification();
        $notification->user_id = $booking->user_id;
        $notification->message = 'Booking Updated';
        $notification->title = 'Booking Confirmed';
        $notification->message = 'Your booking has been confirmed';
        $notification->type = 'booking';
        $notification->notifiable_id = $booking->id;
        $notification->notifiable_type = get_class($booking);
        $notification->data = json_encode($booking);
        $notification->read_at = null;
        $notification->is_read = 0;
        $notification->save();

        $user = $booking->user;

        if ($user) {
            $user->notify(new BookingCompleted($booking));
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'data' => $booking,
            'status' => 'success',
            'message' => 'Booking updated successfully',
        ],200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
