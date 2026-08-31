import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { QRCodeSVG } from 'qrcode.react';
import { api, mediaUrl } from '../api/client';
import { useMercure } from '../hooks/useMercure';
import Header from '../components/Header';

const JOIN_BASE_URL = import.meta.env.VITE_FRONTEND_URL || window.location.origin;

export default function Host() {
  const { quizId } = useParams();
  const [quiz, setQuiz] = useState(null);
  const [session, setSession] = useState(null);
  const [error, setError] = useState(null);
  const [players, setPlayers] = useState([]);
  const [currentIndex, setCurrentIndex] = useState(-1);
  const [phase, setPhase] = useState('attente'); // attente | question | revealed | termine
  const [answeredCount, setAnsweredCount] = useState(0);
  const [reveal, setReveal] = useState(null);

  useEffect(() => {
    (async () => {
      try {
        const quizData = await api.get(`/api/quizzes/${quizId}`);
        setQuiz(quizData);
        const sessionData = await api.post('/api/sessions', { quiz_id: Number(quizId) });
        setSession(sessionData);
      } catch (err) {
        setError(err.message);
      }
    })();
  }, [quizId]);

  useEffect(() => {
    if (!session || phase !== 'attente') return undefined;
    const poll = async () => {
      try {
        const board = await api.get(`/api/sessions/${session.id}/leaderboard`);
        setPlayers(board);
      } catch (err) {
        // ignore transient polling errors
      }
    };
    poll();
    const interval = setInterval(poll, 2000);
    return () => clearInterval(interval);
  }, [session, phase]);

  useMercure(session?.id, (data) => {
    if (data.event === 'player-answered') {
      setAnsweredCount((c) => c + 1);
    }
  });

  async function startNextQuestion() {
    const nextIndex = currentIndex + 1;
    const question = quiz.questions[nextIndex];
    if (!question) return;

    await api.post(`/api/sessions/${session.id}/start-question`, { question_id: question.id });
    setCurrentIndex(nextIndex);
    setAnsweredCount(0);
    setReveal(null);
    setPhase('question');
  }

  async function revealAnswer() {
    const result = await api.post(`/api/sessions/${session.id}/end-question`, {});
    setReveal(result.leaderboard);
    setPhase('revealed');
  }

  async function finishQuiz() {
    const updated = await api.post(`/api/sessions/${session.id}/finish`, {});
    setReveal(await api.get(`/api/sessions/${session.id}/leaderboard`));
    setPhase('termine');
  }

  if (error) return <div className="page page-center"><p className="error">{error}</p></div>;
  if (!quiz || !session) return <div className="page page-center"><p>Préparation de la session...</p></div>;

  const joinUrl = `${JOIN_BASE_URL}/join/${session.code_pin}`;
  const currentQuestion = quiz.questions[currentIndex];
  const isLastQuestion = currentIndex >= quiz.questions.length - 1;

  return (
    <div className="page">
      <Header />
      <h1>{quiz.nom} — Écran hôte</h1>

      {phase === 'attente' && (
        <div className="host-waiting">
          <div className="qr-block">
            <QRCodeSVG value={joinUrl} size={220} />
            <p className="pin">Code PIN : <strong>{session.code_pin}</strong></p>
            <p className="join-url">{joinUrl}</p>
          </div>
          <div className="players-block">
            <h2>Joueurs connectés ({players.length})</h2>
            <ul className="players-list">
              {players.map((p) => (
                <li key={p.id}>{p.numero} {p.pseudo ? `— ${p.pseudo}` : ''}</li>
              ))}
            </ul>
          </div>
          <button onClick={startNextQuestion} disabled={players.length === 0}>
            Démarrer la question 1
          </button>
        </div>
      )}

      {phase === 'question' && currentQuestion && (
        <div className="card question-card">
          <h2>Question {currentIndex + 1} / {quiz.questions.length}</h2>
          {currentQuestion.media_type === 'image' && (
            <img key={currentQuestion.id} src={mediaUrl(currentQuestion.media_url)} alt="Indice" className="media" />
          )}
          {currentQuestion.media_type === 'video' && (
            <video key={currentQuestion.id} src={mediaUrl(currentQuestion.media_url)} className="media" autoPlay muted loop />
          )}
          {currentQuestion.media_type === 'audio' && (
            <audio key={currentQuestion.id} src={mediaUrl(currentQuestion.media_url)} autoPlay controls className="media-audio" />
          )}
          <p className="texte">{currentQuestion.texte}</p>
          <p>{answeredCount} réponse(s) reçue(s)</p>
          <button onClick={revealAnswer}>Révéler la réponse</button>
        </div>
      )}

      {phase === 'revealed' && (
        <div className="card">
          <h2>Réponse : {currentQuestion.reponse_affichee}</h2>
          <Leaderboard entries={reveal} />
          {isLastQuestion ? (
            <button onClick={finishQuiz}>Terminer le quiz</button>
          ) : (
            <button onClick={startNextQuestion}>Question suivante</button>
          )}
        </div>
      )}

      {phase === 'termine' && (
        <div className="card">
          <h2>Classement final</h2>
          <Leaderboard entries={reveal} />
        </div>
      )}
    </div>
  );
}

function Leaderboard({ entries }) {
  if (!entries) return null;
  return (
    <ol className="leaderboard">
      {entries.map((entry) => (
        <li key={entry.id}>
          <span className="numero">{entry.numero}</span>
          <span className="pseudo">{entry.pseudo || ''}</span>
          <span className="score">{entry.score} pts</span>
        </li>
      ))}
    </ol>
  );
}
