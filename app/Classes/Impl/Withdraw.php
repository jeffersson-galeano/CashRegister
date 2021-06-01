<?php

namespace App\Classes\Impl;

use App\Classes\AddCash;
use App\Classes\MoneyBack;
use App\Classes\Transaction;
use App\Models\Cash;
use App\Models\CashMovement;
use App\Models\Log;
use App\Models\LogDetail;
use App\Models\MovementType;

class Withdraw implements Transaction, MoneyBack {

    private $withDrawValue;
    private $cash;

    public function __construct() {
        $this->withDrawValue = 0;
        $this->cash = Cash::find(1);
    }

    public function moneyBack(): array {
        $moneyBack = [];
        foreach($this->cash->cashMoney as $cashMoney) {
            if($cashMoney->amount > 0) {
                $money = [
                    "denominacion" => $cashMoney->money->value,
                    "cantidad" => $cashMoney->amount
                ];
    
                $cashMoney->amount = 0;
                $cashMoney->save();
                $moneyBack[] = $money;
            }
        }

        $this->withDrawValue = $this->cash->total_money;

        $this->cash->total_money = 0;
        $this->cash->save();
        return $moneyBack;
    }

    public function doTransaction(): array {
        $moneyBack = $this->moneyBack();
        $this->addMovement();
        return ["totalRetiro" => $this->withDrawValue, "dinero" => $moneyBack];
    }

    public function addMovement(): void {
        $cashMovement = new CashMovement();
        $cashMovement->cash_id = $this->cash->id;

        $movementType = MovementType::where("name", "Vaciar Caja")->first();
        $cashMovement->movement_type_id = $movementType->id;
        $cashMovement->in_value = 0;
        $cashMovement->out_value = $this->withDrawValue;
        $cashMovement->net_income = 0;
        $cashMovement->save();

        $log = new Log();
        $log->cash_movements_id = $cashMovement->id;
        $log->cash_id = $this->cash->id;
        $log->total_money = $this->cash->total_money;
        $log->save();
        foreach ($this->cash->cashMoney as $value) {
            $logDetails = new LogDetail();
            $logDetails->log_id = $log->id;
            $logDetails->cash_money_id = $value->id;
            $logDetails->amount = $value->amount;
            $logDetails->save();
        }
    }
}