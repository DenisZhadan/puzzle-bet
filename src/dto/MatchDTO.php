<?php

namespace Sparksoft\Bet\dto;

class MatchDTO extends DTO
{
    public int $id;
    public string $team1;
    public string $team2;
    public float $draw_percentage;
    public float $loss_percentage;
    public float $winning_percentage;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->team1 = $data['team1'];
        $this->team2 = $data['team2'];
        $this->draw_percentage = $data['draw_percentage'] ?? random_int(101, 4000) / 100; //1.01 .. 40.00.
        $this->loss_percentage = $data['loss_percentage'] ?? random_int(101, 4000) / 100;
        $this->winning_percentage = $data['winning_percentage'] ?? random_int(101, 4000) / 100;
    }
}
