<?php

namespace Sparksoft\Bet\dto;

class BalanceDTO extends DTO
{
    public string $currency;
    public float $amount;

    public function __construct($data)
    {
        $this->currency = $data['currency'];
        $this->amount = $data['amount'];
    }
}
