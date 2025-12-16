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
        Schema::create('account_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('使用者ID');
            $table->string('uid')->nullable()->comment('使用者UID');
            $table->string('email_hash')->nullable()->comment('使用者Email Hash');
            $table->string('email_masked')->nullable()->comment('使用者Email 遮罩');
            $table->timestamp('deleted_at')->comment('刪除時間');
            $table->string('deleted_by')->default('system')->comment('刪除者');
            $table->string('reason')->nullable()->comment('刪除原因');
            $table->boolean('has_payment')->default(false)->comment('是否曾付款');
            $table->integer('orders_count')->default(0)->comment('訂單數量');
            $table->boolean('violation_flag')->default(false)->comment('違規旗標');
            $table->json('extra')->nullable()->comment('其他資料');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_deletion_logs');
    }
};
