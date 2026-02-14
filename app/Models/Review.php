<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('yandex_review_id')->unique();
            $table->string('author_name');
            $table->integer('rating');
            $table->text('text');
            $table->dateTime('published_at');
            $table->timestamps();
        });
    }
}
