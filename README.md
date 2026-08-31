# Quiz Séries — Séminaire

Application de quiz temps réel sur le thème des séries TV, avec connexion des joueurs par QR code et identification par numéro façon Squid Game.

## Stack

- **Backend** : PHP 8.2 (MVC maison, sans framework), API REST, MySQL 8 (PDO)
- **Frontend** : React 18 + Vite, React Router
- **Temps réel** : [Mercure](https://mercure.rocks/) (hub SSE) pour synchroniser le chrono, la diffusion des questions et le classement live
- **Déploiement** : Docker Compose

## Démarrage

```bash
cp .env.example .env
# Adapter les mots de passe et, le jour J, les URLs publiques (FRONTEND_URL, PUBLIC_API_URL, PUBLIC_MERCURE_URL)
# avec l'IP ou le nom de domaine réellement accessible par les joueurs sur le réseau du séminaire.

docker compose up --build
```

Services disponibles :

| Service | URL par défaut | Rôle |
|---|---|---|
| Frontend | http://localhost:8081 | Interface joueurs / hôte / admin |
| Backend API | http://localhost:8080 | API REST |
| Mercure | http://localhost:3000 | Hub temps réel |
| Adminer | http://localhost:8082 | Inspection de la base MySQL |

Un quiz de démonstration ("Séries Cultes", 3 questions) est chargé automatiquement au premier démarrage.

## Utilisation

1. **Admin** (`/admin`) : créer un quiz, ajouter des questions (média + texte, réponse attendue, points max, durée).
2. **Média des questions** : téléverser directement une image/vidéo depuis le formulaire admin (bouton "Téléverser"), ou renseigner une URL externe manuellement.
3. **Lancer une partie** : depuis `/admin`, cliquer sur "Lancer" pour un quiz → ouvre l'écran hôte (`/host/{quizId}`) avec le QR code et le code PIN à projeter.
4. **Joueurs** : scanner le QR code (ou aller sur `/` et saisir le code PIN) → chaque joueur reçoit un numéro (001, 002, ...).
5. **Déroulé** : l'hôte démarre chaque question, les joueurs voient le média + texte, ont le temps imparti pour saisir le nom de la série, puis l'hôte révèle la réponse et le classement avant de passer à la suivante.

## Règles de jeu

- **Comparaison des réponses** : insensible à la casse, aux accents, aux apostrophes et à la ponctuation/espaces (ex. `Grey's Anatomy`, `greys anatomy` et `grey s anatomy` sont tous acceptés).
- **Réponses alternatives** : dans le formulaire d'une question, un champ optionnel permet d'ajouter d'autres réponses acceptées (une par ligne, ex. `GOT` en plus de `Game of Thrones`).
- **Score dégressif** : une réponse correcte immédiate rapporte `points_max`, une réponse juste avant l'expiration du temps rapporte 10% de `points_max` (dégressif linéaire). Le temps est mesuré côté serveur (horodatage du début de question), pas côté client, pour éviter la triche.
- Chaque joueur ne peut répondre qu'une seule fois par question.

## Upload de médias

Les fichiers téléversés depuis l'admin sont validés côté serveur (type MIME réel, 20 Mo max pour images/vidéos/audio JPEG/PNG/GIF/WebP/MP4/WebM/MOV/MP3) et stockés dans `backend/public/media/` avec un nom généré aléatoirement. Ce dossier est monté en volume Docker : sur un déploiement existant, assurez-vous qu'il est accessible en écriture par le conteneur (`chmod -R a+rwX backend/public/media` sur l'hôte si besoin).

Pour une question de type **audio (MP3)**, le fichier n'est joué que sur l'écran hôte (celui projeté en salle) : les téléphones des joueurs affichent un simple repère visuel, sans lecture audio, pour éviter tout décalage entre les appareils.

## Structure du projet

```
backend/    API PHP MVC (Controllers / Models / Services / Core)
frontend/   Application React (pages Join, Play, Host, Admin)
docker-compose.yml
```

## Développement local (sans Docker)

```bash
# Backend
cd backend && composer install
php -S localhost:8080 -t public

# Frontend
cd frontend && npm install && npm run dev
```

Une base MySQL et un hub Mercure restent nécessaires (via `docker compose up mysql mercure` par exemple).
