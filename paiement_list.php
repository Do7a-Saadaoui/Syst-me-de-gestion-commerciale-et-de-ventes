<?php
$adminPage = true; 
include 'check.php';
?><?php 

// 🔹 Connexion base de données (PDO)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 🔹 Vérification session
if(!isset($_SESSION['USER_ID'])) { 
    header("location:login.php"); 
    exit; 
}  

// 🔹 Récupération des paiements - CORRECTION: Utiliser 'client' au lieu de 'nom'
$sql = "
    SELECT p.*, f.total AS facture_total, c.client AS client_nom
    FROM paiements p
    JOIN factures f ON p.facture_id = f.id
    JOIN clients c ON f.client_id = c.id
    ORDER BY p.date_paiement DESC
";
$paiements = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Gestion des actions
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM paiements WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: paiement_list.php?success=Paiement+supprimé+avec+succès");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiements - Gestion Commerciale</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: #f5f6fa;
    margin:0;
}

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
h1, h2 { font-weight:700; color:#333; }

.card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.table th {
    background-color:#667eea;
    color:white;
    border: none;
}

.badge-statut {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

@media(max-width:768px){
    .container-dashboard { flex-direction:column; }
    .sidebar { width:100%; display:flex; overflow-x:auto; }
    .nav-link { flex:1; text-align:center; }
}
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
        <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
        <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
        <a href="facture_list.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Factures</a>
        <a href="paiement_list.php" class="nav-link active"><i class="bi bi-cash-coin me-2"></i> Paiements</a>
        <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Liste des Paiements</h2>
            <a href="paiement_add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nouveau Paiement
            </a>
        </div>

        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= urldecode($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Facture #</th>
                            <th>Client</th>
                            <th>Montant (MAD)</th>
                            <th>Date Paiement</th>
                            <th>Mode Paiement</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($paiements): ?>
                            <?php foreach($paiements as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td>
                                        <span class="badge bg-secondary">#<?= $p['facture_id'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($p['client_nom']) ?></td>
                                    <td class="fw-bold text-success"><?= number_format($p['montant'], 2, ',', ' ') ?> MAD</td>
                                    <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($p['mode_paiement'] ?: 'Non spécifié') ?></span>
                                    </td>
                                    <td>
                                        <?php if($p['note']): ?>
                                            <small class="text-muted"><?= htmlspecialchars(substr($p['note'], 0, 30)) ?><?= strlen($p['note']) > 30 ? '...' : '' ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="paiement_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="paiement_list.php?action=delete&id=<?= $p['id'] ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Supprimer ce paiement ?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    Aucun paiement trouvé
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistiques des paiements -->
        <?php if($paiements): ?>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Total Paiements</h5>
                        <h3 class="text-success">
                            <?= number_format(array_sum(array_column($paiements, 'montant')), 2, ',', ' ') ?> MAD
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Nombre de Paiements</h5>
                        <h3 class="text-info"><?= count($paiements) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Dernier Paiement</h5>
                        <h6 class="text-muted">
                            <?= date('d/m/Y', strtotime($paiements[0]['date_paiement'])) ?>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>