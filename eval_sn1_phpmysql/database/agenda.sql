-- ============================================================
--  Agenda d'événements : base de données de départ
-- ============================================================
--  Import : dans phpMyAdmin, onglet « Importer », choisir ce fichier.
--
--  Comptes de test (mot de passe en clair entre parenthèses) :
--    - admin@agenda.fr  (admin123)  → administrateur
--    - user@agenda.fr   (user123)   → utilisateur simple
--
--  ⚠️ La table `reservation` n'existe PAS : c'est à vous de la créer.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+01:00';

DROP DATABASE IF EXISTS `agenda`;
CREATE DATABASE `agenda` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `agenda`;

-- ------------------------------------------------------------
--  Table `user`
-- ------------------------------------------------------------
--  role : 'user' ou 'admin'
--  password : hash généré avec password_hash() (à vérifier avec password_verify())

CREATE TABLE `user` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(180) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `role`       VARCHAR(20)  NOT NULL DEFAULT 'user',
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`id`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin@agenda.fr', '$2y$12$pdRt.axJFaqpcmPLnDJUEehiSInM.iFBB1P9qFEdUVMiCEeMTk2Wu', 'admin', '2026-09-01 09:00:00'),
(2, 'user@agenda.fr',  '$2y$12$i/zFRkIgbrSWs4QRGrUaR.ie3XbnrGye88qsIqyuzKW9vlHuihCIa', 'user',  '2026-09-02 10:30:00');

-- ------------------------------------------------------------
--  Table `event`
-- ------------------------------------------------------------
--  category     : 'concert', 'conference', 'atelier' ou 'sport'
--                 (la valeur correspond directement à la classe CSS : cat-concert, cat-conference…)
--  places       : nombre total de places
--  image        : chemin de l'image à afficher (NULL si pas d'image)
--  is_validated : 0 = « Non validé », 1 = validé par un admin

CREATE TABLE `event` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(255) NOT NULL,
    `date`         DATETIME     NOT NULL,
    `information`  TEXT         NOT NULL,
    `category`     VARCHAR(20)  NOT NULL,
    `places`       INT UNSIGNED NOT NULL,
    `image`        VARCHAR(255) DEFAULT NULL,
    `is_validated` TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `event` (`id`, `title`, `date`, `information`, `category`, `places`, `image`, `is_validated`, `created_at`) VALUES
-- Événements à venir et validés (affichés sur la page d'accueil)
(1, 'Soirée jazz au Kiosque', '2026-11-07 20:30:00',
 'Le quartet CuRuba vous propose une soirée de jazz antillais au Kiosque du parc municipal.\nProgramme : 20h30 première partie, 21h30 concert principal. Buvette sur place, entrée libre sur inscription.',
 'concert', 80, 'images/concert.jpg', 1, '2026-10-01 12:00:00'),
(2, 'L''IA expliquée simplement', '2026-11-12 18:00:00',
 'Une heure pour comprendre ce qu''il y a derrière ChatGPT et les autres assistants, sans jargon.\nMédiathèque centrale, salle Jules Verne. Questions-réponses à la fin.',
 'conference', 60, 'images/conference.jpg', 1, '2026-10-02 09:15:00'),
(3, 'Initiation à la poterie', '2026-11-21 14:00:00',
 'Découverte du tour et du modelage avec une céramiste du quartier.\nTablier fourni, prévoir des vêtements qui ne craignent rien. Durée : 3 heures.',
 'atelier', 10, 'images/atelier.jpg', 1, '2026-10-03 16:40:00'),
(4, 'Semi-marathon de la ville', '2026-11-29 09:00:00',
 'Départ et arrivée place de la Mairie, parcours de 21,1 km le long du fleuve.\nRetrait des dossards la veille au gymnase municipal.',
 'sport', 300, 'images/sport.jpg', 1, '2026-10-04 08:00:00'),
(5, 'Rock indé au Sous-Sol', '2026-12-05 21:00:00',
 'Trois groupes de la scène locale se succèdent au Sous-Sol.\nOuverture des portes à 20h30. Bar sur place.',
 'concert', 120, 'images/rock.jpg', 1, '2026-10-05 19:20:00'),
(6, 'Escalade en famille', '2026-12-13 10:00:00',
 'Séance découverte encadrée par des moniteurs diplômés, à partir de 6 ans.\nChaussons et baudriers fournis. Salle Vertical''Art.',
 'sport', 24, 'images/escalade.jpg', 1, '2026-10-06 11:00:00'),
(7, 'Atelier tricot pour débutants', '2026-12-17 18:30:00',
 'Apprenez les mailles de base et repartez avec votre première écharpe.\nLaine et aiguilles fournies. Petit groupe : seulement 2 places !',
 'atelier', 2, NULL, 1, '2026-10-07 14:00:00'),

-- Événements en attente de validation (page /validation)
(8, 'Atelier cuisine du monde', '2026-12-16 18:00:00',
 'Cuisinez un menu complet inspiré de la cuisine libanaise : houmous, taboulé et baklava.\nTous les ingrédients sont fournis.',
 'atelier', 12, 'images/atelier.jpg', 0, '2026-10-10 10:00:00'),
(9, 'Tournoi de basket 3x3', '2026-12-19 14:00:00',
 'Tournoi ouvert à tous, équipes de 3 joueurs + 1 remplaçant.\nTerrain du parc des sports. Inscription par équipe sur place.',
 'sport', 48, 'images/sport.jpg', 0, '2026-10-11 17:30:00'),
(10, 'Concert de Noël', '2026-12-22 20:00:00',
 'La chorale municipale et l''orchestre du conservatoire interprètent les grands classiques de Noël.\nÉglise Saint-Pierre, entrée libre.',
 'concert', 150, NULL, 0, '2026-10-12 13:45:00'),

-- Événements passés (validés, ne doivent PAS apparaître sur la page d'accueil)
(11, 'Fête de la musique', '2026-06-21 18:00:00',
 'Scènes ouvertes dans tout le centre-ville.\nProgramme complet à l''office de tourisme.',
 'concert', 500, 'images/rock.jpg', 1, '2026-05-15 10:00:00'),
(12, 'Conférence : le cerveau et le sommeil', '2026-09-24 19:00:00',
 'Un chercheur en neurosciences explique pourquoi nous dormons et comment mieux dormir.\nAmphithéâtre de l''université.',
 'conference', 200, 'images/conference.jpg', 1, '2026-09-01 09:00:00');

-- ------------------------------------------------------------
--  Table `reservation` : À CRÉER PAR VOUS (voir le README)
-- ------------------------------------------------------------
