<?php

namespace App\Classes\Impl;

use App\Classes\AddCash;
use App\Classes\MoneyBack;
use App\Classes\Transaction;
use App\Exceptions\NotEnoughMoneyExeption;
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

class Pay implements AddCash, Transaction, MoneyBack {

    private $dataMoney;
    private $totalPay;
    private $value;
    private $withDrawValue;
    private $cash;

    public function __construct(array $data) {
        $this->dataMoney = $data["money"];
        $this->totalPay = $data["totalPay"];
        $this->cash = Cash::find(1);
    }

    public function validate() : void {
        if(is_null($this->dataMoney) || empty($this->dataMoney) || count($this->dataMoney) == 0) {
            throw new ValidationException("No hay dinero que depositar");
        }

        $count = 0;
        foreach ($this->dataMoney as $value) {
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
                    $this->dataMoney[$count]["id_money"] = $money->id;
                }
            }
            $count++;
        }
    }

    public function addMoney() : void {
        $this->value = 0;
        foreach ($this->dataMoney as $value) {
            $cashMoney = CashMoney::where([["cash_id", "=", 1], ["money_id", "=", $value["id_money"]]])->first();
            $cashMoney->amount = $cashMoney->amount + intval($value["cantidad"]);
            $this->value = $this->value + (intval($value["cantidad"]) * intval($value["valor"]));
            $cashMoney->save();
        }

        $this->withDrawValue = $this->value - $this->totalPay;

        if($this->withDrawValue < 0) {
            throw new ValidationException("El valor que está tratando de pagar (" . $this->totalPay . ") es mayor que el valor disponible para pagar (" . $this->value . ")");
        }

        $this->cash->total_money = $this->cash->total_money + $this->value;
        $this->cash->save();
    }

    public function moneyBack(): array {
        $moneyBack = [];
        if($this->withDrawValue > 0) {
            $withDrawAux = $this->withDrawValue;
            $cashMoneyList = CashMoney::where([["amount", ">", 0], ["cash_id", "=", $this->cash->id]])->orderBy("priority", "DESC")->get();
            foreach($cashMoneyList as $value) {
                if($value->money->value <= $withDrawAux) {
                    $money = [];
                    $amountNeed = floor($withDrawAux / $value->money->value);
                    if($value->amount >= $amountNeed) {
                        $value->amount = $value->amount - $amountNeed;
                        $withDrawAux = $withDrawAux % $value->money->value;
                        $money["valor"] = $value->money->value;
                        $money["cantidad"] = $amountNeed;
                    } else {
                        $withDrawAux = $withDrawAux - ($value->money->value * $value->amount);
                        $money["valor"] = $value->money->value;
                        $money["cantidad"] = $value->amount;
                        $value->amount = 0;
                    }
                    $value->save();
                    $moneyBack[] = $money;
                }
            }

            if($withDrawAux > 0) {
                throw new NotEnoughMoneyExeption("Lo sentimos, no se tiene el suficiente efectivo para dar el dinero exedente, por lo mismo se cancela la transacción");
            }
        }

        $this->cash->total_money = $this->cash->total_money - $this->withDrawValue;
        $this->cash->save();
        
        return $moneyBack;
    }

    public function doTransaction(): array {
        try {
            DB::beginTransaction();
            $this->validate();
            $this->addMoney();
            $moneyBack = $this->moneyBack();
            $this->addMovement();
            DB::commit();
            return $moneyBack;
        } catch (NotEnoughMoneyExeption $e) {
            DB::rollBack();
            throw $e;
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addMovement(): void {
        $cashMovement = new CashMovement();
        $cashMovement->cash_id = $this->cash->id;

        $movementType = MovementType::where("name", "Realizar Pago")->first();
        $cashMovement->movement_type_id = $movementType->id;
        $cashMovement->in_value = $this->value;
        $cashMovement->out_value = $this->withDrawValue;
        $cashMovement->net_income = $this->totalPay;
        $cashMovement->save();
        
        foreach ($this->dataMoney as $value) {
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