<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Session;
use App\Models\Player;

class PlayerController
{
    public function join(Request $request): void
    {
        $pin = (string) $request->input('code_pin');
        $session = Session::findByPin($pin);
        if (!$session) {
            Response::error('Code PIN invalide', 404);
            return;
        }

        $pseudo = $request->input('pseudo');
        $player = Player::join((int) $session['id'], $pseudo ? trim($pseudo) : null);

        Response::json([
            'player' => $player,
            'session' => $session,
        ], 201);
    }

    public function leaderboardEntryForPlayer(Request $request): void
    {
        $player = Player::find((int) $request->param('id'));
        if (!$player) {
            Response::error('Joueur introuvable', 404);
            return;
        }

        Response::json($player);
    }
}
