-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : jeu. 15 juil. 2021 à 15:28
-- Version du serveur :  5.7.32
-- Version de PHP : 7.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données : `bijouterie_symfony`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` int(11) NOT NULL,
  `create_at` date NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `nom`, `prix`, `create_at`, `photo`, `categorie_id`) VALUES
(1, 'Collier Dora', 200, '2021-07-09', '2021070813282560e6fd79379f7collier-dora.jpg', 1),
(2, 'Bague Hello Kitty', 400, '2021-07-09', '2021070809533060e6cb1a816e1bague-hello-kitty.jpg', 3),
(3, 'Bague Dora', 300, '2021-07-09', '2021070809534360e6cb270d258bague-dora.jpg', 3),
(4, 'Collier Hello Kitty', 450, '2021-07-09', '2021070809540560e6cb3d5a2b1collier-hello-kitty.jpg', 1),
(7, 'Collier \"Reine des neiges\"', 500, '2021-07-09', '2021070813430260e700e61db0ccollier-reine-des-neige.jpg', 1),
(8, 'Bague \"Reine des neiges\"', 400, '2021-07-09', '2021070813453360e7017df3ae8bague-reine-des-neige.jpg', 3),
(9, 'Bague très moche', 2900, '2021-07-15', '2021071509243460effed2ac5412002.jpg', 3),
(10, 'Collier moche', 1000, '2021-07-15', '2021071509295960f000173cb7c652639799_x.jpg', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_23A0E66BCF5E72D` (`categorie_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `FK_23A0E66BCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`);
