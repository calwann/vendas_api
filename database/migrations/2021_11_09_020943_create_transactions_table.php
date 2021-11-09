<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->bigInteger('payer_user_id')->nullable()->unsigned();
                $table->foreign('payer_user_id')->references('id')->on('users');
                $table->bigInteger('payee_user_id')->nullable()->unsigned();
                $table->foreign('payee_user_id')->references('id')->on('users');
                $table->decimal('value', 12, 3);
                $table->enum('type', ['Transfer', 'Deposit']);
                $table->enum('status', ['Pending', 'Done', 'Reversal']);
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
        Schema::dropIfExists('transactions');
    }
}
