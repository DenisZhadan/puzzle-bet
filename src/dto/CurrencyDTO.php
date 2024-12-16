<?php

namespace Sparksoft\Bet\dto;

class CurrencyDTO
{
    public string $currency;

    public function __construct($data)
    {
        $this->currency = $data['currency'];
    }
}
