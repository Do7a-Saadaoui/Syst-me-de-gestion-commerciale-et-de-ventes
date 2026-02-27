<?php
$adminPage = true; 
include 'check.php';
?><?php

// 1) Charger le fichier de connexion s’il existe
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

// 2) Si $pdo n’existe pas encore, créer la connexion PDO
if (!isset($pdo) || !$pdo instanceof PDO) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
    }
}

// 3) Vérification de la session
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit;
}

// 4) Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);
    $total = floatval($_POST['total']);
    $statut = trim($_POST['statut']);
    $note = trim($_POST['note'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO factures (client_id, total, statut, note, date_facture) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$client_id, $total, $statut, $note]);

        header('Location: facture_list.php'); // ✅ nom corrigé
        exit;
    } catch (Exception $e) {
        $errorMsg = "Erreur lors de l’enregistrement : " . htmlspecialchars($e->getMessage());
    }
}

// 5) Récupération des clients
try {
    $clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clients = [];
    $errorMsg = "Erreur lors du chargement des clients : " . htmlspecialchars($e->getMessage());
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Ajouter Facture</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body { font-family: 'Inter', sans-serif; background:#f5f6fa; margin:0; }
.header {
    background: linear-gradient(135deg,#667eea,#764ba2);
    color:white;
    padding:1rem 2rem;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

.logo { font-size:1.6rem; font-weight:700; }
.user-info { display:flex; align-items:center; gap:1rem; }
.logout-btn {
    background: rgba(255,255,255,0.2);
    color:white;
    border:1px solid rgba(255,255,255,0.3);
    padding:0.5rem 1rem;
    border-radius:6px;
    text-decoration:none;
    transition:0.3s;
}
.logout-btn:hover { background: rgba(255,255,255,0.35); }

.container-dashboard { display:flex; min-height:calc(100vh - 70px); }
.sidebar {
    width:250px;
    background:white;
    padding:2rem 1rem;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}
.nav-link {
    display:flex;
    align-items:center;
    gap:0.5rem;
    color:#333;
    text-decoration:none;
    padding:0.75rem 1rem;
    border-radius:8px;
    margin-bottom:0.5rem;
    font-weight:500;
    transition:0.2s;
}
.nav-link.active, .nav-link:hover { background-color:#667eea; color:white; }
.main-content { flex:1; padding:2rem; }

  </style>
</head>
<body>
  <div class="header">
    <div class="logo">Gestion Commerciale</div>
    <div class="user-info">
        <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></span>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
</div>

<div class="container-dashboard">
    <div class="sidebar">
        <h5 class="text-center mb-3 text-primary">Menu</h5>
        <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Tableau de Bord</a>
        <a href="clients.php" class="nav-link"><i class="bi bi-people me-2"></i> Gestion Clients</a>
        <a href="produits.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Produits</a>
        <a href="ventes.php" class="nav-link "><i class="bi bi-cart-check me-2"></i> Ventes</a>
        <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
        <a href="facture_list.php" class="nav-link active "><i class="bi bi-receipt"></i> Factures</a>
        <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
        <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
    </div>


  <div class="main-content">
    <h3 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Ajouter une nouvelle facture</h3>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger"><?= $errorMsg ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Client</label>
        <select name="client_id" class="form-control" required>
          <option value="">-- Sélectionner un client --</option>
          <?php foreach($clients as $c): ?>
            <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Total (MAD)</label>
        <input type="number" step="0.01" name="total" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-control">
          <option value="Non Payée">Non Payée</option>
          <option value="Partiellement Payée">Partiellement Payée</option>
          <option value="Payée">Payée</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Note</label>
        <textarea name="note" class="form-control" rows="3" placeholder="Ajouter une remarque (optionnel)"></textarea>
      </div>

      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save" ></i>Enregistrer</button>
        <a href="facture_list.php" class="btn btn-secondary"><i class="bi bi-x-circle" ></i>Annuler</a>
      </div>
    </form>
  </div>

</body>
</html>
