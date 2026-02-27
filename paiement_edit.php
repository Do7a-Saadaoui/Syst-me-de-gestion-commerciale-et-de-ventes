<?php
$adminPage = true; 
include 'check.php';

// 🔹 Connexion base de données
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

// 🔹 Récupérer le paiement à modifier
$paiement_id = $_GET['id'] ?? null;
if (!$paiement_id) {
    header("Location: paiement_list.php?error=ID+paiement+non+spécifié");
    exit;
}

// 🔹 Récupérer les données du paiement
$stmt = $pdo->prepare("
    SELECT p.*, f.id as facture_id, f.total as facture_total, c.client as client_nom
    FROM paiements p
    JOIN factures f ON p.facture_id = f.id
    JOIN clients c ON f.client_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$paiement_id]);
$paiement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paiement) {
    header("Location: paiement_list.php?error=Paiement+non+trouvé");
    exit;
}

// 🔹 Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = floatval($_POST['montant']);
    $date_paiement = $_POST['date_paiement'];
    $mode_paiement = $_POST['mode_paiement'];
    $note = $_POST['note'] ?? null;

    // Validation du montant
    if ($montant <= 0) {
        $error = "Le montant doit être supérieur à 0";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Mettre à jour le paiement
            $stmt = $pdo->prepare("
                UPDATE paiements 
                SET montant = ?, date_paiement = ?, mode_paiement = ?, note = ?
                WHERE id = ?
            ");
            $stmt->execute([$montant, $date_paiement, $mode_paiement, $note, $paiement_id]);
            
            // Recalculer le statut de la facture
            $stmt = $pdo->prepare("
                SELECT IFNULL(SUM(montant), 0) as total_paye 
                FROM paiements 
                WHERE facture_id = ?
            ");
            $stmt->execute([$paiement['facture_id']]);
            $total_paye = $stmt->fetchColumn();
            
            $statut = 'Non Payée';
            if ($total_paye >= $paiement['facture_total']) {
                $statut = 'Payée';
            } elseif ($total_paye > 0) {
                $statut = 'Partiellement Payée';
            }
            
            // Mettre à jour le statut de la facture
            $stmt = $pdo->prepare("UPDATE factures SET statut = ? WHERE id = ?");
            $stmt->execute([$statut, $paiement['facture_id']]);
            
            $pdo->commit();
            
            header("Location: paiement_list.php?success=Paiement+modifié+avec+succès");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier Paiement - Gestion Commerciale</title>
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
.card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.info-card {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
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
            <h2 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Modifier Paiement</h2>
            <a href="paiement_list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Retour
            </a>
        </div>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <!-- Informations sur la facture -->
            <div class="info-card">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Facture #:</strong> <?= $paiement['facture_id'] ?></p>
                        <p><strong>Client:</strong> <?= htmlspecialchars($paiement['client_nom']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Facture:</strong> <?= number_format($paiement['facture_total'], 2, ',', ' ') ?> MAD</p>
                        <p><strong>Date création:</strong> <?= date('d/m/Y', strtotime($paiement['date_paiement'])) ?></p>
                    </div>
                </div>
            </div>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Montant (MAD) *</label>
                        <input type="number" step="0.01" class="form-control" name="montant" 
                               value="<?= htmlspecialchars($paiement['montant']) ?>" required>
                        <div class="form-text">Montant actuel du paiement</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date de Paiement *</label>
                        <input type="date" class="form-control" name="date_paiement" 
                               value="<?= htmlspecialchars($paiement['date_paiement']) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mode de Paiement *</label>
                        <select class="form-control" name="mode_paiement" required>
                            <option value="Espèces" <?= $paiement['mode_paiement'] == 'Espèces' ? 'selected' : '' ?>>Espèces</option>
                            <option value="Virement" <?= $paiement['mode_paiement'] == 'Virement' ? 'selected' : '' ?>>Virement</option>
                            <option value="Carte Bancaire" <?= $paiement['mode_paiement'] == 'Carte Bancaire' ? 'selected' : '' ?>>Carte Bancaire</option>
                            <option value="Chèque" <?= $paiement['mode_paiement'] == 'Chèque' ? 'selected' : '' ?>>Chèque</option>
                            <option value="PayPal" <?= $paiement['mode_paiement'] == 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                            <option value="Autre" <?= $paiement['mode_paiement'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Note (Optionnel)</label>
                        <textarea class="form-control" name="note" rows="3" 
                                  placeholder="Référence, détails du paiement..."><?= htmlspecialchars($paiement['note'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="paiement_list.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i> Modifier le Paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>