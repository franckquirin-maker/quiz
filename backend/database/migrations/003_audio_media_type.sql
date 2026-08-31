ALTER TABLE questions
  MODIFY COLUMN media_type ENUM('image','video','audio') NOT NULL;
