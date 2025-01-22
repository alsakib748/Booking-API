<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;

class UserController extends Controller
{


    public function register(RegisterRequest $request){

        // $data = $request->all();

        $user = new User();

        if($request->hasFile('avatar')){
            $file = $request->file('avatar');
            $filename = 'uploads/'.time().'_'.date('D').'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads'),$filename);

            // $data = User::create([
            //     'name' => $request->name,
            //     'email' => $request->email,
            //     'phone' => $request->phone,
            //     'password' => Hash::make($request->password),
            //     'role' => $request->role,
            //     'active' => $request->active,
            // ]);
            $user->avatar = $filename;
        }

        // $data = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'phone' => $request->phone,
        //     'password' => Hash::make($request->password),
        //     'role' => $request->role,
        //     'active' => $request->active,
        // ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->active = $request->active;
        $user->save();

        return response()->json([
            'data' => $user,
            'filename' => $filename,
            'message' => 'User registered successfully'
        ],201);

    }

    public function login(LoginRequest $request){

        // $data = $request->all();

        $email = $request->email;
        $password = $request->password;

        $user = User::where('email',$email)->first();

        if(!$user || !Hash::check($password,$user->password)){
            return response()->json([
                'data' => $request->all(),
                'message' => 'Invalid credentials'
            ],401);
        }

        $token = $user->createToken($user->email.'_Token')->plainTextToken;

        return response()->json([
            'data' => $request->all(),
            'token' => $token,
            'message' => 'User logged in successfully'
        ],200);

    }

    public function logout(){

        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'User logged out successfully'
        ],200);

    }

    public function index(){
        $user = auth()->user()->id;

        return response()->json([
            'data' => User::find($user),
            'message' => 'User profile'
        ],200);

    }

    public function edit(){
        $user = User::findOrFail(auth()->user()->id);

        return response()->json([
            'data' => $user,
            'message' => 'User Edit'
        ],200);
    }

    public function update(Request $request){

        $validated = Validator::make($request->all(),[
            'name' => 'required|string|min:3|max:50',
            // 'email' => 'required|email|unique:users,email,id,'.auth()->user()->id,
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(auth()->user()->id)
            ],
            'phone' => [
                'required',
                'numeric',
                Rule::unique('users')->ignore(auth()->user()->id)
            ],
            'password' => 'required|min:5|max:20',
            'role' => 'required|in:admin,user',
            'active' => 'required|in:0,1',
        ]);

        if($validated->fails()){
            return response()->json([
                'errors' => $validated->errors(),
            ],400);
        }

        $user = User::findOrFail(auth()->user()->id);

        if($request->hasFile('avatar')){

            $file = $request->file('avatar');
            $filename = 'uploads/'.time().'_'.date('D').'.'.$file->getClientOriginalExtension();

            $old_file_path = $user->avatar;

            if(file_exists($old_file_path)){
                unlink($old_file_path);
            }

            $file->move(public_path('uploads'),$filename);

            $user->avatar = $filename;

        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->active = $request->active;
        $user->save();

        return response()->json([
            'data' => $user,
            'message' => 'User updated successfully'
        ],200);

    }

    public function passwordReset(){

        $user = User::findOrFail(auth()->user()->id);

        $password = $user->password;

        return response()->json([
            'data' => $password,
            'message' => 'User Password'
        ],200);

    }

    public function passwordUpdate(Request $request){

        $validated = Validator::make($request->all(),[
            'old_password' => 'required|min:5|max:20',
            'new_password' => 'required|min:5|max:20|confirmed',
            'new_password_confirmation' => 'required|min:5|max:20',
        ],
        [
            'new_password.confirmed' => 'Confirm password does not match'
        ]
    );

        if($validated->fails()){
            return response()->json([
                'errors' => $validated->errors(),
            ],400);
        }

        $user = User::findOrFail(auth()->user()->id);

        if(!Hash::check($request->old_password,$user->password)){
            return response()->json([
                'message' => 'Old password does not match'
            ],400);
        }else{
            $user->password = Hash::make($request->new_password);
            $user->save();

            // auth()->user()->tokens()->delete();

            return response()->json([
                'message' => 'Password Update successfully'
            ],200);
        }

    }

}
