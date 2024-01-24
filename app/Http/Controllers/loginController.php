<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use DB;

class loginController extends Controller
 {
    public function signup( Request $request ) {

        $validate = Validator::make( $request->all(), [
            'email'         => 'required',
            'user_name'     => 'required',
            'password'      => 'required|min:6',
            'phone'         => 'required',
           
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        $emailExists = User::where( 'email', $request->email )->first();
        if ( $emailExists ) {
            return response()->json( [ 'error' => 'Email already exists' ], 400 );
        }
        $data = [
            'name'          => $request->user_name,
            'email'         => $request->email,
            'password'      => Hash::make( $request->password ),
            'phone'         => $request->phone,
            'created_at'    => date( 'Y-m-d h:i:s' ),
        ];
        $saveData = User::create( $data );
        if ( $saveData ) {
            return response()->json( [ 'success'=> 'Signup successfully' ], 200 );
        } else {
            return response()->json( [ 'error' => 'Oops! something went wrong' ], 400 );
        }
    }

    public function login( Request $request ) {
        $credentials = $request->only( 'email', 'password' );
        $validate = Validator::make( $credentials, [
            'email'     => 'required',
            'password'  => 'required'
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        $email = User::where( 'email', $request->email )->first();
        if ( $email ) {
            $checkPass = Hash::check( $request->password, $email->password );
            if ( $checkPass ) {
                try {
                    $token = JWTAuth::attempt( $credentials );
                    if ( !$token ) {
                        return response()->json( [ 'error' => 'Invalid Login credentials' ], 400 );
                    } else {
                        return response()->json( [ 'token' => $token ], 200 );
                    }

                } catch( JWTException $e ) {
                    return response()->json( [ 'error'=>$e->getMessage() ], 400 );
                }
            } else {
                return response()->json( [ 'error' => 'wrong password' ],  400 );
            }
        } else {
            return response()-> json( [ 'error' => 'email does not exists' ], 400 );
        }
        return response()->json( [ 'token' => $token ], 200 );
    }

    public function logout( Request $request ) {
        $validate = Validator::make( $request->only( 'token' ), [
            'token' => 'required'
        ] );
        if ( $validate->fails() ) {
            return response()-> json( $validate->errors(), 400 );
        }
        try {
            JWTAuth::invalidate( $request-> token );
            return response() -> json( [ 'success' => 'logged out successfully' ], 200 );
        } catch( JWTException $ex ) {
            return response()-> json( $ex->getMessage(), 400 );
        }
    }
    
    public function updateprofile( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'user_favouritefood'        => 'required',
            'user_favouritdrink'        => 'required',
            // 'user_fbsocialmedia'        => 'required',
            // 'user_instasocialmedia'     => 'required',
            'id'                        => 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        $data = [
            'user_favouritefood'        => $request->user_favouritefood,
            'user_favouritdrink'        => $request->user_favouritdrink,
            'user_fbsocialmedia'        => $request->user_fbsocialmedia,
            // 'user_instasocialmedia'     => $request->user_instasocialmedia,
        ];
        $update  = DB::table('user')
		->where('id','=',$request->id)
		->update($data);
       return response()->json( [ 'success'=> 'Profile updated successfully' ], 200 );
    }

    public function getUser( Request $request ) {
        $user = JWTAuth::authenticate( $request->token );
        return response()-> json(  $user  );
    }
    public function cnicImages(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id' => 'required',
            'front_cnic' => 'required',
            'back_cnic' => 'required',

        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $userid = $request->user_id; 
        if($request->hasFile('front_cnic')){
            $frontimage = time()  . rand() . '.' . $request->front_cnic->extension();
            $request->front_cnic->move(public_path('userid/'.$userid), $frontimage);
        }
        if($request->hasFile('back_cnic')){
            $backimage = time()  . rand() . '.' . $request->back_cnic->extension();
            $request->back_cnic->move(public_path('userid/'.$userid), $backimage);
        }

        $images = [
            'CNICBack' => $frontimage,
            'CNICFront' => $backimage,
            'is_verified'=> 2
            
        ];

        $userupdate = DB::table('user')
            ->where('id', '=', $userid)
            ->update($images);

            return response()->json(['success' => 'images uploaded successfully'], 200);

    }
    public function deleteUser(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        $userId = $request->user_id;
        $user = DB::table('user')->where('id', $userId)->first();


        // Check if the user exists
        if ($user) {
            // Update the status column to 2 using the Query Builder
            DB::table('user')->where('id', $userId)->update(['is_verified' => 2]);
            return response()->json(['message' => 'User status updated successfully'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

}