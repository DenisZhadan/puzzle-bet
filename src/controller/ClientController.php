<?php

namespace Sparksoft\Bet\controller;

use Sparksoft\Bet\dto\BalanceDTO;
use Sparksoft\Bet\dto\ClientDTO;
use Sparksoft\Bet\dto\CurrencyDTO;
use Sparksoft\Bet\route\Route;

class ClientController extends Controller
{
    #[Route('/api/v1/users/', 'GET')]
    public function getClients(): void
    {
        $stmt = $this->pdo->query("SELECT c.id, c.user_name, c.first_name, c.last_name
                                          FROM client c");
        $clients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $clientDTOs = array_map(function ($user) {
            return new ClientDTO($user);
        }, $clients);

        $this->returnResponse($clientDTOs);
    }

    #[Route('/api/v1/user/{client_id}/currency/', 'GET')]
    public function getClientCurrencyList(int $client_id): void
    {
        $stmt = $this->pdo->prepare("SELECT cb.currency
                                            FROM client_balance cb
                                            WHERE cb.client_id = :client_id");
        $stmt->execute(['client_id' => $client_id]);
        $client_currency = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $currencyDTOs = array_map(function ($currency) {
            return new CurrencyDTO($currency);
        }, $client_currency);

        $this->returnResponse($currencyDTOs);
    }

    #[Route('/api/v1/user/{client_id}/balance/', 'GET')]
    public function getClientBalance(int $client_id): void
    {
        $stmt = $this->pdo->prepare("SELECT cb.currency, cb.amount 
                                            FROM client_balance cb 
                                            WHERE cb.client_id = :client_id");
        $stmt->execute(['client_id' => $client_id]);
        $balances = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $balanceDTOs = array_map(function ($balance) {
            return new BalanceDTO($balance);
        }, $balances);

        $this->returnResponse($balanceDTOs);
    }

    #[Route('/api/v1/user/{client_id}/new-balance/', 'PUT')]
    public function updateClientBalance(int $client_id): void
    {
        $input = $this->getJson();
        $balance = new BalanceDTO($input);

        $stmt = $this->pdo->prepare("UPDATE client_balance 
                                            SET amount = :amount 
                                            WHERE client_id = :client_id AND currency = :currency");
        $stmt->execute(['amount' => $balance->amount, 'client_id' => $client_id, 'currency' => $balance->currency]);

        if ($stmt->rowCount() === 1) {
            $response = ['message' => 'Баланс обновлен'];
        } else if ($stmt->rowCount() === 0) {
            $response = ['message' => 'Баланс не обновлен'];
        } else {
            $response = ['message' => 'Баланс обновление затронуло несколько строк'];
        }

        $this->returnResponse($response);
    }
}
