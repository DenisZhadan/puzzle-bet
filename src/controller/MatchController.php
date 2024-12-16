<?php

namespace Sparksoft\Bet\controller;

use Sparksoft\Bet\dto\MatchDTO;
use Sparksoft\Bet\route\Route;

class MatchController extends Controller
{
    #[Route('/api/v1/matches/', 'GET')]
    public function getMatches(): void
    {
        $stmt = $this->pdo->query("SELECT m.*
                                          FROM match m");
        $matches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $matchDTOs = array_map(function ($match) {
            return new MatchDTO($match);
        }, $matches);

        $this->returnResponse($matchDTOs);
    }
}
