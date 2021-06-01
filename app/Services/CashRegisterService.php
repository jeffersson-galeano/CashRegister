<?php 

namespace App\Services;

use App\Classes\Transaction;
use Illuminate\Http\Request;

interface CashRegisterService {
    public function cashStatus(): array;
    public function loadCashBase(Request $request):  array;
    public function pay(Request $request):  array;
    public function withdraw():  array;
    public function doTransaction(Transaction $transaction): array;
    public function movementEventLog(): array;
    public function cashStatusByDate(Request $request): array;
}