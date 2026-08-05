<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Session;
use App\Services\MercureService;

class SessionController
{
    public function store(Request $request): void
    {
        $quizId = (int) $request->input('quiz_id');
        if (!Quiz::find($quizId)) {
            Response::error('Quiz introuvable', 404);
            return;
        }

        $session = Session::create($quizId);
        Response::json($session, 201);
    }

    public function showByPin(Request $request): void
    {
        $session = Session::findByPin((string) $request->param('pin'));
        if (!$session) {
            Response::error('Session introuvable', 404);
            return;
        }

        $quiz = Quiz::find((int) $session['quiz_id']);
        Response::json([
            'session' => $session,
            'quiz_nom' => $quiz['nom'] ?? null,
        ]);
    }

    public function show(Request $request): void
    {
        $session = Session::find((int) $request->param('id'));
        if (!$session) {
            Response::error('Session introuvable', 404);
            return;
        }
        Response::json($session);
    }

    public function startQuestion(Request $request): void
    {
        $sessionId = (int) $request->param('id');
        $session = Session::find($sessionId);
        if (!$session) {
            Response::error('Session introuvable', 404);
            return;
        }

        $questionId = (int) $request->input('question_id');
        $question = Question::find($questionId);
        if (!$question || (int) $question['quiz_id'] !== (int) $session['quiz_id']) {
            Response::error('Question invalide pour ce quiz', 422);
            return;
        }

        $session = Session::startQuestion($sessionId, $questionId);

        MercureService::publish($sessionId, 'question-started', [
            'question' => Question::publicView($question),
            'started_at' => str_replace(' ', 'T', $session['question_started_at']) . 'Z',
        ]);

        Response::json($session);
    }

    public function endQuestion(Request $request): void
    {
        $sessionId = (int) $request->param('id');
        $session = Session::find($sessionId);
        if (!$session) {
            Response::error('Session introuvable', 404);
            return;
        }

        $leaderboard = Session::leaderboard($sessionId);
        $questionId = (int) $session['question_courante_id'];
        $question = $questionId ? Question::find($questionId) : null;

        MercureService::publish($sessionId, 'question-ended', [
            'question_id' => $questionId,
            'reponse' => $question['reponse_affichee'] ?? null,
            'leaderboard' => $leaderboard,
        ]);

        Response::json(['leaderboard' => $leaderboard]);
    }

    public function finish(Request $request): void
    {
        $sessionId = (int) $request->param('id');
        $session = Session::find($sessionId);
        if (!$session) {
            Response::error('Session introuvable', 404);
            return;
        }

        $session = Session::finish($sessionId);
        $leaderboard = Session::leaderboard($sessionId);

        MercureService::publish($sessionId, 'session-finished', [
            'leaderboard' => $leaderboard,
        ]);

        Response::json($session);
    }

    public function leaderboard(Request $request): void
    {
        $sessionId = (int) $request->param('id');
        if (!Session::find($sessionId)) {
            Response::error('Session introuvable', 404);
            return;
        }

        Response::json(Session::leaderboard($sessionId));
    }
}
