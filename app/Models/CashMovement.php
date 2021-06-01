<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $table = "cash_movements";

    public function cash() {
        return $this->belongsTo("App\Models\Cash");
    }

    public function movementType() {
        return $this->belongsTo("App\Models\MovementType");
    }

    public function movementDetails() {
        return $this->hasMany("App\Models\MovementDetail");
    }

    public function log() {
        return $this->belongsTo("App\Models\Log");
    }
}
