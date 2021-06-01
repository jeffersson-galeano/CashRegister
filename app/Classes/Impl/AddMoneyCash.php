<?php

namespace App\Classes\Impl;

use App\Classes\AddCash;
use App\Classes\Transaction;
use App\Exceptions\ValidationException;
use App\Models\Cash;
use App\Models\CashMoney;
use App\Models\CashMovement;
use App\Models\Log;
use App\Models\LogDetail;
use App\Models\Money;
use App\Models\MovementDetail;
use App\Models\MovementType;
use Exception;
use Illuminate\Support\Facades\DB;

class AddMoneyCash implements AddCash, Transaction {
    private $data;
    private $value;
    private $cash;

    public function __construct(array $data) {
        $this->data = $data;
        $this->cash = Cash::find(1);
    }

    public function validate() : void {
        if(is_null($this->data) || empty($this->data) || count($this->data) == 0) {
            throw new ValidationException("No hay dinero que depositar");
        }

        $count = 0;
        foreach ($this->data as $value) {
            if(!array_key_exists("valor", $value)) {
                throw new ValidationException("No se encuentra el valor");
            }

            if(!array_key_exists("cantidad", $value)) {
                throw new ValidationException("No se encuentra la cantidad");
            }

            if($value["cantidad"] == 0) {
                throw new ValidationException("El valor " . $value["valor"] . " tiene una cantidad igual a 0");
            } else {
                $money = Money::where("value", $value["valor"])->first();
                if(is_null($money) || empty($money)) {
                    throw new ValidationException("El valor " . $value["valor"] . " no es válido");
                } else {
                    $this->data[$count]["id_money"] = $money->id;
                }
            }
            $count++;
        }
    }

    public function addMoney() : void {
        $this->value = 0;
        foreach ($this->data as $value) {
            $cashMoney = CashMoney::where([["cash_id", "=", 1], ["money_id", "=", $value["id_money"]]])->first();
            $cashMoney->amount = $cashMoney->amount + intval($value["cantidad"]);
            $this->value = $this->value + (intval($value["cantidad"]) * intval($value["valor"]));
            $cashMoney->save();
        }

        $this->cash->total_money = $this->cash->total_money + $this->value;
        $this->cash->save();
    }

    public function doTransaction(): array {
        try {
            DB::beginTransaction();
            $this->validate();
            $this->addMoney();
            $this->addMovement();
            DB::commit();
            return [];
        } catch(ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addMovement(): void {
        $cashMovement = new CashMovement();
        $cashMovement->cash_id = 1;

        $movementType = MovementType::where("name", "Cargar Base")->first();
        $cashMovement->movement_type_id = $movementType->id;
        $cashMovement->in_value = $this->value;
        $cashMovement->out_value = 0;
        $cashMovement->net_income = $this->value;
        $cashMovement->save();
        
        foreach ($this->data as $value) {
            $movementDetaiil = new MovementDetail();
            $movementDetaiil->cash_movement_id = $cashMovement->id;
            $movementDetaiil->money_id = $value["id_money"];
            $movementDetaiil->amount = $value["cantidad"];
            $movementDetaiil->save();
        }

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