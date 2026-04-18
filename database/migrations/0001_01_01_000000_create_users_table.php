<?php

/*
* `...\Migration` - provides methods for defining the structure of database tables and managing schema changes.
* `...\Blueprint` - provides methods for adding columns, indexes, and other table attributes.
*
* `...\Schema` - This facade provides a convenient interface for interacting with the database schema. 
* It allows you to create, modify, and drop tables using a fluent syntax.
*/
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
* In Laravel, a migration is a type of version control for your database. 
* Used to create, modify, and delete database tables and columns, as well as to manage indexes and foreign keys.
* Provides way to easily share and synchronize database schema changes across different environments (development, staging, and production).
* Each migration file typically contains two methods. The `up` method defines the changes to be made to the database schema,
* while the `down` method defines how to reverse those changes. 
* This allows you to easily apply or roll back migrations as needed.


* used to define an anonymous class that extends the `Migration` class. 
* This allows you to create a migration without having to give it a specific name, which can be useful for simple migrations that don't 
* require a lot of custom logic.
*/
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This method is used to define the database schema for the `users`, `password_reset_tokens`, and `sessions` tables. 
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /*
    * This method is used to drop the `users`, `password_reset_tokens`, and `sessions` tables from the database, 
    * effectively reversing the changes made by the `up` method.
    */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
