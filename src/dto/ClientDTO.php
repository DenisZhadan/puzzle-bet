<?php

namespace Sparksoft\Bet\dto;

class ClientDTO extends DTO
{
    public int $id;
    public string $user_name;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $gender;
    public ?string $birth_date;
    public ?string $street;
    public ?string $city;
    public ?string $state;
    public ?string $postal_code;
    public ?string $country;
    public ?string $status;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->user_name = $data['user_name'];
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name = $data['last_name'] ?? '';
        $this->gender = $data['gender'] ?? null;
        $this->birth_date = $data['birth_date'] ?? null;
        $this->street = $data['street'] ?? '';
        $this->city = $data['city'] ?? '';
        $this->state = $data['state'] ?? '';
        $this->postal_code = $data['postal_code'] ?? '';
        $this->country = $data['country'] ?? '';
        $this->status = $data['status'] ?? null;
    }
}
