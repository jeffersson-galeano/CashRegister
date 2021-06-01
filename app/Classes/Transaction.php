<?php

namespace App\Classes;

interface Transaction {
    public function doTransaction(): array;
    public function addMovement(): void;
}