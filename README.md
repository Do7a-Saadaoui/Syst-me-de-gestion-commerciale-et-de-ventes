# 🛒 Système de Gestion Commerciale et de Vente

## 📌 Description
Application web développée en **PHP** et **MySQL** pour aider les entreprises à gérer leurs ventes, clients, produits, achats et rapports.  

Le projet inclut un **tableau de bord centralisé** et un module de **rapports statistiques avancés** permettant une analyse détaillée des performances commerciales.

---

## 🚀 Fonctionnalités principales

### 🔐 Module d'Authentification (Login)
- Connexion sécurisée pour utilisateurs/admins
- Sessions PHP pour protéger les pages sensibles
- Déconnexion sécurisée (Logout)

### 👥 Gestion des clients
- Ajouter, modifier, supprimer et consulter les clients
- Historique des transactions pour chaque client
- Recherche et filtrage avancé
- Statistiques : **Clients les plus fidèles**

### 📦 Gestion des produits
- CRUD complet pour les produits
- Gestion du stock en temps réel
- Statistiques : **Produits les plus vendus**
- Alertes de stock faible

### 🛍️ Gestion des ventes et achats
- Ajouter, modifier, supprimer ventes et achats
- Calcul automatique des totaux
- Historique des transactions
- Gestion des factures liées aux ventes/achats
- Suivi des paiements : payé / non payé / partiel

### 📊 Tableau de bord & Rapports statistiques
- Bouton **"Rapport statistique"** dans le menu pour accéder aux statistiques avancées
- Visualisation des données commerciales :
  - Produits les plus vendus
  - Clients les plus fidèles
  - Chiffre d’affaires total, actif, moyen
- Exportation des rapports (PDF ou Excel) pour analyse externe
- Graphiques interactifs pour faciliter la lecture des données

### 💾 Exportation des rapports
- Export des données clients, ventes, achats et factures
- Génération de rapports PDF ou Excel
- Utilisation facile pour analyse interne ou présentation

---

## 🛠️ Technologies utilisées

- PHP (Backend)  
- MySQL (Base de données)  
- HTML5 / CSS3 / Bootstrap (Frontend)  
- JavaScript (Validation et interactions)  
- Sessions PHP pour sécurité

---

## 🗄️ Base de données

Le fichier `DATABASE.sql` est inclus dans le projet.

### Installation :
1. Ouvrir phpMyAdmin
2. Créer une nouvelle base de données
3. Importer `DATABASE.sql`
4. Configurer `config.php` (host, user, password, db_name)

---

## ⚙️ Installation du projet

1. Cloner le repository :
   ```bash
   git clone https://github.com/Do7a-Saadaoui/Syst-me-de-gestion-commerciale-et-de-ventes.git
   ```
2. Placer le dossier dans `htdocs` (XAMPP)
3. Démarrer Apache et MySQL
4. Ouvrir le navigateur :
   ```
   http://localhost/Syst-me-de-gestion-commerciale-et-de-ventes
   ```

---

## 🎯 Objectifs

- Développer un projet complet PHP avec base de données  
- Implémenter **CRUD clients, produits, ventes, achats, factures**  
- Créer un **tableau de bord dynamique** avec statistiques avancées  
- Fournir un module **Rapport statistique** avec export PDF/Excel  
- Mettre en pratique la gestion de sessions et sécurité

---

## 👩‍💻 Auteur

**Doha Saadaoui**  
- Portfolio GitHub : [ https://do7a-saadaoui.github.io/my-portfolio/my-portfolio.html]
- Email : saadaouidoha18@gmail.com
