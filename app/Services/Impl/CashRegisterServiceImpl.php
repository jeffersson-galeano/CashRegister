<?php
namespace App\Services\Impl;

use App\Classes\Impl\AddMoneyCash;
use App\Classes\Impl\Pay;
use App\Classes\Impl\Withdraw;
use App\Classes\Transaction;
use App\Exceptions\ValidationException;
use App\Models\Cash;
use App\Models\CashMovement;
use App\Models\Log;
use App\Services\CashRegisterService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Boolean;

class CashRegisterServiceImpl implements CashRegisterService {

    public function cashStatus() : array {
        $cash = Cash::find(1);
        
        $dataResponse = [
            "totalDinero" => $cash->total_money,
            "cantidadEnCaja" => array()
        ];

        foreach($cash->cashMoney as $cashMoney) {
            $cantidadEnCaja = [
                "denominacion" => $cashMoney->money->value,
                "cantidad" => $cashMoney->amount
            ];
            $dataResponse["cantidadEnCaja"][] = $cantidadEnCaja;
        }
        return $dataResponse;
    }

    public function loadCashBase(Request $request): array {
        $dataTransaction = $request->get("dinero");
        if(is_null($dataTransaction)) {
            throw new ValidationException("Se espera que ingrese algún valor en el campo 'dinero'");
        }
        return $this->doTransaction(new AddMoneyCash($dataTransaction));
    }

    public function pay(Request $request): array {
        $dataTransaction = [
            "money" => $request->get("dinero"),
            "totalPay" => $request->get("totalPagar")
        ];
        if(is_null($dataTransaction["money"])) {
            throw new ValidationException("Debe ingresar el dinero con el que va a realizar el pago");
        }

        if(is_null($dataTransaction["totalPay"])) {
            throw new ValidationException("Debe ingresar el total a pagar");
        }
        return $this->doTransaction(new Pay($dataTransaction));
    }

    public function withdraw(): array
    {
        return $this->doTransaction(new Withdraw());
    }

    public function doTransaction(Transaction $transaction) : array {
        date_default_timezone_set("America/Bogota");
        return $transaction->doTransaction();
    }

    public function movementEventLog(): array {
        $movementsResponse = [];
        $cashMovements = CashMovement::where("cash_id", 1)->orderBy("created_at", "DESC")->get();
        foreach($cashMovements as $value) {
            $movementRes = [
                "tipo" => $value->movementType->name,
                "valor_entrada" => $value->in_value,
                "valor_salida" => $value->out_value,
                "valor_entrada_neto" => $value->net_income,
                "fecha" => $value->created_at,
                "detalle" => []
            ];
            foreach($value->movementDetails as $movementDetail) {
                $moveDetRes = [
                    "valor" => $movementDetail->money->value,
                    "cantidad" => $movementDetail->amount
                ];
                $movementRes["detalle"][] = $moveDetRes;
            }
            $movementsResponse[] = $movementRes;
        }
        return $movementsResponse;
    }

    
    public function cashStatusByDate(Request $request): array {
        $logResponse = [];

        $dateReq = $request->get("fecha");
        if(is_null($dateReq)) {
            throw new ValidationException("Debe ingresar la fecha");
        }

        $dtime = DateTime::createFromFormat("Y-m-d G:i", $dateReq);
        if(is_bool($dtime)) {
            throw new ValidationException("No se reconoce el formato de la fecha, debe ingresar la fecha con el siguiente formato 2021-05-30 16:05");
        }

        $logs = Log::where([["cash_id", "=", 1], [DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i")'), "=", $dateReq]])->orderBy("created_at", "DESC")->get();
        foreach($logs as $value) {
            $logRes = [
                "totalDinero" => $value->total_money,
                "fecha" => $value->created_at,
                "cantidadEnCaja" => []
            ];
            foreach($value->logDetails as $logDetail) {
                $logDetRes = [
                    "valor" => $logDetail->cashMoney->money->value,
                    "cantidad" => $logDetail->amount
                ];
                $logRes["cantidadEnCaja"][] = $logDetRes;
            }
            $logResponse[] = $logRes;
        }
        return $logResponse;
    }
}