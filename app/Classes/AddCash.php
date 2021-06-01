<?php

namespace App\Classes;

interface AddCash {
    public function validate() : void;
    public function addMoney() : void;
}