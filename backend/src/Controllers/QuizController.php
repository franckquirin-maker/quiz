<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Quiz;

class QuizController
{
    public function index(Request $request): void
    {
        Response::json(Quiz::all());
    }

    public function show(Request $request): void
    {
        $quiz = Quiz::withQuestions((int) $request->param('id'));
        if (!$quiz) {
            Response::error('Quiz introuvable', 404);
            return;
        }
        Response::json($quiz);
    }

    public function store(Request $request): void
    {
        $nom = trim((string) $request->input('nom', ''));
        if ($nom === '') {
            Response::error('Le nom du quiz est requis', 422);
            return;
        }

        $quiz = Quiz::create($nom, $request->input('description'), $request->input('statut', 'brouillon'));
        Response::json($quiz, 201);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        if (!Quiz::find($id)) {
            Response::error('Quiz introuvable', 404);
            return;
        }

        $nom = trim((string) $request->input('nom', ''));
        if ($nom === '') {
            Response::error('Le nom du quiz est requis', 422);
            return;
        }

        $quiz = Quiz::update($id, $nom, $request->input('description'), $request->input('statut', 'brouillon'));
        Response::json($quiz);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        if (!Quiz::find($id)) {
            Response::error('Quiz introuvable', 404);
            return;
        }

        Quiz::delete($id);
        Response::noContent();
    }
}
