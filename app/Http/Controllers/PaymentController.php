<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Validator;

class PaymentController extends Controller
{

    public function index()
    {
        $payments = Payment::get();

        if($payments->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No payments found',
            ],404);
        }

        return response()->json([
            'data' => $payments,
            'status' => 'success',
            'message' => 'Payments found successfully',
        ],200);

    }

    public function paymentById($id){

        $payment = Payment::where('id',$id)->get();

        if($payment->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No payment found',
            ],404);
        }

        return response()->json([
            'data' => $payment,
            'status' => 'success',
            'message' => 'Payment found successfully',
        ],200);

    }


    public function create()
    {

    }

    public function store(Request $request)
    {
        //
    }

    public function show(Payment $payment)
    {
        //
    }

    public function edit($id)
    {
        $payments = Payment::where('id',$id)->get();

        if($payments->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No payments found',
            ],404);
        }

        return response()->json([
            'data' => $payments,
            'status' => 'success',
            'message' => 'Payments found successfully',
        ],200);

    }

    public function update(Request $request, Payment $payment)
    {

        $validated = Validator::make($request->all(),[
            'payment_id' => 'required|numeric',
            'booking_id' => 'required|numeric',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
            'transaction_id' => 'required|string',
            'is_verified' => 'required|boolean',
        ]);


        if($validated->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ],400);
        }

        $payment = Payment::find($request->payment_id);

        if(!$payment){
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
            ],404);
        }

        $payment->booking_id = $request->booking_id;
        $payment->payment_method = $request->payment_method;
        $payment->amount = $request->amount;
        $payment->transaction_id = $request->transaction_id;
        $payment->is_verified = $request->is_verified;
        $payment->updated_at = now();
        $payment->save();

        return response()->json([
            'data' => $payment,
            'status' => 'success',
            'message' => 'Payment updated successfully',
        ],200);

    }

    public function destroy($id)
    {

        $payment = Payment::find($id);

        if(!$payment){
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
            ],404);
        }

        if($payment->is_verified == true){
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete it. Because Payment is verified',
            ],400);
        }

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully',
        ],200);

    }


}
