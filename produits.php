<?php
$adminPage = true; 
include 'check.php';
?><?php 

// 🔹 Connexion base de données (PDO)
try {
    $db = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 🔹 Vérification session
if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
} 
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Clients - Tableau de Bord</title>
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

    h1,
    h2,
    h3,
    h4 {
        font-weight: 700;
        color: #333;
    }

    .card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .table thead {
        background: #667eea;
        color: white;
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
    <!-- 🔹 Header -->
    <div class="header">
        <div class="logo">Gestion Commerciale</div>
        <div class="user-info">
            <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></span>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <!-- 🔹 Contenu principal -->
    <div class="container-dashboard">
        <!-- 🔹 Sidebar -->
        <div class="sidebar">
            <h5 class="text-center mb-3 text-primary">Menu</h5>
            <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Tableau de Bord</a>
            <a href="clients.php" class="nav-link"><i class="bi bi-people me-2"></i> Gestion Clients</a>
            <a href="produits.php" class="nav-link active"><i class="bi bi-box-seam me-2"></i> Produits</a>
            <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
            <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
            <a href="facture_list.php" class="nav-link "><i class="bi bi-receipt"></i> Factures</a>
            <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
            <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
            <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
        </div>

        <!-- 🔹 Main Content -->
        <div class="main-content">
                        <h2 class="mb-4"><i class="bi bi-box-seam me-2"></i>Gestion des Produits</h2>

            <?php
            // ---------- CRUD PRODUITS ----------
            if(isset($_GET['action'])) {
                $action = $_GET['action'];
                
                if($action == 'add' && $_POST) {
                    $nom = $_POST['nom'];
                    $prix = $_POST['prix'];
                    $stock = $_POST['stock'];
                    $categorie = $_POST['categorie'];
                    $description = $_POST['description'];
                    
                    $stmt = $db->prepare("INSERT INTO produits (nom, prix, stock, categorie, description) VALUES (:nom, :prix, :stock, :categorie, :description)");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':prix' => $prix,
                        ':stock' => $stock,
                        ':categorie' => $categorie,
                        ':description' => $description
                    ]);
                    header("Location: produits.php?success=Produit ajouté avec succès");
                    exit;
                }

                if($action == 'edit' && $_POST) {
                    $stmt = $db->prepare("UPDATE produits SET nom=:nom, prix=:prix, stock=:stock, categorie=:categorie, description=:description WHERE id=:id");
                    $stmt->execute([
                        ':id' => $_POST['id'],
                        ':nom' => $_POST['nom'],
                        ':prix' => $_POST['prix'],
                        ':stock' => $_POST['stock'],
                        ':categorie' => $_POST['categorie'],
                        ':description' => $_POST['description']
                    ]);
                    header("Location: produits.php?success=Produit modifié avec succès");
                    exit;
                }

                if($action == 'delete' && isset($_GET['id'])) {
                    $stmt = $db->prepare("DELETE FROM produits WHERE id=:id");
                    $stmt->execute([':id' => $_GET['id']]);
                    header("Location: produits.php?success=Produit supprimé avec succès");
                    exit;
                }
            }

            // ---------- FORMULAIRE ADD/EDIT ----------
            if(isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')) {
                $produit = [];
                if($_GET['action'] == 'edit' && isset($_GET['id'])) {
                    $stmt = $db->prepare("SELECT * FROM produits WHERE id=:id");
                    $stmt->execute([':id' => $_GET['id']]);
                    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            ?>

            <h1 class="mb-4"><?php echo $_GET['action']=='add'?'Ajouter Produit':'Modifier Produit'; ?></h1>
            <form method="POST" action="produits.php?action=<?php echo $_GET['action']; ?>">
                <?php if($_GET['action']=='edit'): ?>
                <input type="hidden" name="id" value="<?php echo $produit['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" required
                        value="<?php echo $produit['nom'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Prix (MAD) *</label>
                    <input type="number" step="0.01" name="prix" class="form-control" required
                        value="<?php echo $produit['prix'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Stock *</label>
                    <input type="number" name="stock" class="form-control" required
                        value="<?php echo $produit['stock'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Catégorie</label>
                    <select class="form-control" name="categorie">
                        <option value="Électronique"
                            <?php echo ($produit['categorie'] ?? '')=='Électronique'?'selected':''; ?>>Électronique
                        </option>
                        <option value="Informatique"
                            <?php echo ($produit['categorie'] ?? '')=='Informatique'?'selected':''; ?>>Informatique
                        </option>
                        <option value="Mobile" <?php echo ($produit['categorie'] ?? '')=='Mobile'?'selected':''; ?>>
                            Mobile</option>
                        <option value="Accessoires"
                            <?php echo ($produit['categorie'] ?? '')=='Accessoires'?'selected':''; ?>>Accessoires
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3"
                        class="form-control"><?php echo $produit['description'] ?? ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="produits.php" class="btn btn-secondary">Annuler</a>
            </form>

            <?php
            } else {
                // ---------- LISTE DES PRODUITS ----------
            ?>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-between mb-3">
                <form method="GET" action="produits.php" class="d-flex w-50">
                    <input type="text" name="search" class="form-control me-2" placeholder="🔍 Rechercher un client..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                </form>
                    <a href="produits.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter
                    Produit</a>
            </div>
            <div class="card p-3 shadow-sm">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Catégorie</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

        // Récupération des produits (avec recherche)
        $search = $_GET['search'] ?? '';
        if(!empty($search)){
            $stmt = $db->prepare("SELECT * FROM produits WHERE nom LIKE :search OR categorie LIKE :search ORDER BY id DESC");
            $stmt->execute([':search' => "%$search%"]);
        } else {
            $stmt = $db->query("SELECT * FROM produits ORDER BY id DESC");
        }
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($produits as $produit):
                    ?>
                        <tr>
                            <td><?php echo $produit['id']; ?></td>
                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                            <td><?php echo number_format($produit['prix'], 2); ?> MAD</td>
                            <td><?php echo $produit['stock']; ?></td>
                            <td><?php echo htmlspecialchars($produit['categorie']); ?></td>
                            <td>
                                <a href="produits.php?action=edit&id=<?php echo $produit['id']; ?>"
                                    class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="produits.php?action=delete&id=<?php echo $produit['id']; ?>"
                                    class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')"><i
                                        class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>