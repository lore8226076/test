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
        Schema::create('blocklists', function (Blueprint $table) {
            $table->bigIncrements('id');
        
            $table->string('uid', 255)->index()->comment('封鎖者 uid');
            $table->string('blocked_uid', 255)->index()->comment('被封鎖者 uid');
        
            $table->timestamp('blocked_at')->useCurrent()->comment('封鎖時間');
        
            $table->unique(['uid', 'blocked_uid']);
        });
        DB::statement('ALTER TABLE blocklists ADD CONSTRAINT check_self_blocking CHECK (uid != blocked_uid)');
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocklists');
    }
};
