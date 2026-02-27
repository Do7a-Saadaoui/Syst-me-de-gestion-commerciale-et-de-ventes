<?php
$adminPage = true; 
include 'check.php';
?>
<?php 

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

// Vérifier si la table achats a les nouveaux champs, sinon les ajouter
try {
    $db->query("SELECT code_fournisseur FROM achats LIMIT 1");
} catch(PDOException $e) {
    // Ajouter les nouveaux champs si ils n'existent pas
    $db->exec("ALTER TABLE achats 
               ADD COLUMN code_fournisseur VARCHAR(50) UNIQUE NOT NULL AFTER id,
               ADD COLUMN adresse TEXT AFTER fournisseur,
               ADD COLUMN ville VARCHAR(50) AFTER adresse,
               ADD COLUMN pays VARCHAR(50) DEFAULT 'Maroc' AFTER ville,
               ADD COLUMN telephone VARCHAR(20) AFTER pays,
               ADD COLUMN fax VARCHAR(20) AFTER telephone,
               ADD COLUMN personne_contact VARCHAR(100) AFTER fax");
}

if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'add') {
        $code_fournisseur = $_POST['code_fournisseur'];
        $fournisseur = $_POST['fournisseur'];
        $adresse = $_POST['adresse'];
        $ville = $_POST['ville'];
        $pays = $_POST['pays'];
        $telephone = $_POST['telephone'];
        $fax = $_POST['fax'];
        $personne_contact = $_POST['personne_contact'];
        $produit_id = $_POST['produit_id'];
        $quantite = $_POST['quantite'];
        $prix_total = $_POST['prix_total'];
        $date_achat = $_POST['date_achat'];
        
        $query = "INSERT INTO achats SET 
                 code_fournisseur=:code_fournisseur, 
                 fournisseur=:fournisseur, 
                 adresse=:adresse, 
                 ville=:ville, 
                 pays=:pays, 
                 telephone=:telephone, 
                 fax=:fax, 
                 personne_contact=:personne_contact, 
                 produit_id=:produit_id, 
                 quantite=:quantite, 
                 prix_total=:prix_total, 
                 date_achat=:date_achat";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":code_fournisseur", $code_fournisseur);
        $stmt->bindParam(":fournisseur", $fournisseur);
        $stmt->bindParam(":adresse", $adresse);
        $stmt->bindParam(":ville", $ville);
        $stmt->bindParam(":pays", $pays);
        $stmt->bindParam(":telephone", $telephone);
        $stmt->bindParam(":fax", $fax);
        $stmt->bindParam(":personne_contact", $personne_contact);
        $stmt->bindParam(":produit_id", $produit_id);
        $stmt->bindParam(":quantite", $quantite);
        $stmt->bindParam(":prix_total", $prix_total);
        $stmt->bindParam(":date_achat", $date_achat);
        
        if($stmt->execute()) {
            // Mettre à jour le stock du produit
            $query_update = "UPDATE produits SET stock = stock + :quantite WHERE id = :produit_id";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->bindParam(":quantite", $quantite);
            $stmt_update->bindParam(":produit_id", $produit_id);
            $stmt_update->execute();
            
            header("Location: achats.php?success=Achat ajouté avec succès");
            exit;
        }
    }
    
    if($action == 'edit') {
        $id = $_POST['id'];
        $code_fournisseur = $_POST['code_fournisseur'];
        $fournisseur = $_POST['fournisseur'];
        $adresse = $_POST['adresse'];
        $ville = $_POST['ville'];
        $pays = $_POST['pays'];
        $telephone = $_POST['telephone'];
        $fax = $_POST['fax'];
        $personne_contact = $_POST['personne_contact'];
        $produit_id = $_POST['produit_id'];
        $quantite = $_POST['quantite'];
        $prix_total = $_POST['prix_total'];
        $date_achat = $_POST['date_achat'];
        
        $query = "UPDATE achats SET 
                 code_fournisseur=:code_fournisseur, 
                 fournisseur=:fournisseur, 
                 adresse=:adresse, 
                 ville=:ville, 
                 pays=:pays, 
                 telephone=:telephone, 
                 fax=:fax, 
                 personne_contact=:personne_contact, 
                 produit_id=:produit_id, 
                 quantite=:quantite, 
                 prix_total=:prix_total, 
                 date_achat=:date_achat 
                 WHERE id=:id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":code_fournisseur", $code_fournisseur);
        $stmt->bindParam(":fournisseur", $fournisseur);
        $stmt->bindParam(":adresse", $adresse);
        $stmt->bindParam(":ville", $ville);
        $stmt->bindParam(":pays", $pays);
        $stmt->bindParam(":telephone", $telephone);
        $stmt->bindParam(":fax", $fax);
        $stmt->bindParam(":personne_contact", $personne_contact);
        $stmt->bindParam(":produit_id", $produit_id);
        $stmt->bindParam(":quantite", $quantite);
        $stmt->bindParam(":prix_total", $prix_total);
        $stmt->bindParam(":date_achat", $date_achat);
        
        if($stmt->execute()) {
            header("Location: achats.php?success=Achat modifié avec succès");
            exit;
        }
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer la quantité avant suppression pour ajuster le stock
    $query_select = "SELECT produit_id, quantite FROM achats WHERE id=:id";
    $stmt_select = $db->prepare($query_select);
    $stmt_select->bindParam(":id", $id);
    $stmt_select->execute();
    $achat = $stmt_select->fetch(PDO::FETCH_ASSOC);
    
    $query = "DELETE FROM achats WHERE id=:id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $id);
    
    if($stmt->execute()) {
        // Ajuster le stock du produit
        if($achat) {
            $query_update = "UPDATE produits SET stock = stock - :quantite WHERE id = :produit_id";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->bindParam(":quantite", $achat['quantite']);
            $stmt_update->bindParam(":produit_id", $achat['produit_id']);
            $stmt_update->execute();
        }
        
        header("Location: achats.php?success=Achat supprimé avec succès");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des Achats</title>
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
        <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
        <a href="achats.php" class="nav-link active"><i class="bi bi-bag-check me-2"></i> Achats</a>
        <a href="facture_list.php" class="nav-link "><i class="bi bi-receipt"></i> Factures</a>
        <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
        <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
    </div>

    <div class="main-content">

        <?php if(isset($_GET['action']) && ($_GET['action']=='add' || $_GET['action']=='edit')): 
            $query_produits = "SELECT id, nom FROM produits";
            $stmt_produits = $db->prepare($query_produits);
            $stmt_produits->execute();
            $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
            
            $achat = null;
            if($_GET['action'] == 'edit' && isset($_GET['id'])) {
                $query_achat = "SELECT * FROM achats WHERE id = :id";
                $stmt_achat = $db->prepare($query_achat);
                $stmt_achat->bindParam(":id", $_GET['id']);
                $stmt_achat->execute();
                $achat = $stmt_achat->fetch(PDO::FETCH_ASSOC);
            }
        ?>
        <h2 class="mb-4"><i class="bi bi-plus-circle me-2"></i><?php echo $_GET['action'] == 'add' ? 'Nouvel Achat' : 'Modifier Achat'; ?></h2>
        <div class="card p-4">
            <form method="POST" action="achats.php">
                <input type="hidden" name="action" value="<?php echo $_GET['action']; ?>">
                <?php if($achat): ?>
                <input type="hidden" name="id" value="<?php echo $achat['id']; ?>">
                <?php endif; ?>
                
                <h5 class="text-primary mb-3"><i class="bi bi-building"></i> Informations Fournisseur</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Code Fournisseur *</label>
                        <input type="text" class="form-control" name="code_fournisseur" value="<?php echo $achat['code_fournisseur'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nom Fournisseur *</label>
                        <input type="text" class="form-control" name="fournisseur" value="<?php echo $achat['fournisseur'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Adresse</label>
                        <textarea class="form-control" name="adresse" rows="2"><?php echo $achat['adresse'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Ville</label>
                        <input type="text" class="form-control" name="ville" value="<?php echo $achat['ville'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Pays</label>
                        <input type="text" class="form-control" name="pays" value="<?php echo $achat['pays'] ?? 'Maroc'; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Téléphone</label>
                        <input type="text" class="form-control" name="telephone" value="<?php echo $achat['telephone'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fax</label>
                        <input type="text" class="form-control" name="fax" value="<?php echo $achat['fax'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Personne à Contacter</label>
                        <input type="text" class="form-control" name="personne_contact" value="<?php echo $achat['personne_contact'] ?? ''; ?>">
                    </div>
                </div>

                <h5 class="text-primary mb-3"><i class="bi bi-box-seam"></i> Détails de l'Achat</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Produit *</label>
                        <select class="form-control" name="produit_id" required>
                            <option value="">Sélectionner un produit</option>
                            <?php foreach($produits as $produit): ?>
                            <option value="<?php echo $produit['id']; ?>" <?php echo ($achat['produit_id'] ?? '') == $produit['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($produit['nom']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Quantité *</label>
                        <input type="number" class="form-control" name="quantite" value="<?php echo $achat['quantite'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Prix Total (MAD) *</label>
                        <input type="number" step="0.01" class="form-control" name="prix_total" value="<?php echo $achat['prix_total'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date d'Achat *</label>
                        <input type="date" class="form-control" name="date_achat" value="<?php echo $achat['date_achat'] ?? date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?php echo $_GET['action'] == 'add' ? 'Enregistrer' : 'Modifier'; ?></button>
                <a href="achats.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Annuler</a>
            </form>
        </div>

        <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-bag-check me-2"></i>Gestion des Achats</h2>
            <a href="achats.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter Achat</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_GET['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card p-3">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Code Fourn.</th>
                        <th>Fournisseur</th>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Total</th>
                        <th>Date</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT a.*, p.nom as produit_nom FROM achats a 
                    LEFT JOIN produits p ON a.produit_id=p.id 
                    ORDER BY a.id DESC";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($achats as $achat):
                    ?>
                    <tr>
                        <td><?php echo $achat['id']; ?></td>
                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($achat['code_fournisseur']); ?></span></td>
                        <td><?php echo htmlspecialchars($achat['fournisseur']); ?></td>
                        <td><?php echo htmlspecialchars($achat['produit_nom']); ?></td>
                        <td><?php echo $achat['quantite']; ?></td>
                        <td class="text-success fw-bold"><?php echo number_format($achat['prix_total'],2); ?> MAD</td>
                        <td><?php echo $achat['date_achat']; ?></td>
                        <td>
                            <small><?php echo htmlspecialchars($achat['personne_contact']); ?></small><br>
                            <small class="text-muted"><?php echo htmlspecialchars($achat['telephone']); ?></small>
                        </td>
                        <td>
                            <a href="achats.php?action=edit&id=<?php echo $achat['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="achats.php?action=delete&id=<?php echo $achat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet achat ?')">
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