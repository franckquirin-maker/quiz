<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Quiz;
use App\Models\Question;

class QuestionController
{
    public function store(Request $request): void
    {
        $quizId = (int) $request->param('quizId');
        if (!Quiz::find($quizId)) {
            Response::error('Quiz introuvable', 404);
            return;
        }

        $error = $this->validate($request);
        if ($error) {
            Response::error($error, 422);
            return;
        }

        $question = Question::create([
            'quiz_id' => $quizId,
            'media_url' => $request->input('media_url'),
            'media_type' => $request->input('media_type'),
            'texte' => $request->input('texte'),
            'reponse' => $request->input('reponse'),
            'alternatives' => (array) $request->input('alternatives', []),
            'points_max' => (int) $request->input('points_max'),
            'duree_secondes' => (int) $request->input('duree_secondes'),
            'ordre' => (int) $request->input('ordre', 0),
        ]);

        Response::json($question, 201);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        if (!Question::find($id)) {
            Response::error('Question introuvable', 404);
            return;
        }

        $error = $this->validate($request);
        if ($error) {
            Response::error($error, 422);
            return;
        }

        $question = Question::update($id, [
            'media_url' => $request->input('media_url'),
            'media_type' => $request->input('media_type'),
            'texte' => $request->input('texte'),
            'reponse' => $request->input('reponse'),
            'alternatives' => (array) $request->input('alternatives', []),
            'points_max' => (int) $request->input('points_max'),
            'duree_secondes' => (int) $request->input('duree_secondes'),
            'ordre' => (int) $request->input('ordre', 0),
        ]);

        Response::json($question);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        if (!Question::find($id)) {
            Response::error('Question introuvable', 404);
            return;
        }

        Question::delete($id);
        Response::noContent();
    }

    private function validate(Request $request): ?string
    {
        if (!in_array($request->input('media_type'), ['image', 'video', 'audio'], true)) {
            return "media_type doit être 'image', 'video' ou 'audio'";
        }
        if (trim((string) $request->input('reponse', '')) === '') {
            return 'La réponse attendue est requise';
        }
        if ((int) $request->input('points_max', 0) <= 0) {
            return 'points_max doit être positif';
        }
        if ((int) $request->input('duree_secondes', 0) <= 0) {
            return 'duree_secondes doit être positif';
        }
        return null;
    }
}
