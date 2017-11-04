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
* Paste in this code ( ```php artisan serve --host=0.0.0.0 --port=8080``` ). This will start a laravel server on your http://localhost:8080. You can specify your public ip instead of the "0.0.0.0" when you want your server available to the public.

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
        ]);
    }else{//the user is a guest
        $username = "Guest_".(User::all()->last() ? User::all()->last()->id ++ : 1);
        $randomPassword = substr(str_shuffle(str_repeat($x=$username, ceil(8/strlen($x)) )),1,8);

        $user = User::create([
            'name' => $username,
            'email' => null,
            'password' => $randomPassword,
            'highscore' => '0',
            'game_money' => '0',
            'money' => '0',
            'level' => '0',
            'xp' => '0',
            'fbId' => null,
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
assign him the data he did not want to provide us with. After all that is done we return a success message to the app client.
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


