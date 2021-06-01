<?php

use App\Models\Cash;
use App\Models\CashMoney;
use App\Models\Money;
use App\Models\MovementType;
use Illuminate\Database\Seeder;

class InitialPoblationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cash = new Cash();
        $cash->total_money = 0;
        $cash->save();

        $money = new Money();
        $money->value = 100000;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 50000;
        $money->save();
        $moneyAll[] = $money->id;
        
        $money = new Money();
        $money->value = 20000;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 10000;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 5000;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 1000;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 500;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 200;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 100;
        $money->save();
        $moneyAll[] = $money->id;

        $money = new Money();
        $money->value = 50;
        $money->save();
        $moneyAll[] = $money->id;

        $priority = 10;
        foreach ($moneyAll as $value) {
            $cashMoney = new CashMoney();
            $cashMoney->cash_id = $cash->id;
            $cashMoney->money_id = $value;
            $cashMoney->priority = $priority;
            $cashMoney->amount = 0;
            $cashMoney->save();
            $priority--;
        }

        $movementType = new MovementType();
        $movementType->name = "Vaciar Caja";
        $movementType->save();

        $movementType = new MovementType();
        $movementType->name = "Cargar Base";
        $movementType->save();

        $movementType = new MovementType();
        $movementType->name = "Realizar Pago";
        $movementType->save();
    }
}
