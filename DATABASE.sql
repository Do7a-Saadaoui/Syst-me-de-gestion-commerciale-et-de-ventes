-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 27 fév. 2026 à 22:43
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
-- Base de données : `gestion_commerciale`
--

-- --------------------------------------------------------

--
-- Structure de la table `achats`
--

CREATE TABLE `achats` (
  `id` int(11) NOT NULL,
  `code_fournisseur` varchar(50) NOT NULL,
  `fournisseur` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `pays` varchar(50) DEFAULT 'Maroc',
  `telephone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `personne_contact` varchar(100) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_total` decimal(10,2) NOT NULL,
  `date_achat` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `achats`
--

INSERT INTO `achats` (`id`, `code_fournisseur`, `fournisseur`, `adresse`, `ville`, `pays`, `telephone`, `fax`, `personne_contact`, `produit_id`, `quantite`, `prix_total`, `date_achat`, `created_at`) VALUES
(2, 'FRN-MOB-002', 'Mobile Distribution', '456 Avenue Mohammed V', 'Rabat', 'Maroc', '+212 537 654 321', '+212 537 654 322', 'Mme. Fatima Zahra', 2, 20, 12000.00, '2024-01-16', '2025-10-31 23:24:22'),
(3, 'FRN-BUR-003', 'Bureau Plus Maroc', '789 Boulevard Hassan II', 'Marrakech', 'Maroc', '+212 524 987 654', '+212 524 987 655', 'M. Karim Alami', 3, 5, 1250.00, '2024-01-17', '2025-10-31 23:24:22'),
(4, 'FRN-AUD-004', 'Audio Pro', '321 Rue de la Technologie', 'Tanger', 'Maroc', '+212 539 456 789', '+212 539 456 790', 'M. Youssef Chraibi', 4, 15, 2250.00, '2024-01-18', '2025-10-31 23:24:22'),
(5, 'FRN-GAM-005', 'Gaming Solutions', '654 Avenue des FAR', 'Agadir', 'Maroc', '+212 528 321 654', '+212 528 321 655', 'M. Samir Rami', 5, 25, 2000.00, '2024-01-19', '2025-10-31 23:24:22'),
(6, 'FRN-INF-006', 'InfoTech Distribution', '987 Rue Moulay Ismail', 'Fès', 'Maroc', '+212 535 789 123', '+212 535 789 124', 'Mme. Nadia El Fassi', 6, 30, 3900.00, '2024-01-20', '2025-10-31 23:24:22');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `personne_a_contacter` varchar(100) DEFAULT NULL,
  `commercial` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `client`, `adresse`, `ville`, `pays`, `telephone`, `fax`, `email`, `personne_a_contacter`, `commercial`, `created_at`) VALUES
(1, 'TechnoPlus SARL', '45 Avenue des Champs-Élysées', 'Paris', 'France', '+33123456789', '+33123456780', 'contact@technoplus.fr', 'Pierre Martin', 'Sophie Bernard', '2025-10-31 23:18:16'),
(2, 'Global Solutions Inc', '123 Main Street', 'New York', 'USA', '+12125551234', '+12125551235', 'info@globalsolutions.com', 'John Smith', 'Michael Johnson', '2025-10-31 23:18:16'),
(3, 'Elite Services GmbH', 'Berliner Straße 75', 'Berlin', 'Allemagne', '+493012345678', '+493012345679', 'kontakt@elite-services.de', 'Hans Müller', 'Anna Schmidt', '2025-10-31 23:18:16');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `date_commande` date NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','confirmee','livree','annulee') DEFAULT 'en_attente',
  `mode_paiement` enum('especes','carte','virement','cheque') DEFAULT 'especes',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `id_client`, `date_commande`, `total`, `statut`, `mode_paiement`, `commentaire`, `created_at`) VALUES
(1, 1, '2024-01-18', 1799.98, 'livree', 'virement', 'Commande urgente', '2025-10-31 22:51:43'),
(2, 2, '2024-01-19', 2099.97, 'confirmee', 'carte', 'Client fidèle', '2025-10-31 22:51:43'),
(3, 3, '2024-01-20', 449.97, 'en_attente', 'especes', 'À livrer en main propre', '2025-10-31 22:51:43');

-- --------------------------------------------------------

--
-- Structure de la table `commande_items`
--

CREATE TABLE `commande_items` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_items`
--

INSERT INTO `commande_items` (`id`, `commande_id`, `produit_id`, `quantite`, `prix_unitaire`) VALUES
(1, 1, 1, 2, 899.99),
(2, 2, 2, 3, 699.99),
(3, 3, 4, 3, 149.99);

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

CREATE TABLE `devis` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `date_devis` date NOT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `devis`
--

INSERT INTO `devis` (`id`, `client_id`, `description`, `quantite`, `prix_unitaire`, `total`, `date_devis`, `statut`, `created_at`) VALUES
(1, 1, 'Équipement informatique pour nouveau bureau', 5, 899.99, 4499.95, '2024-01-15', 'en_attente', '2025-10-31 22:51:43'),
(2, 2, 'Smartphones pour équipe commerciale', 10, 699.99, 6999.90, '2024-01-16', 'accepte', '2025-10-31 22:51:43'),
(3, 4, 'Matériel bureautique complet', 3, 299.99, 899.97, '2024-01-17', 'refuse', '2025-10-31 22:51:43');

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

CREATE TABLE `factures` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `date_facture` date NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` enum('impayee','payee','partiellement_payee') DEFAULT 'impayee',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`id`, `client_id`, `date_facture`, `total`, `statut`, `note`, `created_at`) VALUES
(1, 1, '2024-01-18', 1799.98, 'payee', 'Facture 2024-001', '2025-10-31 22:51:43'),
(2, 2, '2024-01-19', 2099.97, 'partiellement_payee', 'Facture 2024-002 - Acompte reçu', '2025-10-31 22:51:43'),
(3, 3, '2024-01-20', 449.97, '', 'Facture 2024-003 - En attente de paiement', '2025-10-31 22:51:43');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `facture_id` int(11) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `mode_paiement` enum('especes','carte','virement','cheque') DEFAULT 'especes',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `facture_id`, `montant`, `date_paiement`, `mode_paiement`, `note`, `created_at`) VALUES
(1, 1, 1799.98, '2024-01-18', 'virement', 'Paiement complet', '2025-10-31 22:51:43'),
(2, 2, 1000.00, '2024-01-19', 'carte', 'Acompte', '2025-10-31 22:51:43'),
(3, 2, 1099.97, '2024-01-25', 'virement', 'Solde', '2025-10-31 22:51:43'),
(4, 3, 300.00, '2025-11-01', 'virement', '', '2025-10-31 23:49:11');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `categorie` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `prix`, `stock`, `categorie`, `description`, `created_at`) VALUES
(1, 'Ordinateur Portable Dell', 899.99, 5, 'Informatique', 'Ordinateur portable 15 pouces, 8GB RAM, 512GB SSD', '2025-10-31 22:51:42'),
(2, 'Smartphone Samsung Galaxy', 699.99, 25, 'Téléphonie', 'Smartphone Android 128GB, écran 6.5 pouces', '2025-10-31 22:51:42'),
(3, 'Imprimante HP LaserJet', 299.99, 10, 'Bureautique', 'Imprimante laser monochrome, WiFi', '2025-10-31 22:51:42'),
(4, 'Casque Audio Sony', 149.99, 30, 'Audio', 'Casque sans fil avec réduction de bruit', '2025-10-31 22:51:42'),
(5, 'Souris Gaming Logitech', 79.99, 40, 'Informatique', 'Souris gaming RGB, 16000 DPI', '2025-10-31 22:51:42'),
(6, 'Clavier Mécanique', 129.99, 20, 'Informatique', 'Clavier mécanique rétroéclairé', '2025-10-31 22:51:42');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','vendeur','gestionnaire') DEFAULT 'vendeur',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', 'motdepasse123', 'admin@commerce.com', 'admin', '2025-10-31 22:51:42');

-- --------------------------------------------------------

--
-- Structure de la table `ventes`
--

CREATE TABLE `ventes` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_total` decimal(10,2) NOT NULL,
  `date_vente` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ventes`
--

INSERT INTO `ventes` (`id`, `client_id`, `produit_id`, `quantite`, `prix_total`, `date_vente`, `created_at`) VALUES
(1, 1, 1, 2, 1799.98, '2024-01-18', '2025-10-31 22:51:42'),
(2, 2, 2, 3, 2099.97, '2024-01-19', '2025-10-31 22:51:42'),
(3, 3, 4, 1, 149.99, '2024-01-20', '2025-10-31 22:51:42');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `achats`
--
ALTER TABLE `achats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_fournisseur` (`code_fournisseur`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_client` (`id_client`);

--
-- Index pour la table `commande_items`
--
ALTER TABLE `commande_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `devis`
--
ALTER TABLE `devis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Index pour la table `factures`
--
ALTER TABLE `factures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facture_id` (`facture_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `achats`
--
ALTER TABLE `achats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `commande_items`
--
ALTER TABLE `commande_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `devis`
--
ALTER TABLE `devis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `factures`
--
ALTER TABLE `factures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `ventes`
--
ALTER TABLE `ventes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `achats`
--
ALTER TABLE `achats`
  ADD CONSTRAINT `achats_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id`);

--
-- Contraintes pour la table `commande_items`
--
ALTER TABLE `commande_items`
  ADD CONSTRAINT `commande_items_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`),
  ADD CONSTRAINT `commande_items_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

--
-- Contraintes pour la table `devis`
--
ALTER TABLE `devis`
  ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Contraintes pour la table `factures`
--
ALTER TABLE `factures`
  ADD CONSTRAINT `factures_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`);

--
-- Contraintes pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD CONSTRAINT `ventes_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `ventes_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
