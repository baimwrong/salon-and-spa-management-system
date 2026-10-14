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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->unique()->index();
            $table->string('gender')->nullable(); // female, male, other
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('skin_type')->nullable(); // Normal, Dry, Oily, Combination, Sensitive
            $table->string('hair_type')->nullable(); // Straight, Wavy, Curly, Coily, Chemically Treated
            $table->text('allergies')->nullable();
            $table->foreignId('preferred_therapist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('active')->index(); // active, inactive, vip, blocked
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
