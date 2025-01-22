<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Validator;

class UserManageController extends Controller
{

    public function index()
    {
        $users = User::where('role','user')->get();

        if($users->isEmpty()){
            return response()->json([
                'status'=>'error',
                'message'=>'No user found'
            ],404);
        }

        return response()->json([
            'data'=>$users,
            'status'=>'success',
            'message'=>'All users show successfully'
        ],200);

    }

    public function activeUsers()
    {
        $users = User::where('role','user')->where('active',1)->get();

        if($users->isEmpty()){
            return response()->json([
                'status'=>'error',
                'message'=>'No active user found'
            ],404);
        }

        return response()->json([
            'data'=>$users,
            'status'=>'success',
            'message'=>'All active users show successfully'
        ],200);

    }

    public function unactiveUsers()
    {
        $users = User::where('role','user')->where('active',0)->get();

        if($users->isEmpty()){
            return response()->json([
                'status'=>'error',
                'message'=>'No unactive user found'
            ],404);
        }

        return response()->json([
            'data'=>$users,
            'status'=>'success',
            'message'=>'All unactive users show successfully'
        ],200);

    }

    public function show($id)
    {
        $user = User::where('id',$id)->where('role','user')->first();

        if(!$user){
            return response()->json([
                'status'=>'error',
                'message'=>'User not found'
            ],404);
        }

        return response()->json([
            'data'=>$user,
            'status'=>'success',
            'message'=>'User show successfully'
        ],200);

    }

    public function edit($id)
    {
        $user = User::where('id',$id)->where('role','user')->first();

        if(!$user){
            return response()->json([
                'status'=>'error',
                'message'=>'User not found'
            ],404);
        }

        return response()->json([
            'data'=>$user,
            'status'=>'success',
            'message'=>'User edit successfully'
        ],200);

    }

    public function update(Request $request)
    {

        $validated = Validator::make($request->all(),[
                'user_id' => 'required|numeric',
                'name' => 'required|string|min:3|max:50',
                'email' => 'required|email|unique:users,email,'.$request->user_id,
                'phone' => 'required|numeric|digits:11|unique:users,phone,'.$request->user_id,
                'password' => 'required|string|min:5|max:20',
                'role' => 'required|string|in:user',
                'active' => 'required|boolean',
                'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if($validated->fails()){
            return response()->json([
                'status'=>'error',
                'message'=>$validated->errors()
            ],400);
        }

        $user = User::where('id',$request-> user_id)->where('role','user')->first();

        if(!$user){
            return response()->json([
                'status'=>'error',
                'message'=>'User not found'
            ],404);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->active = $request->active;

        if($request->hasFile('avatar')){
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            $filename = time().'_'.date('D').'.'.$extension;

            if(file_exists($user->avatar)){
                unlink($user->avatar);
            }

            $file->move(public_path('uploads'),$filename);

            $user->avatar = 'uploads'.'/'.$filename;

        }

        $user->save();

        return response()->json([
            'data'=>$user,
            'status'=>'success',
            'message'=>'User update successfully'
        ],200);

    }

    public function destroy($id)
    {
        $user = User::where('id',$id)->where('role','user')->first();

        if(!$user){
            return response()->json([
                'status'=>'error',
                'message'=>'User not found'
            ],404);
        }

        if(file_exists($user->avatar)){
            unlink($user->avatar);
        }

        $user->delete();

        return response()->json([
            'status'=>'success',
            'message'=>'User delete successfully'
        ],200);

    }



}
