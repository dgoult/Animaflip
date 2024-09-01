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
('Transport'),
('Animaux de compagnie'),
('Animaux de la ferme'),
('Animaux sauvages'),
('Instrument');

INSERT INTO animations (theme_id, libelle, video_url) VALUES
-- Thème "Transport"
((SELECT id FROM themes WHERE libelle = "Transport"), "Le camion", "/videos/camion"),
((SELECT id FROM themes WHERE libelle = "Transport"), "Une ambulance", "/videos/ambulance"),
((SELECT id FROM themes WHERE libelle = "Transport"), "Un avion", "/videos/avion"),
((SELECT id FROM themes WHERE libelle = "Transport"), "Le bateau", "/videos/bateau"),
((SELECT id FROM themes WHERE libelle = "Transport"), "Le velo", "/videos/velo"),

-- Thème "Animaux de compagnie"
((SELECT id FROM themes WHERE libelle = "Animaux de compagnie"), "Le chat", "/videos/chat"),
((SELECT id FROM themes WHERE libelle = "Animaux de compagnie"), "Le chien", "/videos/chien"),
((SELECT id FROM themes WHERE libelle = "Animaux de compagnie"), "Le lapin", "/videos/lapin"),
((SELECT id FROM themes WHERE libelle = "Animaux de compagnie"), "Le cochons-dindes", "/videos/cochons-dindes"),

-- Thème "Animaux de la ferme"
((SELECT id FROM themes WHERE libelle = "Animaux de la ferme"), "La poule", "/videos/poule"),
((SELECT id FROM themes WHERE libelle = "Animaux de la ferme"), "Le coq", "/videos/coq"),
((SELECT id FROM themes WHERE libelle = "Animaux de la ferme"), "Le poussin", "/videos/poussin"),
((SELECT id FROM themes WHERE libelle = "Animaux de la ferme"), "Le mouton", "/videos/mouton"),
((SELECT id FROM themes WHERE libelle = "Animaux de la ferme"), "Une oie (test si non fonctionnel)", "/videos/oie"),

-- Thème "Animaux sauvages"
((SELECT id FROM themes WHERE libelle = "Animaux sauvages"), "Le cerf", "/videos/cerf"),
((SELECT id FROM themes WHERE libelle = "Animaux sauvages"), "Le loup", "/videos/loup"),
((SELECT id FROM themes WHERE libelle = "Animaux sauvages"), "Le panda-roux", "/videos/panda-roux"),
((SELECT id FROM themes WHERE libelle = "Animaux sauvages"), "Le singe", "/videos/singe"),

-- Thème "Instrument"
((SELECT id FROM themes WHERE libelle = 'Instrument'), 'La guitare', "/videos/guitare"),
((SELECT id FROM themes WHERE libelle = 'Instrument'), 'Le ukulele', "/videos/ukulele"),
((SELECT id FROM themes WHERE libelle = 'Instrument'), 'La harpe', "/videos/harpe"),
((SELECT id FROM themes WHERE libelle = 'Instrument'), 'Le piano', "/videos/piano"),
((SELECT id FROM themes WHERE libelle = 'Instrument'), 'Le violon', "/videos/violon");

-- Ajout d'un utilisateur admin, pswd : admin
INSERT INTO users (username, password, role) VALUES ('admin@admin.com', '$2y$10$0Kqjjo4ZrJCGKFSgpCOnsubwLv66elX4tntVXLKv6Xq10OfzGskXS', 'admin');
INSERT INTO users (username, password, role) VALUES ('user@user.com', '$2y$10$8wVAFxLpRVLn8agO.Sh5a.RwV27qDibjycDIMnrnAzeUkgZRQykqm', 'user');

-- Ajout de l'accès au thèmes des utilisateurs
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