<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListingRequest;
use App\Models\Listing;
use Illuminate\Http\Request;
use Validator;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listings = Listing::all();
        return response()->json([
            'data' => $listings,
            'status' => 'success',
            'message' => 'Listings fetched successfully',
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = Validator::make(
    $request->all(),
    [
                'title' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:10',
                'location' => 'required|string|min:3|max:30',
                'price' => 'required|numeric|min:0|max:50000',
                'capacity' => 'required|numeric|min:1|max:10',
                'is_available' => 'required|boolean|in:0,1',
            ]
        );

        if($validated->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ],422);
        }

        try{
            $data = Listing::create(
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'location' => $request->location,
                    'price' => $request->price,
                    'capacity' => $request->capacity,
                    'is_available' => $request->is_available,
                    'user_id' => auth()->user()->id,
                ]
            );

            return response()->json([
                'data' => $data,
                'status' => 'success',
                'message' => 'Listing created successfully',
            ],201);
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ],500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $validated = Validator::make(
            $request->all(),
            [
                'listing_id' => 'required|numeric',
                'title' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:10',
                'location' => 'required|string|min:3|max:30',
                'price' => 'required|numeric|min:0|max:50000',
                'capacity' => 'required|numeric|min:1|max:10',
                'is_available' => 'required|boolean|in:0,1',
            ]
        );

        if($validated->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ],422);
        }

        $id = $request->listing_id;

        $listing = Listing::find($id);

        if(!$listing){
            return response()->json([
                'status' => 'error',
                'message' => 'Listing not found',
            ],404);
        }

        $listing->title = $request->title;
        $listing->description = $request->description;
        $listing->location = $request->location;
        $listing->price = $request->price;
        $listing->capacity = $request->capacity;
        $listing->is_available = $request->is_available;
        $listing->user_id = auth()->user()->id;
        $listing->updated_at = now();
        $listing->save();

        return response()->json([
            'data' => $listing,
            'status' => 'success',
            'message' => 'Listing updated successfully',
        ],200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $listing = Listing::find($id);

        if(!$listing){
            return response()->json([
                'status' => 'error',
                'message' => 'Listing not found',
            ],404);
        }

        $listing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Listing deleted successfully',
        ],200);

    }
}
