<?php
$adminPage = true; 
include 'check.php';
?>
<?php

// 1) Charger le fichier de connexion si présent (il doit définir $pdo)
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

// 2) Si $pdo n'est pas défini (fallback), créer une connexion PDO basique
if (!isset($pdo) || !$pdo instanceof PDO) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
    }
}

// 3) Vérification session
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit;
}

// 4) Récupérer les factures (et info client) - CORRIGÉ avec la table clients
try {
    $sql = "
        SELECT f.*, c.client AS client_nom,
               (SELECT IFNULL(SUM(p.montant),0) FROM paiements p WHERE p.facture_id = f.id) AS total_paye
        FROM factures f
        LEFT JOIN clients c ON f.client_id = c.id
        ORDER BY f.date_facture DESC, f.id DESC
    ";
    $factures = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $factures = [];
    $errorMsg = $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Factures - Gestion Commerciale</title>
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
    .table th { background:#667eea; color:#fff; }
    h2 { font-weight:700; color:#333; }

  </style>
</head>
<body>

  <div class="header">
    <div class="logo">Gestion Commerciale</div>
    <div class="user-info">
      <span style="color:#fff; margin-right:1rem;">Bienvenue, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></span>
      <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
  </div>

  <div class="container-dashboard">
    <div class="sidebar">
      <h6 class="text-center text-primary mb-3">Menu</h6>
      <a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i> Tableau de Bord</a>
      <a href="clients.php" class="nav-link"><i class="bi bi-people"></i>Gestion Clients</a>
      <a href="produits.php" class="nav-link"><i class="bi bi-box-seam"></i> Produits</a>
      <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check"></i> Ventes</a>
      <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
      <a href="facture_list.php" class="nav-link active"><i class="bi bi-receipt"></i> Factures</a>
      <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
      <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Rapports</a>
      <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill"></i> Utilisateurs</a>
    </div>

    <div class="main-content">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-4"><i class="bi bi-receipt"></i> Liste des factures</h2>
        <div>
          <a href="facture_add.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nouvelle facture</a>
          <a href="paiement_list.php" class="btn btn-outline-primary ms-2"><i class="bi bi-cash-coin"></i> Paiements</a>
        </div>
      </div>

      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger">Erreur: <?php echo htmlspecialchars($errorMsg); ?></div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Date</th>
              <th>Total (MAD)</th>
              <th>Payé (MAD)</th>
              <th>Reste (MAD)</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($factures): ?>
              <?php foreach ($factures as $f): 
                $total = floatval($f['total'] ?? 0);
                $paye = floatval($f['total_paye'] ?? 0);
                $reste = $total - $paye;
                
                // Déterminer le statut
                if ($reste <= 0) {
                    $statut = 'Payée';
                    $badge_class = 'bg-success';
                } else if ($paye > 0) {
                    $statut = 'Partiellement Payée';
                    $badge_class = 'bg-warning';
                } else {
                    $statut = 'Non Payée';
                    $badge_class = 'bg-danger';
                }
              ?>
                <tr>
                  <td><?php echo $f['id']; ?></td>
                  <td><?php echo htmlspecialchars($f['client_nom'] ?? '—'); ?></td>
                  <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($f['date_facture'] ?? date('Y-m-d')))); ?></td>
                  <td><?php echo number_format($total, 2, ',', ' '); ?></td>
                  <td><?php echo number_format($paye, 2, ',', ' '); ?></td>
                  <td><?php echo number_format($reste, 2, ',', ' '); ?></td>
                  <td>
                    <span class="badge <?php echo $badge_class; ?>"><?php echo $statut; ?></span>
                  </td>
                  <td>
                    <a href="facture_view.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-info" title="Voir"><i class="bi bi-eye"></i></a>
                    <a href="paiement_add.php?facture_id=<?php echo $f['id']; ?>" class="btn btn-sm btn-primary" title="Ajouter Paiement"><i class="bi bi-cash"></i></a>
                    <a href="facture_edit.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted">Aucune facture trouvée</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>