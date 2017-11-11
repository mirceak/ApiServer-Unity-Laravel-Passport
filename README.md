# ApiServer-Unity-Laravel-Passport
A well detailed tutorial for a server based project made in Unity. This template provides you with every step you need to take to setup
your own php server using laravel and having it communicate with unity.

This tutorial is designed for someone with basic computer knowledge that has never seen the technologies we are going to be using so
bear with me until we get to the interesting stuff. 

## First let's install what we need.
1. Unity 3D
2. Xampp
3. Git
4. Npm
5. Composer

#### 1. Unity 3D
Visit this website https://store.unity.com/ and follow the instructions to install Unity 3D.

#### 2. Xampp
Visit this link https://www.apachefriends.org/download.html and install xampp version 7.1.10 or higher.

#### 3. Git
Visit this website https://git-scm.com/downloads and follow the instructions to install Git.

#### 4. Npm
Visit this website https://nodejs.org/en/ and follow the instructions to install NodeJs. It includes Npm.

#### 5. Composer
Visit this website https://getcomposer.org/download/ and follow the instructions to install Composer.

## Great job! Now we can start having some fun!

## Let's create a database first.
Xampp will host our php and mysql servers. Php handles the requests unity will make to the server and mysql will hold the data we want
to store like user info or whatever. This tutorial is for a php based server so it cannot be used for realtime responses. For that you
need to use a socket server like socket.io which is based on node.js. I will probably make a tutorial on that too if this one gets rep.
Moving on.

* Open xampp control panel.
* Make sure you start the Apache and MySQL modules.
* In your browser navigate to http://localhost/phpmyadmin/
* On the left side click on the "New" button.
* In the "Database name" field, enter "tutorial_database"
* From the collation dropdown select the "utf8-unicode-ci" field and click on create.

That's it for this step. You are now hosting your very own mysql database and php server.

## Let's setup laravel.

* Create a new folder and open it.
* In that folder shift+rightClick -> Git Bash. (shift ensures admin rights)
* Paste in this code ( ```composer global require "laravel/installer"``` )
* Paste in this code ( ```laravel new Tutorial_Server``` ). It will take some time so wait for it to finish.
* Paste in this code ( ```cd Tutorial_Server``` )
* Paste in this code ( ```php artisan serve --host=0.0.0.0 --port=8080``` ). This will start a laravel server on your http://localhost:8080. 
You can specify your public ip instead of the "0.0.0.0" when you want your server available to the public.

You are now hosting your very own website! But this is not what we want so we need to move on because there is alot of fun ahead of us!

* Navigate to the newly created "Tutorial_Server" folder.
* Open another GitBash terminal ( shift+rightClick -> Git Bash ). We need the old one open because it's handling our laravel server.
* Paste in this code ( ```composer require laravel/passport``` ). Passport is a laravel package that allows us to create an API based
server with laravel. An API server is usefull because it can communicate to both a mobile application as well as browsers.
* Paste in this code ( ```composer install``` ).
* Paste in this code ( ```php artisan make:auth``` ). This will generate an authentification scaffold in Laravel that we can use to log
users in and register them.

Okay now we have all the dependencies we need. Let's get on to setting things up inside Laravel. At this point you should be using some
sort of IDE like Sublime Text or PhpStorm or Visual Studio Code or whatever you feel like.

Using the IDE of your choice follow these steps:

* Open the .env file in the root folder of the project.
* Replace the "DB_*" fields as follows:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tutorial_database
DB_USERNAME=root
DB_PASSWORD=

# This will tell Laravel where our database is and how to access it.
```

* Open the composer.json file in the root folder of the project.
* In the "require-dev": {} object add the following line:
```
"laracasts/generators": "dev-master",

# This will tell composer we want the laracasts/generatos package.
```

* In the GitBash terminal paste in this code ( ```composer update --dev``` )
* Open the AppServiceProvider.php file in /app/Providers/.
* Replace the register function as follows:
```
public function register()
{
    if ($this->app->environment() == 'local') {
        $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
    }
}

# This will register our Laravel Generator package.
```

* In the GitBash terminal paste this code ( ```php artisan migrate``` ). If you get an "Specified key was too long" error do the
following three steps, if not skip them.
* Add the following line after the "namespace App\Providers;" line:
```
use Illuminate\Support\Facades\Schema;
```
* Replace the boot function as follows:
```
public function boot()
{
    Schema::defaultStringLength(191);
}

# This will fix the "Specified key was too long" error.
```
* In the GitBash terminal paste this code ( ```php artisan migrate``` ).
* In the GitBash terminal paste this code ( ```php artisan passport:install``` ).
* In the GitBash terminal paste this code ( ```php artisan passport:client --password``` ).
* You will be asked for a name for the password grant we are creating. Enter ```tutorial_password_grant```. This will tell Laravel we
want to be able to allow apps to use the api using a secret code. The secret code is displayed in the terminal now but you can acess it
in phpMyAdmin so forget it for now.

// As a side note you can use passport for example to build an app like facebook in the sense that you can offer app hosting. You would
allow users to register clients that could access your api, and you would offer them highscores, login, register callbacks. You would do
this for example to offer people that do not want to host their own server the opportunity to have a user database with scores and
whatever you choose to offer. //

We should now continue setting up passport within laravel.

* Open the AuthServiceProvider.php file in /app/Providers/.
* Replace the register function as follows:
```
public function boot()
{
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addSecond(1));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
}

# This will register the routes or urls passport needs to communicate with clients. Then it will register token logic. Tokens are used
so that someone like a malicious hacker could not register calls on behalf of another user and act as a middle man.
We set the expiration period of the token to 1 second because we will want to remember to implement the refresh token methods in our
client. Setting it to one second will make passport return an error when we will make calls to the server after loging a user in thus
notifing us that we lack a refresh method. Moving on.
```
* After that add the following lines after this line ```"namespace App\Providers;"```:
```
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;

# We import the classes we need for our passport registers.
```

* Open the auth.php file in /config/.
* Replace this
```
'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],
```
with this:
```
'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],

# The guard is a method of protecting urls from unwanted access. Say for example you don't want an unregistered user to be able to
access a certain url for example http://serverAddress/api/user because that url would require user information that would not exist
because the user is just a guest.
```

Now we should setup the urls we want to have available on our server.

* Open the web.php file in /routes/.
* Comment everything out. We are creating an api server not a webserver that hosts web pages. We only host a database and urls that
clients can access.

We should now create an controller for our server. A controller is a php class that we use to store logic we want to assign to urls. For
example we want to register users that will use our app and we want some sort of logic for that so we can actually validate the input
for example.

* Open a GitBash terminal in the Tutorial_Server folder by shit+rightClicking inside it.
* Paste in this code ( ```php artisan make:controller Api/ApiController``` ). This will generate an Api folder for us and place an
ApiController.php file inside.

* Open the api.php file in /routes/.
* Replace this
```
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
```
with this:
```
Route::post('/register', 'Api\ApiController@register');
Route::middleware('auth:api')->get('/logout', 'Api\ApiController@logout');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user()->toJson();
});

# We register the url "http://localhost:8080/api/register" to function as follows:
- when a user makes a POST request to the /api/register url with his register credentials we then pass those credentials to the 
ApiController class we have just created. Then the register (@register) method within that class is called. If the method does not exist
the server would throw an error. We need to do this to have control over how the user registers himself because we want for example to
use this with a Facebook login option for example. We will actually do this too further on.
- the method will then return information or just a status code like "ok" or "success".
# The same goes for the /logout except we specify the middleware meaning what guards we want to use. We want to use a guard because we
could not for example logout a guest, only a user so we only want logged in users to be able to logout so we specify the auth:api (api
authentification guard).
```

Now we should actually fill out the ApiController with the register and logout methods.

* Open the AuthServiceProvider.php file in /app/Providers/.
* Replace this:
```
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
```
With this:
```
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use function MongoDB\BSON\toJSON;
```

Now inside the class we need the following things so after the ```"class ApiController extends Controller {"``` do this:
* Paste in this code:
```
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

# This tells laravel we will use this method to register users.
```
* Paste in this code:
```
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

# This is our logout function. Here we revoke the user's token so that calls cannot be made on his behalf anymore and return a success
message to the client app.
```
* Paste in this code:
```
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
        $valid = validator($request->only('guestName', 'password'), [
            'guestName' => 'required|string|max:255|unique:users',
        ]);

        if ($valid->fails()) {
            $jsonError=response()->json($valid->errors()->all(), 400);
            return \Response::json($jsonError);
        }

        $data = request()->only('guestName');

        $guestName = $data['guestName'];
        $name = "Guest_".(User::all()->last() ? User::all()->last()->id ++ : 1);

        $user = User::create([
            'name' => $name,
            'email' => null,
            'password' => bcrypt($guestName),
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
    
# In this function we can see we have it split in two major if statements that check wether the account is an guest account/an account
registered with our own api/an facebook account. If we have an ```email``` parrameter in the ```$request``` parrameter then we know the
user registered using our api and we validate the input then create a user based on that input and store it in our database. If we have
an ```fbId``` in the ```$request``` parameter then we treat that request accodringly as we did with the previous one. If there are none
of the above present we then know the user does not want to register so we register him with a guest account meaning we generate and
assign him the data he did not want to provide us with. Within our client we will generate a unique id when the app is first run and
store it so we can use it instead of a password and email or fbId. After all that is done we return a success message to the app client.
```

Right now we set the user to have some fields we did not discuss for example the ```highscore / game money``` and so on. We need to
create a database model for laravel so that it will know how to work with those fields when saving them in our databse.

* Open the User.php file in /app/.
* Replace this:
```
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
```
With this:
```
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
```
Now inside the class we need the following things so after the ```"class User extends Authenticatable {"``` do this:
* Paste in this code:
```
use HasApiTokens, Notifiable;

/**
 * The attributes that are mass assignable.
 *
 * @var array
 */
protected $fillable = [
    'name', 'password', 'email', 'highscore', 'game_money', 'money', 'fbId',
    'xp', 'level', 'guestName',
];

/**
 * The attributes that should be hidden for arrays.
 *
 * @var array
 */
protected $hidden = [
    'password', 'remember_token',
];

/**
 * The attributes that should be guarded.
 *
 * @var array
 */
protected $guarded = [
];

//the user will be able to login using either email or fbId or a guest account
public function findForPassport($identifier) {
    return $this->orWhere('email', $identifier)->orWhere('fbId', $identifier)->orWhere('guestName', $identifier)->first();
}

# Here we specify that the user has tokens so that passport knows how to handle logging the user in. We then specify the fields the user
has in the database and how they should be managed. For example laravel should not expose the fields entered in the ```$hidden``` object
we defined.
# We then add the method ```findForPassport($identifier)``` so that passport knows what types of fields we consider to be relevant to
logging users in. This means that for example we consider the ```email``` to be something unique in our database users table so we want
to use it as an identifier for people who logged in using an email. The same goes for the ```fbId``` and so on.
```

Now that we have setup what we would want from the user we need to add it to our database. For that laravel has created a migration
file. That file tells laravel that it should querry a CREATE method to our database with a ```users``` table containing ```id, name,
email, password``` fields and the token and timestamps.

* In the GitBash terminal running in the Tutorial_Servers folder paste the following code ( ```php artisan make:migration
add_fbId_guestName_highscore_game_money_xp_level_money_to_users``` ). This creates a new migration file we will use to add more collumns
to our users table from our database. We need to do this for the fileds we have setup in the ```User.php``` file, fields like
```highscores, fbId``` and so on.

* Open the file that contains ```"add_fbId_guestName_highscore_game_money_xp_level_money_to_users"``` in it's name from /database/migrations/.
* Replace everything with this:
```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFbIdGuestNameHighscoreGameMoneyXpLevelMoneyToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table) {
            $table->string('fbId')->nullable();
            $table->string('guestName')->nullable();
            $table->integer('highscore')->defaults(0);
            $table->integer('game_money')->defaults(0);
            $table->integer('xp')->defaults(0);
            $table->integer('level')->defaults(0);
            $table->integer('money')->defaults(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table) {
            $table->dropColumn('fbId');
            $table->dropColumn('guestName');
            $table->dropColumn('highscore');
            $table->dropColumn('game_money');
            $table->dropColumn('xp');
            $table->dropColumn('level');
            $table->dropColumn('money');
        });
    }
}
```
* In the GitBash terminal paste the following code ( ```php artisan make:migration change_email_in_users``` ). We need this to make the
email field nullable.

* Open the file that contains ```"change_email_in_users"``` in it's name from /database/migrations/.
* Replace everything with this:
```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEmailInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->change();
        });
    }
}

```
* In the GitBash terminal paste this code ( ```composer require doctrine/dbal``` ). This will enable us to change columns in the table.
* In the GitBash terminal paste this code ( ```php artisan migrate``` ). Now the email can be stored as being null.

 We finnaly finished setting up Laravel for now. We now have a functioning server that registers a user with some default data and is
 able to login/logout that user and show their info.

## Now it's time to move on to Unity and create some sort of logic that we will use to communicate with the server.


First thing is first, we need a server class that will hold all the data we need to handle the server like an api_user class and stull like that.

* Create a new unity project and name it ```"Tutorial_Client"```.
* In the assets folder create a ```"Resources"``` folder. In that folder create a ```"Scripts"``` folder. In that folder create a 
```"ServerApi"``` folder. In that folder create a ```"SecuredData"``` folder. In the ```"SecuredData"``` folder create two C# scripts named ```"Api_User"``` 
and ```"Api_Data"```.
* Open up the Api_Data class and paste this:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using System;

[Serializable]
public class Api_Data
{
    //made null in logout api call
    public Api_User user;
    //Api_UserData class is defined inside Api_User
    public Api_UserData userData;

    public Api_Data()
    {
        user = new Api_User();
        userData = new Api_UserData();
    }
}
# This class will hold all the information we request from the internet.
```

* Open up the Api_User class and paste this:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using System;

[Serializable]
public class Api_User
{
    public string access_token;
    public string refresh_token;

    public string name;
    public string email;
    public string password;
    public string fbId;
    public string highscore;
    public string game_money;
    public string money;
    public string xp;
    public string level;
    public string guestName;
    public string created_at;
    public string updated_at;
}

public class Api_UserData
{
    public Texture2D texture_profilePic;
}
```

All right, now we have something that can hold what we have setup inside our server. Now let's define some constants for it.

* In the ```"ServerApi"``` folder we need to create a ```"Server_Constants"``` C# class.
* In that file paste this:
```
using System;
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Server_Constants
{
    public const string serverApi_BaseUrl = "http://127.0.0.1:8080";
    
    //we use this identifier to register the client as a guest when he first opens the application.
    protected static string uniqueIdentifier;
    public static string GetUniqueIdentifier{
        get
        {
            if (uniqueIdentifier == null)
            uniqueIdentifier = PlayerPrefs.HasKey("uniqueIdentifier") ? PlayerPrefs.GetString("uniqueIdentifier") : GetUniqueID();

            return uniqueIdentifier;
        }
    }
    private static string GetUniqueID()
    {
        string key = "ID";

        var random = new System.Random();
        DateTime epochStart = new System.DateTime(1970, 1, 1, 8, 0, 0, System.DateTimeKind.Utc);
        double timestamp = (System.DateTime.UtcNow - epochStart).TotalSeconds;

        string uniqueID = Application.systemLanguage                            //Language
                + "-" + Application.platform                                            //Device    
                + "-" + String.Format("{0:X}", Convert.ToInt32(timestamp))                //Time
                + "-" + String.Format("{0:X}", Convert.ToInt32(Time.time * 1000000))        //Time in game
                + "-" + String.Format("{0:X}", random.Next(1000000000));                //random number
        
        if (PlayerPrefs.HasKey(key))
        {
            uniqueID = PlayerPrefs.GetString(key);
        }
        else
        {
            PlayerPrefs.SetString(key, uniqueID);
            PlayerPrefs.Save();
        }

        return uniqueID;
    }
}
# This class holds the information we need to be able to connect to the server.
```

All right, we're all set up with where we're going to store information from the server and what information we actually need from it. 
Now let's make our first request. In order to do this we should create some sort of interface to handle api calls since they all have
many things in common like the fact that they need to have some sort of beginning and end callbacks and some should be able to refresh
the server token and so on.

The purpose of this tutorial is to get as much people as possible to build their own client server project. In order to do so I decided 
to build api calls as GameObjects within unity so that destroying them would go smoothly and so that debugging would not be a problem
even for a novice user.

First we should build a manager for the api calls we are going to make.
* Inside the ```"ServerApi"``` folder create a new C# class named ```"Manager_Api_Call"```.
* Inside that class paste this:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Manager_Api_Call {

    List<Api_Call_Params> calls = new List<Api_Call_Params>();

    public void Generate_ApiCall(JSONObject data = null, System.Action<JSONObject> callback = null, string type = null)
    {
        Server._LoadingStart();

        //we generate an empty gameObject that will have an apiCall attached to it
        //after the api call is done the object will self destruct and if callback!=null will callback()
        GameObject go = new GameObject();

        //add the apiCall component to the game object and remember the info we need to initialize the call class
        Api_Call call = null;
        switch (type) {
            case api_register:
                call = go.AddComponent<Register>();
                break;
            case api_login:
                call = go.AddComponent<Login>();
                break;
            case api_logout:
                call = go.AddComponent<Logout>();
                break;
            case api_refreshToken:
                call = go.AddComponent<RefreshToken>();
                break;
            case api_registerFacebook:
                call = go.AddComponent<RegisterFacebook>();
                break;
            case api_registerGuest:
                call = go.AddComponent<RegisterGuest>();
                break;
            case api_userProfileDetails:
                call = go.AddComponent<UserProfileDetails>();
                break;
        }
        go.name = type;

        //create a params field and add it to the list and to the call so we can access it later.
        Api_Call_Params callParams = new Api_Call_Params(call, callback, data, type);
        calls.Add(callParams);
        call.callParams = callParams;
        //we only want to start one call at a time for this project so we need to check if there are any calls running or if we are just refreshing a token
        //the rest of the calls added to the list will be called after the last call dies
        if (calls.Count == 1 || call is RefreshToken)
        {
            InitiateCall(call, data, ApiCallback);
        }
    }

    Api_Call _currentCall;
    void InitiateCall(Api_Call call, JSONObject data, System.Action<JSONObject> callback)
    {
        _currentCall = call;
        Server.instance.StartCoroutine(_currentCall.Initialize(data, callback));
    }

    void ApiCallback(JSONObject data)
    {
        Debug.Log("API CALL:" + _currentCall.callParams.type);
        Debug.Log("API RESPONSE: " + data);
        Debug.Log("___________________");
        //we check for errors first
        if (data.HasField("message"))
        {
            //we check if we need to refresh the token
            if (data.GetField("message").str.Contains("Unauthenticated"))
            {
                //we don't propagate the callback anymore because the api call did not finish, it need to refesh the token first
                Debug.Log("Refreshing");
                RefreshToken();
                return;
            }
        }

        //we call the current call's callback
        if (calls.Count != 0)
            calls[0].callback(data);
    }

    public void RefreshToken()
    {
        BuildRefreshToken();
    }
    public void DestroyCall(Api_Call call)
    {
        //whenever a call dies out we check if there are any other calls we need to make for example we use this 
        //system when we refresh the token: the call gets a unauthorized error, then we skip destroying the call 
        //and instantiate and initiate a refresh call that will die out and reach this method. after that it will
        //reininitate the original call and all flows the same way until there are no more calls left in the stack

        //get rid of the refference
        calls.Remove(call.callParams);
        call = null;

        if (calls.Count != 0) //if there are any calls left we initiate the first one
        {
            Api_Call_Params current_call_params;
            current_call_params = calls[0];

            InitiateCall(current_call_params.call, current_call_params.data, ApiCallback);
        }
        else
        {
            //there are no more calls to make resume app
            Server._LoadingEnd();
        }
    }
    void BuildRefreshToken()
    {
        //we create a simple refresh token request using the info we have stored about the user.
        //if the user does not exist we return an error
        if (Server.apiData.user.refresh_token != null)
        {
            Generate_ApiCall(null, RefreshedToken, api_refreshToken);
        }
        else
        {
            //we should not be in here, there is no user logged in!
            ApiCallback(new JSONObject("{ "+'"'+ "error" + '"' + " : " + '"' + " No user! " + '"' + " }"));
            calls[0].call.DestroySelf();
        }
    }
    void RefreshedToken(JSONObject data)
    {
        Debug.Log("RefreshedToken");

        if (!string.IsNullOrEmpty(data.GetField("error").str))
        {
            //error from api
            ApiCallback(new JSONObject("{ " + '"' + "error" + '"' + " : " + '"' + " token refresh error! " + '"' + " }"));
            calls[0].call.DestroySelf();
        }
        else
        {
            //data from api
            //we don't need to do anything here because after the refresh token dies out it will reinitiate the api call that got locked
        }
    }

    //api calls...
    const string api_register = "Register";
    const string api_registerGuest = "RegisterGuest";
    const string api_registerFacebook = "RegisterFacebook";
    const string api_login = "Login";
    const string api_userProfileDetails = "UserProfileDetails";
    const string api_logout = "Logout";
    const string api_refreshToken = "RefreshToken";

    //public api calls
    public void Register(JSONObject data, System.Action<JSONObject> callback)
    {
        Generate_ApiCall(data, callback, api_register);
    }
    public void RegisterGuest(JSONObject data, System.Action<JSONObject> callback)
    {
        Generate_ApiCall(data, callback, api_registerGuest);
    }
    public void RegisterFacebook(JSONObject data, System.Action<JSONObject> callback)
    {
        Generate_ApiCall(data, callback, api_registerFacebook);
    }
    public void Login(JSONObject data, System.Action<JSONObject> callback)
    {
        Generate_ApiCall(data, callback, api_login);
    }
    //

    //for registered users
    public void UpdateUser(JSONObject data)
    {
        Server.apiData.user = JsonUtility.FromJson<Api_User>(data.Print());
    }
    public void UserProfileDetails(System.Action<JSONObject> callback)
    {
        Generate_ApiCall(null, callback, api_userProfileDetails);
    }
    public void Logout(System.Action<JSONObject> callback)
    {
        Generate_ApiCall(null, callback, api_logout);
    }
    //
}
# The code is pretty well commented out so feel free to look around and understand the logic.
```

* Inside the ```"ServerApi"``` folder create a new folder named ```"Objects"``` and in that one create a folder named ```"Api"```. 
Inside Api create a C# class named ```"Api_Call_Params"```.
* Inside that class paste this:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Api_Call_Params {

    public Api_Call call = null;
    public System.Action<JSONObject> callback = null;
    public JSONObject data = null;
    public string type = null;
 
    public Api_Call_Params(Api_Call _call, System.Action<JSONObject> _callback, JSONObject _data, string _type)
    {
        call = _call;
        callback = _callback;
        data = _data;
        type = _type;
    }
}
# we use this class to help remember what api calls we have to make
```

Great! Now we have a functioning manager but we need actual calls.

* Create a C# class named ```"Api_call"``` now inside the ```"Api"``` folder.
* Inside Api_call.cs paste this:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine.UI;
using UnityEngine;

public abstract class Api_Call : MonoBehaviour {

    //api call details
    public string url;
    public JSONObject status_text = new JSONObject();
    
    protected System.Action<JSONObject> _callback;

    public Api_Call_Params callParams;
    //

    //
    public WWW www;
    WWWForm _form_forRegisteredUser;
    public WWWForm form_forRegisteredUser
    {
        get
        {
            if (_form_forRegisteredUser == null)
            {
                _form_forRegisteredUser = new WWWForm();
                _form_forRegisteredUser.AddField("grant_type", Server.grant_type);
                _form_forRegisteredUser.AddField("client_id", Server.client_id);
                _form_forRegisteredUser.AddField("client_secret", Server.client_secret);
            }

            return _form_forRegisteredUser;
        }
    }
    WWWForm _form_forGuestUser;
    public WWWForm form_forGuestUser
    {
        get
        {
            if (_form_forGuestUser == null)
            {
                _form_forGuestUser = new WWWForm();
            }

            return _form_forGuestUser;
        }
    }
    Hashtable _headers;
    public Hashtable headers
    {
        get
        {
            if (_headers == null)
            {
                _headers = new Hashtable();
                _headers.Add("Accept", "application/json");
                _headers.Add("Authorization", "Bearer " + Server.apiData.user.access_token);
            }

            return _headers;
        }
    }
    //
        
    public IEnumerator Initialize(JSONObject data, System.Action<JSONObject> callback)
    {
        status_text.Clear();
        _headers = null;
        _form_forGuestUser = null;
        _form_forRegisteredUser = null;

        _callback = callback;

        //we call the inheriting class's Call method. this is set in other classes not this one
        Call(data);
        yield return www;

        status_text = new JSONObject(www.text);
        status_text.AddField("error", new JSONObject(www.error));

        ResponseCallback();

        //after the call has returned a message from the server we destroy the object. some classes will redefine this "Die" method
        Die();
    }

    public virtual void Die()
    {
        //we check for errors first
        if (status_text.HasField("message") && status_text.GetField("message").str.Contains("Unauthenticated"))
        {
            //we check if we need to refresh the token
            if (_callback != null)
                _callback(status_text);
            return;
        }
        
        //if this call should call another function when it's finished it should do so now
        if (_callback != null)
            _callback(status_text);

        DestroySelf();

    }

    public void DestroySelf()
    {
        //the end callback function can be used in classes that inherit this one to set custom event logic when this call is done loading. you will set whatever you want to happen
        //at the end of each callback in the api call class that will inherit this one. you will use "public override void EndCallback() { code }".
        EndCallback();

        //we remove the call from the stack
        Server.manager_Api_Call.DestroyCall(this);

        //we remove the object for good
        GameObject.Destroy(gameObject);
    }

    public abstract void ResponseCallback();
    public abstract void EndCallback();

    public abstract void Call(JSONObject data);
}
# The code is pretty well commented out so feel free to look around and understand the logic. 
```

Awesome we're almost ready to make our first call to the server.

* Inside the ```"ServerApi"``` folder create a new c# class named ```"Server"```.
* Inside Server.cs paste this:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;


public class Server : MonoBehaviour
{
    public static Api_Data apiData = new Api_Data(); //this should be saved and loaded with playerprefs
    public static FacebookManager facebookManager = new FacebookManager();

    public static Server instance;

    public static Manager_Api_Call manager_Api_Call = new Manager_Api_Call();
    private void Start()
    {
        instance = this;
    }

    //api client details
    public const string grant_type = "password";
    public const string client_id = "3";
    public const string client_secret = "GkYWeJsAxXVZxmn2vXDfIOkemdZLY2hn3wMy2Att";
    //

    //callbacks for ui
    //we check if isLoading because we don't want the loadingStart ui callback to trigger when we start
    //the login api call right after registering. the register apiCall expects an immediate call to the login api call
    public static void _LoadingStart()
    {
        if (isLoading) return;
        isLoading = true;
        Debug.Log("Started Loading");
        if (LoadingStart != null)
        {
            LoadingStart();
        }
    }
    protected static bool isLoading = false;
    protected static System.Action LoadingStart;
    protected static System.Action LoadingEnd;
    public static void _LoadingEnd()
    {
        if (!isLoading) return;
        isLoading = false;
        Debug.Log("Stopped Loading");
        if (LoadingEnd != null)
        {
            LoadingEnd();
        }
    }
    //
    public static void Register(JSONObject data, System.Action<JSONObject> callback)
    {
        manager_Api_Call.Register(data, callback);
    }
    public static void RegisterGuest(JSONObject data, System.Action<JSONObject> callback)
    {
        manager_Api_Call.RegisterGuest(data, callback);
    }
    public static void RegisterFacebook(JSONObject data, System.Action<JSONObject> callback)
    {
        manager_Api_Call.RegisterFacebook(data, callback);
    }
    public static void Login(JSONObject data, System.Action<JSONObject> callback)
    {
        manager_Api_Call.Login(data, callback);
    }
    
    //for registered users
    public static void UserProfileDetails(System.Action<JSONObject> callback)
    {
        manager_Api_Call.UserProfileDetails(callback);
    }
    public static void Logout(System.Action<JSONObject> callback)
    {
        manager_Api_Call.Logout(callback);
    }
    //





    //Example functions...

    //facebook functions
    public void FacebookInit()
    {
        facebookManager.FBInit();
    }

    UnityEngine.UI.Button firstFriendPictureButton;
    public void GetFirstFriendUserPicture(UnityEngine.UI.Button targetButton)
    {
        firstFriendPictureButton = targetButton;
        Debug.Log("GetFirstFriendUserPicture");
        _LoadingStart();
        JSONObject firstUserFriend = facebookManager.facebookUser.friendsList[0];
        facebookManager.GetUserPicture(FacebookFinishedGetUserPicture, firstUserFriend.GetField("id").str);
    }

    public void FacebookFinishedGetUserPicture(Texture2D result)
    {
        Debug.Log("FacebookFinishedGetUserPicture");
        firstFriendPictureButton.image.sprite = Sprite.Create(result, new Rect(0, 0, 100, 100), new Vector2(.5f, .5f));
        _LoadingEnd();
    }

    public void FacebookFriends()
    {
        Debug.Log("FacebookFriends");
        _LoadingStart();
        facebookManager.GetFriends(FacebookFinishedFriends);
    }

    public void FacebookFinishedFriends(JSONObject result)
    {
        Debug.Log("FacebookFinishedFriends");
        Debug.Log(result);
        _LoadingEnd();
    }

    public void FacebookLogin()
    {
        Debug.Log("FacebookLogin");
        _LoadingStart();
        facebookManager.Login(FacebookFinishedLogin);
    }

    public void FacebookFinishedLogin(JSONObject result)
    {
        Debug.Log("FacebookFinishedLogin");
        Debug.Log(result);
        facebookManager.GetUserName(FacebookFinishedUsername);
    }

    public void FacebookFinishedUsername(JSONObject result)
    {
        Debug.Log("FacebookFinishedUsername");
        Debug.Log(result);
        RegisterFacebookLogin();
    }

    void RegisterFacebookLogin()
    {
        Debug.Log("RegisterFacebookLogin");
        JSONObject data = new JSONObject();
        data.AddField("name", facebookManager.facebookUser.name);
        data.AddField("fbId", facebookManager.facebookUser.user_id);
        data.AddField("password", Server_Constants.GetUniqueIdentifier);

        RegisterFacebook(data, OnRegisterFacebookLoginEnd);
    }
    void OnRegisterFacebookLoginEnd(JSONObject status)
    {
        //data from api
        if (status.HasField("success") || (status.HasField("original") && status.GetField("original")[0].str.Contains("The fb id has already been taken.")) )
        {
            LoginFacebookLogin();
        }
    }
    void LoginFacebookLogin()
    {
        JSONObject data = new JSONObject();
        data.AddField("username", facebookManager.facebookUser.user_id);
        data.AddField("password", Server_Constants.GetUniqueIdentifier);
        data.AddField("scope", "");

        Login(data, OnLoginFacebookLoginEnd);
    }
    void OnLoginFacebookLoginEnd(JSONObject status)
    {
        //data from api
    }
    //




    //Login Function
    public void FakeLoginUser()
    {
        LoginUser("first@mail.com", "password");
    }
    public void LoginUser(string username, string password, string scope = "")
    {
        JSONObject data = new JSONObject();
        data.AddField("username", username);
        data.AddField("password", password);
        data.AddField("scope", scope);

        DoLoginUser(data);
    }
    void DoLoginUser(JSONObject data)
    {
        Server.Login(data, OnLoginEnd);
    }
    void OnLoginEnd(JSONObject status)
    {
        //data from api
    }
    //




    //Login Function
    public void FakeFacebookLogin()
    {
        FacebookLogin("first@mail.com", "password");
    }
    public void FacebookLogin(string username, string password, string scope = "")
    {
        JSONObject data = new JSONObject();
        data.AddField("username", username);
        data.AddField("password", password);
        data.AddField("scope", scope);

        DoFacebookLogin(data);
    }
    void DoFacebookLogin(JSONObject data)
    {
        Server.Login(data, OnFacebookLoginEnd);
    }
    void OnFacebookLoginEnd(JSONObject status)
    {
        //data from api
    }
    //



    //Register Function
    public void FakeRegisterUser()
    {
        RegisterUser("first", "firsts@mail.com", "password");
    }
    public void RegisterUser(string name, string email, string password)
    {
        JSONObject data = new JSONObject();
        data.AddField("name", name);
        data.AddField("email", email);
        data.AddField("password", password);

        DoRegisterUser(data);
    }
    void DoRegisterUser(JSONObject data)
    {
        Server.Register(data, OnRegisterEnd);
    }
    void OnRegisterEnd(JSONObject status)
    {
        //data from api
        //the Register api call expects an immediate call to the Login api call
        if (status.HasField("success"))
        {
            FakeLoginUser();
        }
    }
    //


    //RegisterGuest Function
    public void FakeRegisterGuestUser()
    {
        RegisterGuestUser();
    }
    public void RegisterGuestUser()
    {
        JSONObject data = new JSONObject();
        data.AddField("guestName", Server_Constants.GetUniqueIdentifier);

        DoRegisterGuestUser(data);
    }
    void DoRegisterGuestUser(JSONObject data)
    {
        Server.RegisterGuest(data, OnRegisterGuestEnd);
    }
    void OnRegisterGuestEnd(JSONObject status)
    {
        //data from api
        //the Register api call expects an immediate call to the Login api call
        if (status.HasField("success"))
        {
            FakeLoginGuestUser();
        }
    }
    //


    //Login Guest Function
    public void FakeLoginGuestUser()
    {
        LoginGuestUser();
    }
    public void LoginGuestUser()
    {
        JSONObject data = new JSONObject();
        data.AddField("username", Server_Constants.GetUniqueIdentifier);
        data.AddField("password", Server_Constants.GetUniqueIdentifier);
        data.AddField("scope", "");

        DoLoginGuestUser(data);
    }
    void DoLoginGuestUser(JSONObject data)
    {
        Server.Login(data, OnLoginGuestEnd);
    }
    void OnLoginGuestEnd(JSONObject status)
    {
        //data from api
    }
    //



    //UserDetails Function
    public void FakeUserProfileDetails()
    {
        DoUserProfileDetails();
    }
    void DoUserProfileDetails()
    {
        Server.UserProfileDetails(OnUserProfileDetailsEnd);
    }
    void OnUserProfileDetailsEnd(JSONObject status)
    {
        //data from api
    }
    //



    //Logout Function
    public void FakeLogout()
    {
        DoFakeLogout();
    }
    void DoFakeLogout()
    {
        Server.Logout(OnFakeLogoutEnd);
    }
    void OnFakeLogoutEnd(JSONObject status)
    {
        //data from api
    }
    //
}
# The code is pretty well commented out so feel free to look around and understand the logic. You have examples for about 
everything you will need to login/register/view/edit a user.
```

Now we're all set to build and run the api calls we are using in the ```Server.cs``` we have just made.

Let's create them one by one they are not that many:

* First create a folder named ```"ApiCalls"``` in the folder named ```"ServerApi"```. Then create another folder named 
```"Auth"``` (for authentification) in the ```ApiCalls``` folder.

* Inside the ```Auth``` folder create a new c# class named ```"Register"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Register : Api_Call
{
    public override void Call(JSONObject data)
    {
        url = Server_Constants.serverApi_BaseUrl + "/api/register";
        
        form_forGuestUser.AddField("name", data.GetField("name").str);
        form_forGuestUser.AddField("email", data.GetField("email").str);
        form_forGuestUser.AddField("password", data.GetField("password").str);

        www = new WWW(url, form_forGuestUser);
    }

    public override void ResponseCallback()
    {
        if (!status_text.HasField("success"))
        {
            //credentials already used (account details not unique)
        }
    }

    public override void EndCallback()
    {

    }
}
# Pretty straight forward, we make a form with the info we would need to register a user and then if we get a good 
response we move on.
```

* Inside the ```Auth``` folder create a new c# class named ```"Login"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Login : Api_Call
{
    public override void Call(JSONObject data)
    {
        url = Server_Constants.serverApi_BaseUrl + "/oauth/token";

        form_forRegisteredUser.AddField("username", data.GetField("username").str);
        form_forRegisteredUser.AddField("password", data.GetField("password").str);
        form_forRegisteredUser.AddField("scope", data.GetField("scope").str);
        
        www = new WWW(url, form_forRegisteredUser);
    }

    public override void ResponseCallback()
    {
        if (status_text.HasField("access_token"))
        {
            JSONObject serverUserData = new JSONObject();
            serverUserData.AddField("access_token", status_text.GetField("access_token").str);
            serverUserData.AddField("refresh_token", status_text.GetField("refresh_token").str);
            Server.manager_Api_Call.UpdateUser(serverUserData);
        }
    }

    public override void EndCallback()
    {

    }
}
# Pretty straight forward, we make a form with the info we would need to login a user and then if we get a good 
response we update our Api_User.
```

* Inside the ```Auth``` folder create a new c# class named ```"Logout"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Logout : Api_Call
{
    public override void Call(JSONObject data = null)
    {
        url = Server_Constants.serverApi_BaseUrl + "/api/logout";
        
        www = new WWW(url, null, headers);
    }

    public override void ResponseCallback()
    {

    }

    public override void EndCallback()
    {         
        if (status_text.HasField("success"))
        {
            Server.apiData.user = new Api_User();

            Server.facebookManager.Logout();
        }
    }
}
# Pretty straight forward, we make a request with the info we would need to logout a user and then if we get a good 
response we update our Api_User.
```

* Inside the ```Auth``` folder create a new c# class named ```"RefreshToken"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class RefreshToken : Api_Call
{
    public override void Call(JSONObject data)
    {
        url = Server_Constants.serverApi_BaseUrl + "/oauth/token";
        
        form_forRegisteredUser.AddField("grant_type", "refresh_token");
        form_forRegisteredUser.AddField("refresh_token", Server.apiData.user.refresh_token);
        form_forRegisteredUser.AddField("scope", "");

        www = new WWW(url, form_forRegisteredUser);
    }

    public override void ResponseCallback()
    {
        if (status_text.HasField("access_token"))
        {
            JSONObject serverUserData = new JSONObject();
            serverUserData.AddField("access_token", status_text.GetField("access_token").str);
            serverUserData.AddField("refresh_token", status_text.GetField("refresh_token").str);
            Server.manager_Api_Call.UpdateUser(serverUserData);
        }
    }

    public override void EndCallback()
    {

    }
}
# Pretty straight forward, we make a form with the info we would need to refresh a token and then if we get a good 
response we update our Api_User with the new token.
```

* Inside the ```Auth``` folder create a new c# class named ```"RegisterGuest"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class RegisterGuest : Api_Call
{

    public override void Call(JSONObject data)
    {
        url = Server_Constants.serverApi_BaseUrl + "/api/register";

        form_forGuestUser.AddField("guestName", data.GetField("guestName").str);

        www = new WWW(url, form_forGuestUser);
    }

    public override void ResponseCallback()
    {
        if (!status_text.HasField("success"))
        {
            //credentials already used (account details not unique)
        }
    }

    public override void EndCallback()
    {

    }
}
# Pretty straight forward, we make a request with the info we would need to register a guest user and then if we get a good 
response we move on.
```

* Inside the ```Auth``` folder create a new c# class named ```"RegisterFacebook"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class RegisterFacebook : Api_Call
{
    public override void Call(JSONObject data)
    {
        url = Server_Constants.serverApi_BaseUrl + "/api/register";

        form_forGuestUser.AddField("name", data.GetField("name").str);
        form_forGuestUser.AddField("fbId", data.GetField("fbId").str);
        form_forGuestUser.AddField("password", data.GetField("password").str);

        www = new WWW(url, form_forGuestUser);
    }

    public override void ResponseCallback()
    {
        if (!status_text.HasField("success"))
        {
            //credentials already used (account details not unique)
        }
    }

    public override void EndCallback()
    {

    }
}
# Pretty straight forward, we make a request with the info we would need to register a facebook user and then if we get a good 
response we move on.
```

* Inside the ```ApiCalls``` folder create a new c# class named ```"UserProfileDetails"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class UserProfileDetails : Api_Call
{
    public override void Call(JSONObject data)
    {
        url = Server_Constants.serverApi_BaseUrl + "/api/user";

        www = new WWW(url, null, headers);
    }

    public override void ResponseCallback()
    {

    }

    public override void EndCallback()
    {

    }
}
# Pretty straight forward, we make a request with the info we would need to register a facebook user and then if we get a good 
response we move on.
```

Okay, now make sure you download the official ```Facebook beta``` app from the asset store in unity and also get the ```JSON Object``` 
asset from ```Defective Studios```.

* Inside the ```Scripts``` folder create a new folder named ```"Facebook"```.
* Inside the ```Facebook``` folder create a new c# class named ```"FacebookUser"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;

[System.Serializable]
public class FacebookUser  {
    public string permissions;
    public string expiration_timestamp;
    public string access_token;
    public string user_id;
    public string[] granted_permissions;
    public string[] declined_permissions;
    public string callback_id;
    public string name;
    public JSONObject friendsList;
}
# We use these params to remember our facebook info.
```

* Inside the ```Facebook``` folder create a new c# class named ```"FacebookUser"``` and paste in this code:
```
using System.Collections;
using System.Collections.Generic;
using Facebook.Unity;
using UnityEngine;

public class FacebookManager {

    public FacebookUser facebookUser;

	public void FBInit()
    {
        FB.Init(OnInitComplete);
    }

    public System.Action<JSONObject> Login_callback;
    public void Login(System.Action<JSONObject> callback)
    {
        Login_callback = callback;
        FB.LogInWithPublishPermissions(new List<string>() { "publish_actions", "public_profile", "user_friends" }, OnLoginComplete);
    }

    public void OnInitComplete()
    {
        Debug.Log("fb init: " + FB.IsInitialized);
    }

    public void OnLoginComplete(ILoginResult result)
    {
        if (result.Error != null)
        {
            Debug.Log("fb login error: " + result.Error);
        }
        else if (result.Cancelled)
        {
            Debug.Log("fb login: " + "Canceled by user");
        }        
        else
        {
            Debug.Log("fb login: " + result.RawResult);
            JSONObject resultJson = new JSONObject(result.RawResult);
            facebookUser = JsonUtility.FromJson<FacebookUser>(result.RawResult);
            Login_callback(resultJson);
        }
    }

    public System.Action<JSONObject> GetUserName_callback;
    public void GetUserName(System.Action<JSONObject> callback)
    {
        GetUserName_callback = callback;
        //right now this only gets the user name
        FB.API("/me?fields=name", HttpMethod.GET, DoGetUserName);
    }

    public void DoGetUserName(IGraphResult result)
    {
        if (result.Error != null)
        {
            Debug.Log("fb user name error: " + result.Error);
        }
        else if (result.Cancelled)
        {
            Debug.Log("fb user name: " + "Canceled by user");
        }
        else
        {
            Debug.Log("fb user name: " + result.RawResult);
            JSONObject resultJson = new JSONObject(result.RawResult);
            facebookUser.name = resultJson.GetField("name").str;
            GetUserName_callback(resultJson);
        }
    }

    public void Logout()
    {
        // called in the logout api call
        if (FB.IsInitialized && FB.IsLoggedIn)
        {
            Debug.Log("fb logout");
            FB.LogOut();
            facebookUser = null;
        }
    }

    public System.Action<JSONObject> GetFriends_callback;
    public void GetFriends(System.Action<JSONObject> callback)
    {
        GetFriends_callback = callback;
        FB.API("/me/friends", HttpMethod.GET, DoGetFriends);
    }

    public void DoGetFriends(IGraphResult result)
    {
        if (result.Error != null)
        {
            Debug.Log("fb user friends error: " + result.Error);
        }
        else if (result.Cancelled)
        {
            Debug.Log("fb user friends: " + "Canceled by user");
        }
        else
        {
            Debug.Log("fb user friends: " + result.RawResult);
            JSONObject resultJson = new JSONObject(result.RawResult);
            facebookUser.friendsList = resultJson.GetField("data");
            GetFriends_callback(resultJson);
        }
    }

    public System.Action<Texture2D> GetUserPicture_callback;
    public void GetUserPicture(System.Action<Texture2D> callback, string fbId, float width = 100, float height = 100)
    {
        GetUserPicture_callback = callback;
        FB.API(fbId + "/picture?width=" + width + "& height=" + height, HttpMethod.GET, PictureCallBack);
    }
    void PictureCallBack(IGraphResult result)
    {
        if (result.Error != null)
        {
            Debug.Log("fb user picture error: " + result.Error);
        }
        else if (result.Cancelled)
        {
            Debug.Log("fb user picture: " + "Canceled by user");
        }
        else
        {
            Debug.Log("fb user picture: " + result.RawResult);
            GetUserPicture_callback(result.Texture);
        }
    }
}
# This is not the best way to go about it but this is not about facebook, it's just a proof of concept at this point.
```

