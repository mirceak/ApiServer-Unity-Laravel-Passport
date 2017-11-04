<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use function MongoDB\BSON\toJSON;

class ApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    function register(Request $request)
    {
        //we check if request is for a normal account or a facebook account
        if ($request->email != null){ // is a normal account register
            /**
             * Get a validator for an incoming registration request.
             *
             * @param  array  $request
             * @return \Illuminate\Contracts\Validation\Validator
             */
            $valid = validator($request->only('email', 'name', 'password'), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($valid->fails()) {
                $jsonError=response()->json($valid->errors()->all(), 400);
                return \Response::json($jsonError);
            }

            $data = request()->only('email','name','password');

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'highscore' => '0',
                'game_money' => '0',
                'money' => '0',
                'level' => '0',
                'xp' => '0',
                'fbId' => null,
                'guestName' => null,
            ]);
        }else if ($request->fbId != null){
            /**
             * Get a validator for an incoming registration request.
             *
             * @param  array  $request
             * @return \Illuminate\Contracts\Validation\Validator
             */
            $valid = validator($request->only('fbId', 'name', 'password'), [
                'name' => 'required|string|max:255',
                'fbId' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($valid->fails()) {
                $jsonError=response()->json($valid->errors()->all(), 400);
                return \Response::json($jsonError);
            }

            $data = request()->only('fbId','name','password');

            $user = User::create([
                'name' => $data['name'],
                'email' => null,
                'password' => bcrypt($data['password']),
                'highscore' => '0',
                'game_money' => '0',
                'money' => '0',
                'level' => '0',
                'xp' => '0',
                'fbId' => $data['fbId'],
                'guestName' => null,
            ]);
        }else{//the user is a guest
            $guestName = "Guest_".(User::all()->last() ? User::all()->last()->id ++ : 1);
            $randomPassword = substr(str_shuffle(str_repeat($x=$guestName, ceil(8/strlen($x)) )),1,8);

            $user = User::create([
                'name' => $guestName,
                'email' => null,
                'password' => $randomPassword,
                'highscore' => '0',
                'game_money' => '0',
                'money' => '0',
                'level' => '0',
                'xp' => '0',
                'fbId' => null,
                'guestName' => $guestName,
            ]);
        }

        $status = ["success"=> "Registered User"];
        return $status;
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $json = [
            'success' => true,
            'code' => 200,
            'message' => 'You are Logged out.',
        ];
        return response()->json($json, '200');
    }
}
