<?php

namespace Sparksoft\Bet\controller;

use Sparksoft\Bet\dto\BetDTO;
use Sparksoft\Bet\route\Route;

class BetController extends Controller
{
    #[Route('/api/v1/bet/', 'POST')]
    public function setBet(): void
    {
        try {
            $this->db->beginTransaction();

            $input = $this->getJson();
            $bet = new BetDTO($input);

            if ($bet->amount < 1 || $bet->amount > 500) {
                $response = ['success' => false, 'message' => 'Сумма ставки должна быть в пределах от 1 до 500 единиц.'];
                $this->db->rollBack();
                $this->returnResponse($response);
            }

            $stmt = $this->pdo->prepare("SELECT amount 
                                                FROM client_balance 
                                                WHERE client_id = :client_id AND currency = :currency");
            $stmt->execute(['client_id' => $bet->client_Id, 'currency' => $bet->currency]);
            $balance = $stmt->fetchColumn();

            if ($balance === false || $balance < $bet->amount) {
                $response = ['success' => false, 'message' => 'Недостаточно средств для совершения ставки.'];
                $this->db->rollBack();
            } else {
                $stmt = $this->pdo->prepare("UPDATE client_balance 
                                                    SET amount = amount - :amount 
                                                    WHERE client_id = :client_id AND currency = :currency");
                $stmt->execute(['amount' => $bet->amount, 'client_id' => $bet->client_Id, 'currency' => $bet->currency]);

                $stmt = $this->pdo->prepare("INSERT INTO bet_list (client_id, match_id, expected_result, amount, currency, coefficient) 
                                                    VALUES (:client_id, :match_id, :expected_result, :amount, :currency, :coefficient)");
                $stmt->execute([
                    'client_id' => $bet->client_Id,
                    'match_id' => $bet->match_id,
                    'expected_result' => $bet->expected_result,
                    'amount' => $bet->amount,
                    'currency' => $bet->currency,
                    'coefficient' => $bet->coefficient
                ]);

                $this->db->commit();
                $response = ['success' => true, 'message' => 'Ставка успешно сделана!'];
            }
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            $response = ['success' => false, 'message' => 'Произошла ошибка при совершении ставки.'];
        }

        $this->returnResponse($response);
    }

    #[Route('/api/v1/bet_result/{match_id}/', 'PUT')]
    public function updateBetResult(int $match_id): void
    {
        try {
            $this->db->beginTransaction();

            $data = $this->getJson();
            $actual_result = $data['actual_result'] ?? null;

            if (!$match_id || !$actual_result) {
                $response = ['success' => false, 'message' => 'Отсутствуют необходимые данные.'];
                $this->returnResponse($response);
                return;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM update_bet_result(:match_id, :actual_result)");
            $stmt->execute(['match_id' => $match_id, 'actual_result' => $actual_result]);
            $result = $stmt->fetch();
            $this->db->commit();

            $response = [
                'success' => true,
                'total_bets' => $result['total_bets'],
                'total_winnings' => $result['total_winnings'],
                'total_losses' => $result['total_losses'],
                'message' => "Обновлено ставок: {$result['total_bets']}\nСумма выигрыша: {$result['total_winnings']}\nСумма проигрыша: {$result['total_losses']}"
            ];
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            $response = ['success' => false, 'message' => 'Произошла ошибка при обновлении результатов ставок.'];
        }

        $this->returnResponse($response);
    }
}
