<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementDetail extends Model
{
    protected $table = "movement_detail";

    public function movement() {
        return $this->belongsTo("App\Models\CashMovement");
    }

    public function money() {
        return $this->belongsTo("App\Models\Money");
    }
}