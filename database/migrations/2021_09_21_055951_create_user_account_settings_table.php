<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAccountSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_account_settings', function (Blueprint $table) {
            $table->id();
            $table->string('numbers');
            $table->string('current_location');
            // $table->decimal('latitude', 10, 8);
            // $table->decimal('longitude', 11, 8);
            $table->float('latitude',4,2);
            $table->float('longitude',4,2);
            $table->integer('show_me_sos');
            $table->string('gender');
            $table->string('Job_title');
            $table->string('maximum_distance');
            $table->integer('min_age');
            $table->integer('max-age');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_account_settings');
    }
}
