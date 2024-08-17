-- Création de la base de données

CREATE DATABASE animaflip
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Création des tables MySQL dans un second temps
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user'
);

CREATE TABLE themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(255) NOT NULL
);

CREATE TABLE animations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme_id INT NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (theme_id) REFERENCES themes(id)
);

ALTER DATABASE animaflip CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE themes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE animations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Insérer des thèmes
INSERT INTO themes (libelle) VALUES 
('Nature'),
('Technologie'),
('Sport'),
('Musique');

-- Insérer des animations pour chaque thème
INSERT INTO animations (theme_id, libelle, video_url) VALUES
((SELECT id FROM themes WHERE libelle = 'Nature'), 'Oiseaux Chantants', "/videos/birds_singing.mp4"),
((SELECT id FROM themes WHERE libelle = 'Nature'), 'Rivière Coulante', "/videos/river_flowing.mp4"),
((SELECT id FROM themes WHERE libelle = 'Technologie'), 'Démarrage voiture', "/videos/startup_car.mp4"),
((SELECT id FROM themes WHERE libelle = 'Technologie'), 'Voix Robotique', "/videos/robotic_voice.mp4"),
((SELECT id FROM themes WHERE libelle = 'Sport'), 'Acclamations de la Foule', "/videos/crowd_cheering.mp4"),
((SELECT id FROM themes WHERE libelle = 'Sport'), 'Sifflement', "/videos/whistle_blowing.mp4"),
((SELECT id FROM themes WHERE libelle = 'Musique'), 'Solo de Guitare', "/videos/guitar_solo.mp4"),
((SELECT id FROM themes WHERE libelle = 'Musique'), 'Mélodie de Piano', "/videos/piano_melody.mp4");

-- Ajout d'un utilisateur admin, pswd : admin
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$0Kqjjo4ZrJCGKFSgpCOnsubwLv66elX4tntVXLKv6Xq10OfzGskXS', 'admin');