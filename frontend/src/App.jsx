import { Routes, Route } from 'react-router-dom';
import Join from './pages/Join.jsx';
import Play from './pages/Play.jsx';
import Host from './pages/Host.jsx';
import AdminQuizzes from './pages/AdminQuizzes.jsx';
import AdminQuizEdit from './pages/AdminQuizEdit.jsx';

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Join />} />
      <Route path="/join/:pin" element={<Join />} />
      <Route path="/play/:playerId" element={<Play />} />
      <Route path="/host/:quizId" element={<Host />} />
      <Route path="/admin" element={<AdminQuizzes />} />
      <Route path="/admin/quizzes/:quizId" element={<AdminQuizEdit />} />
    </Routes>
  );
}
