<?php

namespace Sparksoft\Bet\controller;

use Sparksoft\Bet\db\Database;

class Controller
{
    protected Database $db;
    protected \PDO $pdo;
    protected string $http_response_text_400 = 'HTTP/1.0 400 Bad Request';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    protected function getJson(): array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            $response = ['success' => false, 'message' => 'Нет входных данных.'];
            $this->returnResponse($response, $this->http_response_text_400);
        }

        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = ['success' => false, 'message' => 'Ошибка декодирования JSON.'];
            $this->returnResponse($response, $this->http_response_text_400);
        }
        return $data;
    }

    public function returnResponse(array $response, string $header_response_text = ''): void
    {
        if (!empty($header_response_text)) {
            header($header_response_text);
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}
