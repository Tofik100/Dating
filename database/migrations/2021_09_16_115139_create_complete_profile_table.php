<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompleteProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complete_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('user_bio');
            $table->json('user_image_uploade');
            $table->string('job_title');
            $table->string('univercity_name');
            $table->string('gender');
            $table->string('company');
            $table->boolean('don’t_show_my_age');
            $table->boolean('distance_invisible');
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
        Schema::dropIfExists('complete_profile');
    }
}
