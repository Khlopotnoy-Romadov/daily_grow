<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('yandex_map_link')->nullable();
            $table->string('company_name')->nullable();
            $table->string('yandex_org_id')->nullable();
            $table->timestamps();
        });
    }
}
