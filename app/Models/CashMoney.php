<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMoney extends Model
{
    protected $table = "cash_money";

    public function cash() {
        return $this->belongsTo("App\Models\Cash");
    }

    public function money() {
        return $this->belongsTo("App\Models\Money");
    }
}
