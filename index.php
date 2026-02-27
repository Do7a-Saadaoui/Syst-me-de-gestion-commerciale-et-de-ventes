<?php
$adminPage = true; 
include 'check.php';
?>
<?php
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

require_once "db.php"; // <-- ça définit $pdo
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion Commerciale</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f6fa;
        margin: 0;
    }

    .header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .logo {
        font-size: 1.6rem;
        font-weight: 700;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .logout-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        transition: 0.3s;
    }

    .logout-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .container-dashboard {
        display: flex;
        min-height: calc(100vh - 70px);
    }

    .sidebar {
        width: 250px;
        background: white;
        padding: 2rem 1rem;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #333;
        text-decoration: none;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        font-weight: 500;
        transition: 0.2s;
    }

    .nav-link.active,
    .nav-link:hover {
        background-color: #667eea;
        color: white;
    }

    .main-content {
        flex: 1;
        padding: 2rem;
    }

    h1 {
        font-weight: 700;
        margin-bottom: 2rem;
        color: #333;
    }

    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .card h3 {
        color: #667eea;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }

    .card .number {
        font-size: 2rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.3rem;
    }

    .card p {
        color: #666;
        font-size: 0.95rem;
        text-align: center;
    }

    .recent-activity {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .recent-activity h2 {
        color: #333;
        margin-bottom: 1rem;
        border-bottom: 2px solid #667eea;
        padding-bottom: 0.5rem;
    }

    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        padding: 0.8rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #eee;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item span.badge {
        background: #667eea;
        color: white;
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
        font-size: 0.85rem;
    }

    @media(max-width:768px) {
        .container-dashboard {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            display: flex;
            overflow-x: auto;
        }

        .nav-link {
            flex: 1;
            text-align: center;
        }
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
            <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Tableau de Bord</a>
            <a href="clients.php" class="nav-link"><i class="bi bi-people me-2"></i>Gestion Clients</a>
            <a href="produits.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Produits</a>
            <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
            <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i>Achats</a>
            <a href="facture_list.php" class="nav-link"><i class="bi bi-receipt"></i>Factures</a>
            <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>

            <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
            <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i>Utilisateurs</a>
            <?php endif; ?>
        </div>


        <div class="main-content">
            <h1>Tableau de Bord</h1>

            <?php
        // Statistiques principales
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
            $totalClients = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM produits");
            $totalProduits = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $pdo->query("SELECT SUM(prix_total) as ca FROM ventes WHERE MONTH(date_vente)=MONTH(CURDATE()) AND YEAR(date_vente)=YEAR(CURDATE())");
            $chiffreAffaires = $stmt->fetch(PDO::FETCH_ASSOC)['ca'] ?? 0;

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM ventes WHERE MONTH(date_vente)=MONTH(CURDATE()) AND YEAR(date_vente)=YEAR(CURDATE())");
            $ventesMois = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        } catch (Exception $e) {
            echo '<div class="error-message">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $totalClients = $totalProduits = $chiffreAffaires = $ventesMois = 0;
        }
        ?>

            <div class="dashboard-cards">
                <div class="card">
                    <h3>Clients</h3>
                    <div class="number"><?php echo $totalClients; ?></div>
                    <p>Total clients</p>
                </div>
                <div class="card">
                    <h3>Produits</h3>
                    <div class="number"><?php echo $totalProduits; ?></div>
                    <p>En stock</p>
                </div>
                <div class="card">
                    <h3>Chiffre d'Affaires</h3>
                    <div class="number"><?php echo number_format($chiffreAffaires,2,',',' '); ?> MAD</div>
                    <p>Ce mois</p>
                </div>
                <div class="card">
                    <h3>Ventes</h3>
                    <div class="number"><?php echo $ventesMois; ?></div>
                    <p>Ce mois</p>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Activité Récente</h2>
                <ul class="activity-list">
                    <?php
                try {
                    $stmt = $pdo->query("
                        SELECT v.*, c.nom as client_nom
                        FROM ventes v
                        LEFT JOIN clients c ON v.client_id = c.id
                        ORDER BY v.date_vente DESC
                        LIMIT 5
                    ");
                    $ventesRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if($ventesRecentes){
                        foreach($ventesRecentes as $vente){
                            echo "<li class='activity-item'>";
                            echo "<span><strong>Vente #".($vente['id'] ?? 'N/A')."</strong> - ".($vente['client_nom'] ?? 'Client inconnu')."</span>";
                            echo "<span>".number_format($vente['prix_total'] ?? 0,2,',',' ')." MAD</span>";
                            echo "<span>".date('d/m/Y', strtotime($vente['date_vente'] ?? date('Y-m-d')))."</span>";
                            echo "</li>";
                        }
                    } else {
                        echo "<li class='activity-item'>Aucune vente récente</li>";
                    }
                } catch (Exception $e){
                    echo "<li class='activity-item'>Erreur de chargement des activités</li>";
                }
                ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>