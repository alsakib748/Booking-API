<?php

namespace App\Http\Controllers;

// use Notification;
use Validator;
use Carbon\Carbon;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\BookingCompleted;

class UserInterfaceController extends Controller
{

    public function listings(){
        $listings = Listing::all();
        return response()->json([
            'data' => $listings,
            'status' => 'success',
            'message' => 'Listings fetched successfully',
        ],200);
    }

    public function singleListing($id){
        $listing = Listing::find($id);

        if(!$listing){
            return response()->json([
                'status' => 'error',
                'message' => 'Listing not found',
            ],404);
        }

        return response()->json([
            'data' => $listing,
            'status' => 'success',
            'message' => 'Listing fetched successfully',
        ],200);
    }

    public function search(Request $request){

        $search = $request->query('query');

        $listings = Listing::where('title','like','%'.$search.'%')
            ->orWhere('location','like','%'.$search.'%')
            ->get();

        if($listings->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No search results found',
            ],404);
        }

        return response()->json([
            'data' => $listings,
            'status' => 'success',
            'message' => 'Search results fetched successfully',
        ],200);


    }

    public function filter(Request $request){

        $filter = '';

        // $location = '';
        // $min_price = '';
        // $max_price = '';
        // $type = '';
        // $capacity = '';

        $query = Listing::query();

        if($request->has('location')){
            $location = $request->query('location');
            $query->where('location',$location);
        }

        if($request->has('min_price')){
            $min_price = $request->query('min_price');
            $query->where('price','>=',$min_price);
        }

        if($request->has('max_price')){
            $max_price = $request->query('max_price');
            $query->where('price','<=',$max_price);
        }

        if($request->has('capacity')){
            $capacity = $request->query('capacity');
            $query->where('capacity',$capacity);
        }

        $listings = $query->get();

        if($listings->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No filter results found',
            ],404);
        }

        return response()->json([
            'data' => $listings,
            'status' => 'success',
            'message' => 'Filter results fetched successfully',
        ],200);

    }

    public function bookings(Request $request){

        $validated = Validator::make($request->all(),
        [
            'listing_id' => 'required|exists:listings,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date',
        ]);

        if($validated->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ],400);
        }

        if($request->check_in < date('Y-m-d')){
            return response()->json([
                'status' => 'error',
                'message' => 'Check-in date cannot be in the past',
            ],400);
        }

        if($request->check_in > $request->check_out){
            return response()->json([
                'status' => 'error',
                'message' => 'Check-in date cannot be greater than Check-out date',
            ],400);
        }

        $booking_is_exists = Booking::where('listing_id',$request->listing_id)
            ->where('check_in',$request->check_in)
            ->where('check_out',$request->check_out)
            ->where('status', '!=','cancelled')
            ->exists();

        $booking_is_exists = Booking::where('listing_id', $request->listing_id)
        ->where('status', '!=', 'cancelled')
        ->where(function ($query) use ($request) {
            $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                ->orWhere(function ($query) use ($request) {
                    $query->where('check_in', '<=', $request->check_in)
                    ->where('check_out', '>=', $request->check_out);
                });
        })
        ->exists();

    // $booking_is_exists = Booking::where('listing_id', $request->listing_id)
    // ->where('status', '!=', 'cancelled')
    // ->where(function ($query) use ($request) {
    //     $query->whereBetween('check_in', [$request->check_in, $request->check_out])
    //         ->orWhereBetween('check_out', [$request->check_in, $request->check_out]);
    // })
    // ->exists();

        if($booking_is_exists){
            return response()->json([
                'status' => 'error',
                'message' => 'Booking already exists for this date range',
            ],400);
        }

        // $booking_is_exists = Booking::where('listing_id',$request->listing_id)
        // ->where('check_out','>=',$request->check_in)
        // ->where('status', '!=','cancelled')
        // ->exists();

        // if($booking_is_exists){
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Booking already exists for this date range(check-out)',
        //     ],400);
        // }

        $listing = Listing::find($request->listing_id);

        // $total_days = (strtotime($request->check_out) - strtotime($request->check_in)) / (60 * 60 * 24);

        $total_days = Carbon::parse($request->check_in)->diffInDays(Carbon::parse($request->check_out),false) + 1;

        $total_price = $total_days * $listing->price;

        DB::beginTransaction();

        try{

            $booking = Booking::create([
                'user_id' => auth()->user()->id,
                'listing_id' => $request->listing_id,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'total_price' => $total_price,
            ]);

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $request->payment_method,
                'amount' => $total_price,
                'transaction_id' => 'TXN'.rand(100000,999999),
                'is_verified' => 0,
            ]);

            $notification = Notification::create([
                'user_id' => auth()->user()->id,
                'title' => 'Booking On The Way',
                'message' => 'Your booking has been confirmed',
                'type' => 'booking',
                'notifiable_id' => $booking->id,
                'notifiable_type' => get_class($booking),
                'data' => json_encode($booking),
                'read_at' => null,
                'is_read' => 0,
            ]);

            DB::commit();

            $user = auth()->user();

            $user->notify(new BookingCompleted($booking));

            return response()->json([
                'booking' => $booking,
                'payment' => $payment,
                'notification' => $notification,
                'status' => 'success',
                'message' => 'Booking created successfully',
            ],201);

        }
        catch(\Exception $e){

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ],400);

        }


    }

    public function specificBookingsGet(Request $request, $id){

        if($request->has('read')){
            $notification = Notification::where('notifiable_id',$id)
                ->where('notifiable_type','App\Models\Booking')
                ->where('user_id',auth()->user()->id)
                ->update(['is_read' => 1,'read_at' => now()]);
        }

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
            'message' => 'Bookings fetched successfully',
        ],200);
    }

    public function notifications(){

        $notifications = Notification::where('user_id',auth()->user()->id)
            ->where('type','booking')
            ->get();

        if($notifications->isEmpty()){

            return response()->json([
                'status' => 'error',
                'message' => 'No notifications found'
            ],404);

        }

        return response()->json([

            'data' => $notifications,
            'status' => 'success',
            'message' => 'Notifications fetched successfully'

        ],200);

    }

    public function notificationsRead(){

        $notification = Notification::where('user_id',auth()->user()->id)
            ->where('is_read',1)
            ->get();

        if($notification->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No read notifications found',
            ],404);
        }

        return response()->json([
            'data' => $notification,
            'status' => 'success',
            'message' => 'Read notifications show successfully',
        ],200);

    }

    public function notificationsUnread(){

        $notification = Notification::where('user_id',auth()->user()->id)
            ->where('is_read',0)
            ->get();

        if($notification->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No unread notifications found',
            ],404);
        }

        return response()->json([
            'data' => $notification,
            'status' => 'success',
            'message' => 'Unread notifications show successfully',
        ],200);

    }

    public function reviewsAll(){

        $reviews = Review::all();

        if($reviews->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No reviews found',
            ],404);
        }

        return response()->json([
            'data' => $reviews,
            'status' => 'success',
            'message' => 'Reviews show successfully',
        ],200);

    }

    public function specificReviews($id){

        $review = Review::where('id',$id)->get();

        if($review->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No reviews found',
            ],404);
        }

        return response()->json([
            'data' => $review,
            'status' => 'success',
            'message' => 'Review show successfully',
        ],200);

    }

    public function reviews(Request $request){

        $validator = Validator::make($request->all(),
        [
            'listing_id' => 'required|exists:listings,id',
            'review' => 'required|string|min:10|max:255',
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ],400);
        }

        $review = Review::create([
            'user_id' => auth()->user()->id,
            'listing_id' => $request->listing_id,
            'review' => $request->review,
            'rating' => $request->rating,
        ]);

        return response()->json([
            'data' => $review,
            'status' => 'success',
            'message' => 'Review created successfully',
        ],201);

    }

    public function reviewsEdit($id){

        $review = Review::find($id);

        if(!$review){
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found',
            ],404);
        }

        return response()->json([
            'data' => $review,
            'status' => 'success',
            'message' => 'Review data successfully',
        ],200);

    }

    public function reviewsUpdate(Request $request){

        $validator = Validator::make($request->all(),
        [
            'review_id' => 'required|exists:reviews,id',
            'listing_id' => 'required|exists:listings,id',
            'review' => 'required|string|min:10|max:255',
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ],400);
        }

        $id = $request->review_id;

        $review = Review::find($id);

        if(!$review){
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found',
            ],404);
        }

        $review->update([
            'review' => $request->review,
            'rating' => $request->rating,
        ]);

        return response()->json([
            'data' => $review,
            'status' => 'success',
            'message' => 'Review updated successfully',
        ],200);

    }

    public function reviewsDelete($id){

        $review = Review::find($id);

        if(is_null($review)){
            return response()->json([
                'status' => 'error',
                'message' => 'Reviews not found'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reviews delete successfully'
        ],200);

    }


}
