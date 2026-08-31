CREATE TABLE IF NOT EXISTS quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  description TEXT,
  statut ENUM('brouillon','pret','archive') NOT NULL DEFAULT 'brouillon',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  media_url VARCHAR(500),
  media_type ENUM('image','video','audio') NOT NULL,
  texte TEXT,
  reponse_normalisee VARCHAR(255) NOT NULL,
  reponse_affichee VARCHAR(255) NOT NULL,
  points_max INT NOT NULL,
  duree_secondes INT NOT NULL,
  ordre INT NOT NULL,
  CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id)
    REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  code_pin VARCHAR(10) NOT NULL UNIQUE,
  statut ENUM('attente','en_cours','termine') NOT NULL DEFAULT 'attente',
  question_courante_id INT NULL,
  question_started_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sessions_quiz FOREIGN KEY (quiz_id)
    REFERENCES quizzes(id) ON DELETE CASCADE,
  CONSTRAINT fk_sessions_question FOREIGN KEY (question_courante_id)
    REFERENCES questions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  numero VARCHAR(10) NOT NULL,
  pseudo VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_players_session FOREIGN KEY (session_id)
    REFERENCES sessions(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_numero_session (session_id, numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  question_id INT NOT NULL,
  reponse_brute VARCHAR(255),
  reponse_normalisee VARCHAR(255),
  temps_ms INT,
  points_obtenus INT NOT NULL DEFAULT 0,
  correcte BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_answers_player FOREIGN KEY (player_id)
    REFERENCES players(id) ON DELETE CASCADE,
  CONSTRAINT fk_answers_question FOREIGN KEY (question_id)
    REFERENCES questions(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_player_question (player_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
