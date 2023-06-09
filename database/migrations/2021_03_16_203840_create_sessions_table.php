<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date');
            $table->integer('duration');
            $table->timestamp('end_date')->nullable();
            $table->string('session_days');
            $table->integer('frequency');
            $table->text('description');
            $table->bigInteger('course_id')->unsigned();
            $table->foreign('course_id')->references('id')
                ->on('courses')->onDelete('cascade');
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
        Schema::dropIfExists('course_sessions');
    }
}
