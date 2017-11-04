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
