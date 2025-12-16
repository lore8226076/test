<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
        
            $table->string('reporter_uid', 255)->index()->comment('檢舉者 uid');
            $table->string('reported_uid', 255)->index()->comment('被檢舉者 uid');
        
            $table->string('type', 50)->comment('檢舉類型');
            $table->text('reason')->nullable()->comment('檢舉說明');
        
            $table->string('status', 20)->default('pending')->comment('狀態：pending / reviewed / rejected');
            $table->timestamp('reported_at')->useCurrent()->comment('檢舉時間');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
