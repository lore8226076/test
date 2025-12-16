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
        Schema::create('redeem_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 64)->unique()->comment('兌換碼');                        // 兌換碼
            $table->string('name')->comment('兌換碼標題或說明');                       // 兌換碼標題或說明
            $table->date('start_at')->nullable()->comment('啟用日期');                   // 啟用日期
            $table->date('end_at')->nullable()->comment('到期日期');                     // 到期日期
            $table->string('rewards', 255)->nullable()->comment('獎勵內容');               // 獎勵內容
            $table->string('memo', 255)->nullable()->comment('備註');                        // 備註
            $table->timestamps();                                                              // created_at, updated_at
            $table->softDeletes();
            $table->index('code');
            $table->index('start_at');
            $table->index('end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeem_codes');
    }
};
