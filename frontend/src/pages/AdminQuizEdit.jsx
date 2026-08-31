import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { api, uploadMedia, mediaUrl } from '../api/client';
import Header from '../components/Header';

const emptyForm = {
  id: null,
  media_url: '',
  media_type: 'image',
  texte: '',
  reponse: '',
  alternatives: '',
  points_max: 1000,
  duree_secondes: 20,
  ordre: 1,
};

function parseAlternatives(json) {
  if (!json) return [];
  try {
    const parsed = JSON.parse(json);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

export default function AdminQuizEdit() {
  const { quizId } = useParams();
  const [quiz, setQuiz] = useState(null);
  const [error, setError] = useState(null);
  const [form, setForm] = useState(emptyForm);
  const [uploading, setUploading] = useState(false);

  function load() {
    api.get(`/api/quizzes/${quizId}`).then(setQuiz).catch((e) => setError(e.message));
  }

  useEffect(load, [quizId]);

  function updateField(field, value) {
    setForm((f) => ({ ...f, [field]: value }));
  }

  async function handleFileChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    setUploading(true);
    setError(null);
    try {
      const result = await uploadMedia(file);
      setForm((f) => ({ ...f, media_url: result.media_url, media_type: result.media_type }));
    } catch (err) {
      setError(err.message);
    } finally {
      setUploading(false);
      e.target.value = '';
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    try {
      const payload = {
        media_url: form.media_url,
        media_type: form.media_type,
        texte: form.texte,
        reponse: form.reponse,
        alternatives: form.alternatives
          .split('\n')
          .map((a) => a.trim())
          .filter(Boolean),
        points_max: Number(form.points_max),
        duree_secondes: Number(form.duree_secondes),
        ordre: Number(form.ordre),
      };
      if (form.id) {
        await api.put(`/api/questions/${form.id}`, payload);
      } else {
        await api.post(`/api/quizzes/${quizId}/questions`, payload);
      }
      setForm({ ...emptyForm, ordre: (quiz.questions?.length || 0) + 1 });
      load();
    } catch (err) {
      setError(err.message);
    }
  }

  function editQuestion(q) {
    setForm({
      id: q.id,
      media_url: q.media_url || '',
      media_type: q.media_type,
      texte: q.texte || '',
      reponse: q.reponse_affichee,
      alternatives: parseAlternatives(q.reponses_alternatives).join('\n'),
      points_max: q.points_max,
      duree_secondes: q.duree_secondes,
      ordre: q.ordre,
    });
  }

  async function deleteQuestion(id) {
    if (!window.confirm('Supprimer cette question ?')) return;
    await api.del(`/api/questions/${id}`);
    load();
  }

  if (error) return <div className="page"><p className="error">{error}</p></div>;
  if (!quiz) return <div className="page"><p>Chargement...</p></div>;

  return (
    <div className="page">
      <Header />
      <p><Link to="/admin">&larr; Retour aux quiz</Link></p>
      <h1>{quiz.nom}</h1>

      <h2>Questions</h2>
      <ol className="admin-question-list">
        {quiz.questions.map((q) => {
          const alternatives = parseAlternatives(q.reponses_alternatives);
          return (
            <li key={q.id} className="card">
              <div>
                <strong>{q.reponse_affichee}</strong> — {q.points_max} pts / {q.duree_secondes}s
                <div className="muted">{q.texte}</div>
                {alternatives.length > 0 && (
                  <div className="muted">Alternatives acceptées : {alternatives.join(', ')}</div>
                )}
              </div>
              <div className="actions">
                <button onClick={() => editQuestion(q)}>Éditer</button>
                <button onClick={() => deleteQuestion(q.id)}>Supprimer</button>
              </div>
            </li>
          );
        })}
      </ol>

      <h2>{form.id ? 'Modifier la question' : 'Ajouter une question'}</h2>
      <form onSubmit={handleSubmit} className="card">
        <label>
          Téléverser une image, vidéo ou audio (MP3)
          <input type="file" accept="image/*,video/*,audio/*" onChange={handleFileChange} disabled={uploading} />
        </label>
        {uploading && <p className="muted">Téléversement en cours...</p>}
        {form.media_url && form.media_type === 'image' && (
          <img src={mediaUrl(form.media_url)} alt="Aperçu" className="media" />
        )}
        {form.media_url && form.media_type === 'video' && (
          <video src={mediaUrl(form.media_url)} className="media" controls />
        )}
        {form.media_url && form.media_type === 'audio' && (
          <audio src={mediaUrl(form.media_url)} controls className="media-audio" />
        )}
        <label>
          URL du média (image, vidéo ou audio)
          <input value={form.media_url} onChange={(e) => updateField('media_url', e.target.value)} required />
        </label>
        <label>
          Type de média
          <select value={form.media_type} onChange={(e) => updateField('media_type', e.target.value)}>
            <option value="image">Image</option>
            <option value="video">Vidéo</option>
            <option value="audio">Audio (MP3) — joué uniquement sur l'écran hôte</option>
          </select>
        </label>
        <label>
          Texte d'indice
          <textarea value={form.texte} onChange={(e) => updateField('texte', e.target.value)} />
        </label>
        <label>
          Réponse attendue (nom de la série)
          <input value={form.reponse} onChange={(e) => updateField('reponse', e.target.value)} required />
        </label>
        <label>
          Réponses alternatives acceptées (une par ligne, optionnel)
          <textarea
            value={form.alternatives}
            onChange={(e) => updateField('alternatives', e.target.value)}
            placeholder={'GOT\nGame of Thrones (VF)'}
          />
        </label>
        <label>
          Points maximum
          <input type="number" min="1" value={form.points_max} onChange={(e) => updateField('points_max', e.target.value)} required />
        </label>
        <label>
          Durée (secondes)
          <input type="number" min="1" value={form.duree_secondes} onChange={(e) => updateField('duree_secondes', e.target.value)} required />
        </label>
        <label>
          Ordre
          <input type="number" min="1" value={form.ordre} onChange={(e) => updateField('ordre', e.target.value)} required />
        </label>
        <div className="actions">
          <button type="submit">{form.id ? 'Enregistrer' : 'Ajouter'}</button>
          {form.id && <button type="button" onClick={() => setForm(emptyForm)}>Annuler</button>}
        </div>
      </form>
    </div>
  );
}
