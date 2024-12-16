<?php

namespace Sparksoft\Bet\dto;

class DTO
{
    public function toArray()
    {
        return get_object_vars($this);
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
