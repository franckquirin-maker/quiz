INSERT INTO quizzes (id, nom, description, statut) VALUES
  (1, 'Séries Cultes', 'Devinez la série à partir d''une image ou d''un extrait', 'pret');

INSERT INTO questions (quiz_id, media_url, media_type, texte, reponse_normalisee, reponse_affichee, points_max, duree_secondes, ordre) VALUES
  (1, '/media/q1.jpg', 'image', 'Une île, un crash d''avion, beaucoup de mystères...', 'lost', 'Lost', 1000, 20, 1),
  (1, '/media/q2.jpg', 'image', 'Un jeu mortel, des combinaisons colorées, un numéro sur le survêtement.', 'squidgame', 'Squid Game', 1000, 20, 2),
  (1, '/media/q3.jpg', 'image', 'Un hôpital de Seattle, des chirurgiens, beaucoup de drames.', 'greysanatomy', 'Grey''s Anatomy', 1000, 20, 3);
