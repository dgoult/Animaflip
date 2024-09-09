-- Executer en premier -> Création de la base de données

CREATE DATABASE animaflip
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE animaflip;

-- Création des tables MySQL
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user'
);

CREATE TABLE themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE animations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(255) NOT NULL UNIQUE,
    video_url VARCHAR(255) NOT NULL
);

-- Table de liaison entre les thèmes et les animations pour la relation 1.n
CREATE TABLE theme_animation (
    theme_id INT NOT NULL,
    animation_id INT NOT NULL,
    PRIMARY KEY (theme_id, animation_id),
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE,
    FOREIGN KEY (animation_id) REFERENCES animations(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER DATABASE animaflip CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE themes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE animations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Insérer des thèmes
INSERT INTO themes (libelle) VALUES 
('Transport'),
('Animaux de compagnie'),
('Animaux de la ferme'),
('Animaux sauvages'),
('Instrument');

-- Insérer des animations
INSERT INTO animations (libelle, video_url) VALUES
('Le camion', '/videos/camion'),
('Une ambulance', '/videos/ambulance'),
('Un avion', '/videos/avion'),
('Le bateau', '/videos/bateau'),
('Le velo', '/videos/velo'),
('Le chat', '/videos/chat'),
('Le chien', '/videos/chien'),
('Le lapin', '/videos/lapin'),
('Le cochons-dindes', '/videos/cochons-dindes'),
('La poule', '/videos/poule'),
('Le coq', '/videos/coq'),
('Le poussin', '/videos/poussin'),
('Le mouton', '/videos/mouton'),
('Une oie', '/videos/oie'),
('Le cerf', '/videos/cerf'),
('Le loup', '/videos/loup'),
('Le panda-roux', '/videos/panda-roux'),
('Le singe', '/videos/singe'),
('La guitare', '/videos/guitare'),
('Le ukulele', '/videos/ukulele'),
('La harpe', '/videos/harpe'),
('Le piano', '/videos/piano'),
('Le violon', '/videos/violon');

-- Assigner les animations aux thèmes dans la table pivot
INSERT INTO theme_animation (theme_id, animation_id) VALUES
((SELECT id FROM themes WHERE libelle = 'Transport'), (SELECT id FROM animations WHERE libelle = 'Le camion')),
((SELECT id FROM themes WHERE libelle = 'Transport'), (SELECT id FROM animations WHERE libelle = 'Une ambulance')),
((SELECT id FROM themes WHERE libelle = 'Transport'), (SELECT id FROM animations WHERE libelle = 'Un avion')),
((SELECT id FROM themes WHERE libelle = 'Transport'), (SELECT id FROM animations WHERE libelle = 'Le bateau')),
((SELECT id FROM themes WHERE libelle = 'Transport'), (SELECT id FROM animations WHERE libelle = 'Le velo')),
((SELECT id FROM themes WHERE libelle = 'Animaux de compagnie'), (SELECT id FROM animations WHERE libelle = 'Le chat')),
((SELECT id FROM themes WHERE libelle = 'Animaux de compagnie'), (SELECT id FROM animations WHERE libelle = 'Le chien')),
((SELECT id FROM themes WHERE libelle = 'Animaux de compagnie'), (SELECT id FROM animations WHERE libelle = 'Le lapin')),
((SELECT id FROM themes WHERE libelle = 'Animaux de compagnie'), (SELECT id FROM animations WHERE libelle = 'Le cochons-dindes')),
((SELECT id FROM themes WHERE libelle = 'Animaux de compagnie'), (SELECT id FROM animations WHERE libelle = 'La poule')),
((SELECT id FROM themes WHERE libelle = 'Animaux de compagnie'), (SELECT id FROM animations WHERE libelle = 'Le coq')),
((SELECT id FROM themes WHERE libelle = 'Animaux de la ferme'), (SELECT id FROM animations WHERE libelle = 'La poule')),
((SELECT id FROM themes WHERE libelle = 'Animaux de la ferme'), (SELECT id FROM animations WHERE libelle = 'Le coq')),
((SELECT id FROM themes WHERE libelle = 'Animaux de la ferme'), (SELECT id FROM animations WHERE libelle = 'Le poussin')),
((SELECT id FROM themes WHERE libelle = 'Animaux de la ferme'), (SELECT id FROM animations WHERE libelle = 'Le mouton')),
((SELECT id FROM themes WHERE libelle = 'Animaux de la ferme'), (SELECT id FROM animations WHERE libelle = 'Le chien')),
((SELECT id FROM themes WHERE libelle = 'Animaux de la ferme'), (SELECT id FROM animations WHERE libelle = 'Une oie')),
((SELECT id FROM themes WHERE libelle = 'Animaux sauvages'), (SELECT id FROM animations WHERE libelle = 'Le cerf')),
((SELECT id FROM themes WHERE libelle = 'Animaux sauvages'), (SELECT id FROM animations WHERE libelle = 'Le loup')),
((SELECT id FROM themes WHERE libelle = 'Animaux sauvages'), (SELECT id FROM animations WHERE libelle = 'Le panda-roux')),
((SELECT id FROM themes WHERE libelle = 'Animaux sauvages'), (SELECT id FROM animations WHERE libelle = 'Le singe')),
((SELECT id FROM themes WHERE libelle = 'Instrument'), (SELECT id FROM animations WHERE libelle = 'La guitare')),
((SELECT id FROM themes WHERE libelle = 'Instrument'), (SELECT id FROM animations WHERE libelle = 'Le ukulele')),
((SELECT id FROM themes WHERE libelle = 'Instrument'), (SELECT id FROM animations WHERE libelle = 'La harpe')),
((SELECT id FROM themes WHERE libelle = 'Instrument'), (SELECT id FROM animations WHERE libelle = 'Le piano')),
((SELECT id FROM themes WHERE libelle = 'Instrument'), (SELECT id FROM animations WHERE libelle = 'Le violon'));

-- Ajout d'un utilisateur admin, pswd : admin
INSERT INTO users (username, password, role) VALUES ('admin@admin.com', '$2y$10$0Kqjjo4ZrJCGKFSgpCOnsubwLv66elX4tntVXLKv6Xq10OfzGskXS', 'admin');
INSERT INTO users (username, password, role) VALUES ('user@user.com', '$2y$10$8wVAFxLpRVLn8agO.Sh5a.RwV27qDibjycDIMnrnAzeUkgZRQykqm', 'user');

-- Ajout de l'accès aux thèmes des utilisateurs
CREATE TABLE user_themes (
    user_id INT NOT NULL,
    theme_id INT NOT NULL,
    PRIMARY KEY (user_id, theme_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Donner accès à tous les thèmes au premier admin
INSERT INTO user_themes (user_id, theme_id)
SELECT u.id, t.id
FROM users u
JOIN themes t
WHERE u.username = 'admin@admin.com';