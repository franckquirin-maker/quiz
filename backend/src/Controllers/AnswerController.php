<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Session;
use App\Models\Player;
use App\Models\Question;
use App\Models\Answer;
use App\Services\Normalizer;
use App\Services\ScoringService;
use App\Services\MercureService;
use DateTime;

class AnswerController
{
    public function store(Request $request): void
    {
        $playerId = (int) $request->input('player_id');
        $questionId = (int) $request->input('question_id');
        $reponseBrute = (string) $request->input('reponse', '');

        $player = Player::find($playerId);
        if (!$player) {
            Response::error('Joueur introuvable', 404);
            return;
        }

        $session = Session::find((int) $player['session_id']);
        if (!$session || (int) $session['question_courante_id'] !== $questionId) {
            Response::error("Cette question n'est plus active pour cette session", 409);
            return;
        }

        if (Answer::findForPlayerAndQuestion($playerId, $questionId)) {
            Response::error('Réponse déjà enregistrée pour cette question', 409);
            return;
        }

        $question = Question::find($questionId);
        if (!$question) {
            Response::error('Question introuvable', 404);
            return;
        }

        $startedAt = new DateTime($session['question_started_at']);
        $tempsEcouleMs = max(0, (int) round((microtime(true) - $startedAt->getTimestamp()) * 1000));

        $reponseNormalisee = Normalizer::normalize($reponseBrute);
        $correcte = $reponseNormalisee !== '' && in_array($reponseNormalisee, Question::acceptedAnswers($question), true);

        $points = ScoringService::compute(
            $correcte,
            (int) $question['points_max'],
            (int) $question['duree_secondes'],
            $tempsEcouleMs
        );

        $answer = Answer::record($playerId, $questionId, $reponseBrute, $reponseNormalisee, $tempsEcouleMs, $points, $correcte);

        MercureService::publish((int) $session['id'], 'player-answered', [
            'question_id' => $questionId,
            'player_id' => $playerId,
        ]);

        Response::json($answer, 201);
    }
}
