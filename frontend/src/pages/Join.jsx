import { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../api/client';
import Header from '../components/Header';

export default function Join() {
  const { pin: pinFromUrl } = useParams();
  const navigate = useNavigate();
  const [pin, setPin] = useState(pinFromUrl || '');
  const [pseudo, setPseudo] = useState('');
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const result = await api.post('/api/players/join', { code_pin: pin, pseudo: pseudo || null });
      navigate(`/play/${result.player.id}`);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="page page-center">
      <Header />
      <h1>Quiz Séries</h1>
      <p>Entrez le code PIN affiché à l'écran pour rejoindre la partie.</p>
      <form onSubmit={handleSubmit} className="card">
        <label>
          Code PIN
          <input
            value={pin}
            onChange={(e) => setPin(e.target.value)}
            inputMode="numeric"
            maxLength={6}
            required
            autoFocus
          />
        </label>
        <label>
          Pseudo (optionnel)
          <input value={pseudo} onChange={(e) => setPseudo(e.target.value)} maxLength={100} />
        </label>
        {error && <p className="error">{error}</p>}
        <button type="submit" disabled={loading}>
          {loading ? 'Connexion...' : 'Rejoindre'}
        </button>
      </form>
    </div>
  );
}
