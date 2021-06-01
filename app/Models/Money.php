<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Money extends Model
{
    protected $table = "money";

    public function cashMoney() {
        return $this->hasMany("App\Models\CashMoney");
    }

    public function lodDetails() {
        return $this->hasMany("App\Models\LogDetail");
    }
}
