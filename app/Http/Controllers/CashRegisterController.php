<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Services\Impl\CashRegisterServiceImpl;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    private $cashRegisterService;
    public function __construct(CashRegisterServiceImpl  $cashRegisterService){
        $this->cashRegisterService = $cashRegisterService;
    }


    public function cashStatus() {
        try {
            $dataResponse = $this->cashRegisterService->cashStatus();
            return response($dataResponse, 200);
        } catch (\Exception $e) {
            return response(json_encode(["message" => $e->getMessage()]), 500);
        }
    }

    public function loadCashBase(Request $request) {
        try {
            $dataResponse = $this->cashRegisterService->loadCashBase($request);
            return response($dataResponse, 200);
        } catch (\App\Exceptions\ValidationException $e) {
            return response(json_encode(["message" => $e->getMessage()]), 400);
        } catch (\Exception $e) {
            return response(json_encode(["message" => $e->getMessage()]), 500);
        }
    }

    public function pay(Request $request) {
        try {
            $dataResponse = $this->cashRegisterService->pay($request);
            return response($dataResponse, 200);
        } catch (\App\Exceptions\NotEnoughMoneyExeption $e) {
            return response(json_encode(["message" => $e->getMessage()]), 202);
        } catch (\App\Exceptions\ValidationException $e) {
            return response(json_encode(["message" => $e->getMessage()]), 400);
        } catch (\Exception $e) {
            return response(json_encode(["message" => $e->getMessage()]), 500);
        }
    }

    public function withdraw() {
        try {
            $dataResponse = $this->cashRegisterService->withdraw();
            return response($dataResponse, 200);
        } catch (\Exception $e) {
            return response(json_encode(["message" => $e->getMessage()]), 500);
        }
    }

    public function movementEventLog() {
        try {
            $dataResponse = $this->cashRegisterService->movementEventLog();
            return response($dataResponse, 200);
        } catch (\Exception $e) {
            return response(json_encode(["message" => $e->getMessage()]), 500);
        }
    }

    public function cashStatusByDate(Request $request) {
        try {
            $dataResponse = $this->cashRegisterService->cashStatusByDate($request);
            return response($dataResponse, 200);
        } catch (ValidationException $e) {
            return response(json_encode(["message" => $e->getMessage()]), 400);
        } catch (\Exception $e) {
            return response(json_encode(["message" => $e->getMessage()]), 500);
        }
    }
}
