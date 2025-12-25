<?php
// database/migrations/xxxx_xx_xx_create_password_otps_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('password_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code_hash');
            $table->timestamp('expires_at')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('resends')->default(0);
            $table->string('purpose')->default('password_reset');
            $table->timestamps();

            $table->unique('email'); // one active reset OTP per email
        });
    }

    public function down(): void {
        Schema::dropIfExists('password_otps');
    }
};
