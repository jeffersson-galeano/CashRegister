<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDetail extends Model
{
    protected $table = "log_details";

    public function log() {
        return $this->belongsTo("App\Models\Log");
    }

    public function cashMoney() {
        return $this->belongsTo("App\Models\CashMoney");
    }
}
