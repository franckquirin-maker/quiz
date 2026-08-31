<?php

use App\Core\Router;
use App\Controllers\QuizController;
use App\Controllers\QuestionController;
use App\Controllers\SessionController;
use App\Controllers\PlayerController;
use App\Controllers\AnswerController;
use App\Controllers\MediaController;

$router = new Router();

$quizController = new QuizController();
$router->get('/api/quizzes', [$quizController, 'index']);
$router->post('/api/quizzes', [$quizController, 'store']);
$router->get('/api/quizzes/{id}', [$quizController, 'show']);
$router->put('/api/quizzes/{id}', [$quizController, 'update']);
$router->delete('/api/quizzes/{id}', [$quizController, 'destroy']);

$questionController = new QuestionController();
$router->post('/api/quizzes/{quizId}/questions', [$questionController, 'store']);
$router->put('/api/questions/{id}', [$questionController, 'update']);
$router->delete('/api/questions/{id}', [$questionController, 'destroy']);

$sessionController = new SessionController();
$router->post('/api/sessions', [$sessionController, 'store']);
$router->get('/api/sessions/{id}', [$sessionController, 'show']);
$router->get('/api/sessions/pin/{pin}', [$sessionController, 'showByPin']);
$router->post('/api/sessions/{id}/start-question', [$sessionController, 'startQuestion']);
$router->post('/api/sessions/{id}/end-question', [$sessionController, 'endQuestion']);
$router->post('/api/sessions/{id}/finish', [$sessionController, 'finish']);
$router->get('/api/sessions/{id}/leaderboard', [$sessionController, 'leaderboard']);

$playerController = new PlayerController();
$router->post('/api/players/join', [$playerController, 'join']);
$router->get('/api/players/{id}', [$playerController, 'leaderboardEntryForPlayer']);

$answerController = new AnswerController();
$router->post('/api/answers', [$answerController, 'store']);

$mediaController = new MediaController();
$router->post('/api/media/upload', [$mediaController, 'upload']);

return $router;
