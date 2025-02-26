-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 26 fév. 2025 à 02:35
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `quizzapp`
--

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text NOT NULL,
  `option_d` text NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quiz_id` int(11) DEFAULT NULL,
  `timer` int(11) NOT NULL DEFAULT 30,
  `scenario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `created_at`, `quiz_id`, `timer`, `scenario`) VALUES
(1, 'Quelle est la capitale de la France ?', 'Paris', 'Londres', 'Berlin', 'Madrid', 'A', '2025-02-26 00:57:24', 4, 30, 'La France est un pays d\'Europe occidentale.'),
(2, 'Combien font 2 + 2 ?', '3', '4', '5', '6', 'B', '2025-02-26 00:57:24', 4, 20, 'Dans un calcul simple d\'addition.'),
(3, 'Quelle est la plus grande planète du système solaire ?', 'Mars', 'Vénus', 'Jupiter', 'Saturne', 'C', '2025-02-26 00:57:24', 4, 25, 'Dans notre système solaire, il existe 8 planètes principales.'),
(4, 'Quelle est la capitale de la France ?', 'Londres', 'Paris', 'Berlin', 'Marseille', 'B', '2025-02-26 00:57:39', 5, 30, 'Un touriste vous demande la capitale de la France'),
(5, 'Quels sont les pays frontaliers de la France ?', 'Belgique', 'Allemagne', 'Suisse', 'Colombie', 'A', '2025-02-26 00:57:39', 5, 30, 'Un géographe étudie les frontières de la France'),
(6, 'Quelle est la couleur du ciel ?', 'Jaune', 'Bleu', 'Vert', 'Rouge', 'B', '2025-02-26 00:57:39', 5, 30, 'Un enfant vous pose la question en regardant par la fenêtre'),
(7, 'Quels sont les langages de programmation web ?', 'Cobra', 'Python', 'JavaScript', 'Java', 'C', '2025-02-26 00:57:39', 5, 30, 'Un développeur web prépare un projet'),
(8, 'Quel est le plus grand océan du monde ?', 'Pacifique', 'Atlantique', 'Indien', 'Arctique', 'A', '2025-02-26 00:57:39', 5, 30, 'Un élève prépare un exposé sur les océans'),
(9, 'Qui a peint la Joconde ?', 'Léonard de Vinci', 'Michel-Ange', 'Picasso', 'Van Gogh', 'A', '2025-02-26 00:57:39', 5, 30, 'Un historien d\'art étudie la Renaissance'),
(10, 'Quelles sont les couleurs du drapeau français ?', 'Bleu', 'Blanc', 'Rouge', 'Vert', 'C', '2025-02-26 00:57:39', 5, 30, 'Un enfant apprend les couleurs du drapeau'),
(11, 'Quels sont les océans qui bordent la France ?', 'Atlantique', 'Pacifique', 'Indien', 'Méditerranée', 'A', '2025-02-26 00:57:39', 5, 30, 'Un marin étudie les côtes françaises');

-- --------------------------------------------------------

--
-- Structure de la table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `professor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `description`, `category`, `created_at`, `professor_id`) VALUES
(4, 'tess', 'tesst', 'informatique', '2025-02-26 00:57:24', 1),
(5, 'tafaz', 'ararfa', 'geographie', '2025-02-26 00:57:39', 1);

-- --------------------------------------------------------

--
-- Structure de la table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `results`
--

INSERT INTO `results` (`id`, `user_id`, `quiz_id`, `score`, `total_questions`, `completed_at`) VALUES
(1, 2, 5, 3, 8, '2025-02-26 01:17:28');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'professeur@quiz.com', 'prof123', 'professeur', '2025-02-25 21:08:00'),
(2, 'eleve1@quiz.com', 'eleve123', 'eleve', '2025-02-25 21:08:00'),
(3, 'eleve2@quiz.com', 'eleve123', 'eleve', '2025-02-25 21:08:00'),
(4, 'eleve3@quiz.com', 'eleve123', 'eleve', '2025-02-25 21:08:00'),
(5, 'admin@quiz.com', 'admin123', 'admin', '2025-02-26 01:35:03');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Index pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Index pour la table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `results_ibfk_2` (`quiz_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);

--
-- Contraintes pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
