import { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import { api } from '../api/client';
import { useMercure } from '../hooks/useMercure';

export default function Play() {
  const { playerId } = useParams();
  const [player, setPlayer] = useState(null);
  const [error, setError] = useState(null);
  const [question, setQuestion] = useState(null);
  const [startedAt, setStartedAt] = useState(null);
  const [remaining, setRemaining] = useState(0);
  const [reponse, setReponse] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [result, setResult] = useState(null);
  const [leaderboard, setLeaderboard] = useState(null);
  const [finished, setFinished] = useState(false);

  useEffect(() => {
    api.get(`/api/players/${playerId}`).then(setPlayer).catch((e) => setError(e.message));
  }, [playerId]);

  useMercure(player?.session_id, (data) => {
    if (data.event === 'question-started') {
      setQuestion(data.question);
      setStartedAt(new Date(data.started_at).getTime());
      setReponse('');
      setSubmitted(false);
      setResult(null);
    } else if (data.event === 'question-ended') {
      setResult({ reponse: data.reponse });
      setLeaderboard(data.leaderboard);
    } else if (data.event === 'session-finished') {
      setFinished(true);
      setLeaderboard(data.leaderboard);
    }
  });

  useEffect(() => {
    if (!question || !startedAt) return undefined;

    const tick = () => {
      const elapsed = (Date.now() - startedAt) / 1000;
      setRemaining(Math.max(0, Math.ceil(question.duree_secondes - elapsed)));
    };

    tick();
    const interval = setInterval(tick, 250);
    return () => clearInterval(interval);
  }, [question, startedAt]);

  const monScore = useMemo(() => {
    if (!leaderboard || !player) return null;
    return leaderboard.find((p) => String(p.id) === String(player.id));
  }, [leaderboard, player]);

  async function handleSubmit(e) {
    e.preventDefault();
    if (submitted || remaining <= 0) return;
    setSubmitted(true);
    try {
      await api.post('/api/answers', {
        player_id: player.id,
        question_id: question.id,
        reponse,
      });
    } catch (err) {
      setError(err.message);
    }
  }

  if (error) return <div className="page page-center"><p className="error">{error}</p></div>;
  if (!player) return <div className="page page-center"><p>Connexion...</p></div>;

  return (
    <div className="page page-center">
      <div className="player-badge">Joueur {player.numero}</div>

      {finished && leaderboard && (
        <div className="card">
          <h2>Partie terminée !</h2>
          <Leaderboard entries={leaderboard} highlightId={player.id} />
        </div>
      )}

      {!finished && !question && (
        <div className="card"><p>En attente du début de la prochaine question...</p></div>
      )}

      {!finished && question && !result && (
        <div className="card question-card">
          <div className="timer">{remaining}s</div>
          {question.media_type === 'image' ? (
            <img src={question.media_url} alt="Indice" className="media" />
          ) : (
            <video src={question.media_url} className="media" autoPlay muted loop />
          )}
          <p className="texte">{question.texte}</p>
          <form onSubmit={handleSubmit}>
            <input
              value={reponse}
              onChange={(e) => setReponse(e.target.value)}
              placeholder="Nom de la série..."
              disabled={submitted || remaining <= 0}
              autoFocus
            />
            <button type="submit" disabled={submitted || remaining <= 0 || !reponse.trim()}>
              {submitted ? 'Réponse envoyée' : 'Valider'}
            </button>
          </form>
        </div>
      )}

      {!finished && result && (
        <div className="card">
          <h2>La réponse était : {result.reponse}</h2>
          {monScore && <p>Votre score total : {monScore.score} pts</p>}
          {leaderboard && <Leaderboard entries={leaderboard} highlightId={player.id} />}
        </div>
      )}
    </div>
  );
}

function Leaderboard({ entries, highlightId }) {
  return (
    <ol className="leaderboard">
      {entries.map((entry) => (
        <li key={entry.id} className={String(entry.id) === String(highlightId) ? 'me' : ''}>
          <span className="numero">{entry.numero}</span>
          <span className="pseudo">{entry.pseudo || ''}</span>
          <span className="score">{entry.score} pts</span>
        </li>
      ))}
    </ol>
  );
}
