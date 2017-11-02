# ApiServer-Unity-Laravel-Passport
A well detailed tutorial for a server based project made in Unity. This template provides you with every step you need to take to setup your own php server using laravel and having it communicate with unity.

This tutorial is designed for someone with basic computer knowledge that has never seen the technologies we are going to be using so bear with me until we get to the interesting stuff. 

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
Xampp will host our php and mysql servers. Php handles the requests unity will make to the server and mysql will hold the data we want to store like user info or whatever. This tutorial is for a php based server so it cannot be used for realtime responses. For that you need to use a socket server like socket.io which is based on node.js. I will probably make a tutorial on that too if this one gets rep. Moving on.

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
* Paste in this code ( ```composer require laravel/passport``` ). Passport is a laravel package that allows us to create an API based server with laravel. An API server is usefull because it can communicate to both a mobile application as well as browsers.
* Paste in this code ( ```composer install``` ).
* Paste in this code ( ```php artisan make:auth``` ). This will generate an authentification scaffold in Laravel that we can use to log users in and register them.

Okay now we have all the dependencies we need. Let's get on to setting things up inside Laravel. At this point you should be using some sort of IDE like Sublime Text or PhpStorm or Visual Studio Code or whatever you feel like.

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

* In the GitBash terminal paste this code ( ```php artisan migrate``` ). If you get an "Specified key was too long" error do the following three steps, if not skip them.
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
php
* In the GitBash terminal paste this code ( ```php artisan passport:install``` ).
* In the GitBash terminal paste this code ( ```php artisan passport:client --password``` ).
* You will be asked for a name for the password grant we are creating. Enter ```tutorial_password_grant```. This will tell Laravel we want to be able to allow apps to use the api using a secret code.
* The secret code is displayed in the terminal now but you can acess it in phpMyAdmin so forget it for now.


