<?php
$adminPage = true; 
include 'check.php';
?><?php 

try {
    $db = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
} 

if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'add') {
        $client_id = $_POST['client_id'];
        $produit_id = $_POST['produit_id'];
        $quantite = $_POST['quantite'];
        $prix_total = $_POST['prix_total'];
        $date_vente = $_POST['date_vente'];
        
        $query_stock = "SELECT stock FROM produits WHERE id = :produit_id";
        $stmt_stock = $db->prepare($query_stock);
        $stmt_stock->bindParam(":produit_id", $produit_id);
        $stmt_stock->execute();
        $stock = $stmt_stock->fetch(PDO::FETCH_ASSOC)['stock'];
        
        if($stock < $quantite) {
            header("Location: ventes.php?error=Stock insuffisant");
            exit();
        }
        
        $query = "INSERT INTO ventes SET client_id=:client_id, produit_id=:produit_id, quantite=:quantite, prix_total=:prix_total, date_vente=:date_vente";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":client_id", $client_id);
        $stmt->bindParam(":produit_id", $produit_id);
        $stmt->bindParam(":quantite", $quantite);
        $stmt->bindParam(":prix_total", $prix_total);
        $stmt->bindParam(":date_vente", $date_vente);
        
        if($stmt->execute()) {
            $query_update = "UPDATE produits SET stock = stock - :quantite WHERE id = :produit_id";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->bindParam(":quantite", $quantite);
            $stmt_update->bindParam(":produit_id", $produit_id);
            $stmt_update->execute();
            
            header("Location: ventes.php?success=Vente ajoutée avec succès");
            exit();
        }
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer la quantité avant suppression pour réajuster le stock
    $query_select = "SELECT produit_id, quantite FROM ventes WHERE id=:id";
    $stmt_select = $db->prepare($query_select);
    $stmt_select->bindParam(":id", $id);
    $stmt_select->execute();
    $vente = $stmt_select->fetch(PDO::FETCH_ASSOC);
    
    $query = "DELETE FROM ventes WHERE id=:id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $id);
    
    if($stmt->execute()) {
        // Réajuster le stock du produit
        if($vente) {
            $query_update = "UPDATE produits SET stock = stock + :quantite WHERE id = :produit_id";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->bindParam(":quantite", $vente['quantite']);
            $stmt_update->bindParam(":produit_id", $vente['produit_id']);
            $stmt_update->execute();
        }
        
        header("Location: ventes.php?success=Vente supprimée avec succès");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des Ventes - Tableau de Bord</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
}
.card:hover { transform: translateY(-5px); box-shadow:0 8px 20px rgba(0,0,0,0.15); }

.table thead { background:#667eea; color:white; }

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
        <a href="ventes.php" class="nav-link active"><i class="bi bi-cart-check me-2"></i> Ventes</a>
        <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
        <a href="facture_list.php" class="nav-link "><i class="bi bi-receipt"></i> Factures</a>
        <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
        <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
    </div>

    <div class="main-content">

        <?php if(isset($_GET['action']) && $_GET['action']=='add'): 
            // CORRECTION: Utiliser 'client' au lieu de 'nom'
            $query_clients = "SELECT id, client FROM clients";
            $stmt_clients = $db->prepare($query_clients);
            $stmt_clients->execute();
            $clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

            $query_produits = "SELECT id, nom, prix, stock FROM produits WHERE stock>0";
            $stmt_produits = $db->prepare($query_produits);
            $stmt_produits->execute();
            $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <h2 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Nouvelle Vente</h2>
        <div class="card p-4">
            <form method="POST" action="ventes.php">
                <input type="hidden" name="action" value="add">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Client *</label>
                        <select class="form-control" name="client_id" required>
                            <option value="">Sélectionner un client</option>
                            <?php foreach($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['client']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Produit *</label>
                        <select class="form-control" name="produit_id" id="produit_id" required onchange="updatePrix()">
                            <option value="">Sélectionner un produit</option>
                            <?php foreach($produits as $produit): ?>
                            <option value="<?php echo $produit['id']; ?>" data-prix="<?php echo $produit['prix']; ?>" data-stock="<?php echo $produit['stock']; ?>">
                                <?php echo htmlspecialchars($produit['nom']); ?> - <?php echo number_format($produit['prix'],2); ?> MAD (Stock: <?php echo $produit['stock']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Quantité *</label>
                        <input type="number" class="form-control" name="quantite" id="quantite" required onchange="updatePrix()" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Prix Total (MAD) *</label>
                        <input type="number" step="0.01" class="form-control" name="prix_total" id="prix_total" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date de Vente *</label>
                        <input type="date" class="form-control" name="date_vente" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div id="stock-info" class="alert alert-info" style="display:none;">
                        Stock disponible: <span id="stock-disponible">0</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                <a href="ventes.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Annuler</a>
            </form>
        </div>

        <script>
        function updatePrix() {
            const produitSelect = document.getElementById('produit_id');
            const quantiteInput = document.getElementById('quantite');
            const prixTotalInput = document.getElementById('prix_total');
            const stockInfo = document.getElementById('stock-info');
            const stockDisponible = document.getElementById('stock-disponible');
            
            const selectedOption = produitSelect.options[produitSelect.selectedIndex];
            const prixUnitaire = selectedOption.getAttribute('data-prix') || 0;
            const stock = selectedOption.getAttribute('data-stock') || 0;
            const quantite = quantiteInput.value || 0;
            
            // Mettre à jour le prix total
            prixTotalInput.value = (prixUnitaire * quantite).toFixed(2);
            
            // Afficher les informations de stock
            if (produitSelect.value) {
                stockInfo.style.display = 'block';
                stockDisponible.textContent = stock;
                
                // Vérifier si la quantité dépasse le stock
                if (quantite > stock) {
                    quantiteInput.setCustomValidity('Quantité dépasse le stock disponible');
                    quantiteInput.classList.add('is-invalid');
                } else {
                    quantiteInput.setCustomValidity('');
                    quantiteInput.classList.remove('is-invalid');
                }
            } else {
                stockInfo.style.display = 'none';
            }
        }
        </script>

        <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-cart-check me-2"></i>Gestion des Ventes</h2>
            <a href="ventes.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nouvelle Vente</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_GET['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_GET['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card p-3">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // CORRECTION: Utiliser 'client' au lieu de 'nom'
                    $query = "SELECT v.*, c.client as client_nom, p.nom as produit_nom 
                              FROM ventes v 
                              LEFT JOIN clients c ON v.client_id=c.id 
                              LEFT JOIN produits p ON v.produit_id=p.id 
                              ORDER BY v.id DESC";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($ventes as $vente):
                    ?>
                    <tr>
                        <td><?php echo $vente['id']; ?></td>
                        <td><?php echo htmlspecialchars($vente['client_nom']); ?></td>
                        <td><?php echo htmlspecialchars($vente['produit_nom']); ?></td>
                        <td><?php echo $vente['quantite']; ?></td>
                        <td class="text-success fw-bold"><?php echo number_format($vente['prix_total'],2); ?> MAD</td>
                        <td><?php echo $vente['date_vente']; ?></td>
                        <td>
                            <a href="ventes.php?action=delete&id=<?php echo $vente['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette vente ?')">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>