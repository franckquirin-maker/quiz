import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';
import Header from '../components/Header';

export default function AdminQuizzes() {
  const [quizzes, setQuizzes] = useState([]);
  const [nom, setNom] = useState('');
  const [error, setError] = useState(null);

  function load() {
    api.get('/api/quizzes').then(setQuizzes).catch((e) => setError(e.message));
  }

  useEffect(load, []);

  async function handleCreate(e) {
    e.preventDefault();
    try {
      await api.post('/api/quizzes', { nom });
      setNom('');
      load();
    } catch (err) {
      setError(err.message);
    }
  }

  async function handleDelete(id) {
    if (!window.confirm('Supprimer ce quiz et toutes ses questions ?')) return;
    await api.del(`/api/quizzes/${id}`);
    load();
  }

  return (
    <div className="page">
      <Header />
      <h1>Administration des quiz</h1>
      {error && <p className="error">{error}</p>}

      <form onSubmit={handleCreate} className="card inline-form">
        <input
          value={nom}
          onChange={(e) => setNom(e.target.value)}
          placeholder="Nom du nouveau quiz"
          required
        />
        <button type="submit">Créer</button>
      </form>

      <ul className="admin-quiz-list">
        {quizzes.map((quiz) => (
          <li key={quiz.id} className="card">
            <div>
              <strong>{quiz.nom}</strong>
              <span className={`badge badge-${quiz.statut}`}>{quiz.statut}</span>
            </div>
            <div className="actions">
              <Link to={`/admin/quizzes/${quiz.id}`}>Éditer</Link>
              <Link to={`/host/${quiz.id}`}>Lancer</Link>
              <button onClick={() => handleDelete(quiz.id)}>Supprimer</button>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}
