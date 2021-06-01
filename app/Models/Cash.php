<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    protected $table = "cash";

    public function cashMoney() {
        return $this->hasMany("App\Models\CashMoney");
    }

    public function cashMovements() {
        return $this->hasMany("App\Models\CashMovement");
    }

    public function logs() {
        return $this->hasMany("App\Models\Log");
    }
}
