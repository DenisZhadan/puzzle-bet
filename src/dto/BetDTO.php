<?php

namespace Sparksoft\Bet\dto;

use Sparksoft\Bet\dto\DTO;

class BetDTO extends DTO
{
    public int $client_Id;
    public int $match_id;
    public string $expected_result;
    public float $amount;
    public string $currency;
    public float $coefficient;

    public function __construct($data)
    {
        $this->client_Id = intval($data['client_id']);
        $this->match_id = intval($data['match_id']);
        $this->expected_result = $data['expected_result'];
        $this->amount = floatval($data['amount']);
        $this->currency = $data['currency'];
        $this->coefficient = floatval($data['coefficient']);
    }
}
