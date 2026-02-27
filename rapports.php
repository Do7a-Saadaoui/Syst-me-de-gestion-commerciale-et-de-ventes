<?php
// Vérifier si la session est déjà active avant de démarrer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['USER_ID']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

// 🔹 Connexion base de données (PDO)
try {
    $db = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 🔹 Statistiques pour le dashboard - CORRECTIONS: Utiliser 'client' au lieu de 'nom'
$query_ventes_mois = "SELECT MONTH(date_vente) as mois, SUM(prix_total) as total 
                     FROM ventes 
                     WHERE YEAR(date_vente) = YEAR(CURDATE()) 
                     GROUP BY MONTH(date_vente) 
                     ORDER BY mois";
$stmt_ventes_mois = $db->prepare($query_ventes_mois);
$stmt_ventes_mois->execute();
$ventes_par_mois = $stmt_ventes_mois->fetchAll(PDO::FETCH_ASSOC);

$query_produits_populaires = "SELECT p.nom, SUM(v.quantite) as total_vendu 
                             FROM ventes v 
                             JOIN produits p ON v.produit_id = p.id 
                             GROUP BY p.id 
                             ORDER BY total_vendu DESC 
                             LIMIT 5";
$stmt_produits_populaires = $db->prepare($query_produits_populaires);
$stmt_produits_populaires->execute();
$produits_populaires = $stmt_produits_populaires->fetchAll(PDO::FETCH_ASSOC);

// CORRECTION: Utiliser c.client au lieu de c.nom
$query_clients_fideles = "SELECT c.client, SUM(v.prix_total) as total_achats, COUNT(v.id) as nb_commandes 
                         FROM ventes v 
                         JOIN clients c ON v.client_id = c.id 
                         GROUP BY c.id 
                         ORDER BY total_achats DESC 
                         LIMIT 5";
$stmt_clients_fideles = $db->prepare($query_clients_fideles);
$stmt_clients_fideles->execute();
$clients_fideles = $stmt_clients_fideles->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Statistiques supplémentaires
$query_ca_mois = "SELECT SUM(prix_total) as ca FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE())";
$stmt_ca = $db->prepare($query_ca_mois);
$stmt_ca->execute();
$ca = $stmt_ca->fetch(PDO::FETCH_ASSOC)['ca'] ?? 0;

$query_ventes_count = "SELECT COUNT(*) as total FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE())";
$stmt_count = $db->prepare($query_ventes_count);
$stmt_count->execute();
$ventes_count = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// CORRECTION: Utiliser c.client au lieu de c.nom
$query_clients_actifs = "SELECT COUNT(DISTINCT v.client_id) as total 
                        FROM ventes v 
                        JOIN clients c ON v.client_id = c.id 
                        WHERE MONTH(v.date_vente) = MONTH(CURDATE())";
$stmt_clients = $db->prepare($query_clients_actifs);
$stmt_clients->execute();
$clients_actifs = $stmt_clients->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$query_moyenne_panier = "SELECT AVG(prix_total) as moyenne FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE())";
$stmt_moyenne = $db->prepare($query_moyenne_panier);
$stmt_moyenne->execute();
$moyenne_panier = $stmt_moyenne->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rapports - Gestion Commerciale</title>
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
h1,h2,h3,h4 { font-weight:700; color:#333; }

.card {
    background:white;
    padding:1.5rem;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%;
}
.card:hover { transform: translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,0.12); }

.table thead { background:#667eea; color:white; }

.stat-card {
    text-align: center;
    padding: 1.5rem;
    border-radius: 10px;
    color: white;
    margin-bottom: 1rem;
}
.stat-card .card-value {
    font-size: 2rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.bg-custom-primary { background: linear-gradient(135deg, #667eea, #764ba2); }
.bg-custom-success { background: linear-gradient(135deg, #4CAF50, #45a049); }
.bg-custom-info { background: linear-gradient(135deg, #2196F3, #1976D2); }
.bg-custom-warning { background: linear-gradient(135deg, #FF9800, #F57C00); }

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
        <a href="facture_list.php" class="nav-link"><i class="bi bi-receipt"></i> Factures</a>
        <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
        <a href="rapports.php" class="nav-link active"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
    </div>

    <div class="main-content">
        <h2 class="mb-4"><i class="bi bi-bar-chart-line me-2"></i>Rapports et Statistiques</h2>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-custom-primary">
                    <h5 class="card-title">Chiffre d'Affaires</h5>
                    <h2 class="card-value"><?= number_format($ca, 2) ?> MAD</h2>
                    <small>Ce mois</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-custom-success">
                    <h5 class="card-title">Ventes du Mois</h5>
                    <h2 class="card-value"><?= $ventes_count ?></h2>
                    <small>Transactions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-custom-info">
                    <h5 class="card-title">Clients Actifs</h5>
                    <h2 class="card-value"><?= $clients_actifs ?></h2>
                    <small>Ce mois</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-custom-warning">
                    <h5 class="card-title">Moyenne Panier</h5>
                    <h2 class="card-value"><?= number_format($moyenne_panier, 2) ?> MAD</h2>
                    <small>Par commande</small>
                </div>
            </div>
        </div>

        <!-- Produits les plus vendus et Clients fidèles -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-trophy me-2"></i>Produits les Plus Vendus</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-end">Quantité Vendue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($produits_populaires as $produit): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                                            <td class="text-end fw-bold text-success"><?php echo $produit['total_vendu']; ?> unités</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-star me-2"></i>Clients les Plus Fidèles</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-end">Total Achats</th>
                                        <th class="text-end">Commandes</th>
                                        <th class="text-end">Moyenne</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($clients_fideles as $client): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($client['client']); ?></td>
                                            <td class="text-end fw-bold text-primary"><?php echo number_format($client['total_achats'], 2); ?> MAD</td>
                                            <td class="text-end"><?php echo $client['nb_commandes']; ?></td>
                                            <td class="text-end fw-bold"><?php echo number_format($client['total_achats'] / $client['nb_commandes'], 2); ?> MAD</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Import/Export -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-download me-2"></i>Exportation des Rapports</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success mb-3"><i class="bi bi-file-earmark-excel me-2"></i>Format Excel</h6>
                                <div class="btn-group-vertical w-100 mb-3">
                                    <a href="export_excel.php?type=ventes" class="btn btn-outline-success text-start">
                                        <i class="bi bi-file-earmark-excel me-2"></i> Rapport des Ventes
                                    </a>
                                    <a href="export_excel.php?type=clients" class="btn btn-outline-success text-start">
                                        <i class="bi bi-file-earmark-excel me-2"></i> Liste des Clients
                                    </a>
                                    <a href="export_excel.php?type=produits" class="btn btn-outline-success text-start">
                                        <i class="bi bi-file-earmark-excel me-2"></i> Catalogue Produits
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-danger mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Format PDF</h6>
                                <div class="btn-group-vertical w-100">
                                    <a href="export_pdf.php?type=ventes" class="btn btn-outline-danger text-start">
                                        <i class="bi bi-file-earmark-pdf me-2"></i> Rapport des Ventes
                                    </a>
                                    <a href="export_pdf.php?type=clients" class="btn btn-outline-danger text-start">
                                        <i class="bi bi-file-earmark-pdf me-2"></i> Liste des Clients
                                    </a>
                                    <a href="export_pdf.php?type=produits" class="btn btn-outline-danger text-start">
                                        <i class="bi bi-file-earmark-pdf me-2"></i> Catalogue Produits
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>