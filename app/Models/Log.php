<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = "logs";

    public function movement() {
        return $this->hasOne("App\Models\CashMovement");
    }

    public function cash() {
        return $this->belongsTo("App\Models\Cash");
    }

    public function logDetails() {
        return $this->hasMany("App\Models\LogDetail");
    }
}
