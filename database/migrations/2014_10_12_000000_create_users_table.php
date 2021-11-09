<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->bigInteger('cpf')->nullable()->unique();
                $table->bigInteger('cnpj')->nullable()->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->enum('type', ['User', 'Shopkeeper', 'Admin']);
                $table->enum('status', ['Pending', 'Wait', 'Active'])->default('Active');
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
