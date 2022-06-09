<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public $successStatus = 200;

    /**
     * User login API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        /* if ($validator->fails()) {
            return response()->json(['Validation Error' => $validator->errors()], 422);
        } */

         $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $success['name'] = $user->name;
            $success['token'] = $user->createToken('accessToken')->accessToken;

            return sendResponse($success, 'You are successfully logged in.');
            // return response()->json(['success' => $success], $this->successStatus);
        } else {
            return sendError('Unauthorised', ['error' => 'Unauthorised'], 401);
            // return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * User registration API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // dd('test');
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);
        /* if ($validator->fails()) {
            return response()->json(['Validation Error' => $validator->errors()], 422);
        } */


        // $name = $request->file('image')->getClientOriginalName(); 
        // $path = $request->file('image')->store('public/images');

        // file upload.
        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('images'), $imageName);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'image' => $imageName
            ]);

            $success['name'] = $user->name;
            $message = 'Yay! A user has been successfully created.';
            $success['token'] = $user->createToken('accessToken')->accessToken;
        } catch (Exception $e) {
            $success['token'] = [];
            $message = 'Oops! Unable to create a new user.';
        }

        return sendResponse($success, $message);
        // return response()->json(['success' => $success, 'message' => $message]);
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        // return $user->id; // for user id
        // return response()->json(['success' => $user], $this->successStatus);
        return sendResponse($user, 'Get successfully details');
    }

}
