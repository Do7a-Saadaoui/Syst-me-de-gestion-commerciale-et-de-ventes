<?php
$adminPage = true; 
include 'check.php';
?>
<?php 

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

// 🔹 Gestion des actions CRUD
if(isset($_POST['action'])) {
    $action = $_POST['action'];

    // ➕ Ajouter un client
    if($action == 'add') {
        $stmt = $db->prepare("INSERT INTO clients (client, adresse, ville, pays, telephone, fax, email, personne_a_contacter, commercial) 
                             VALUES (:client, :adresse, :ville, :pays, :telephone, :fax, :email, :personne, :commercial)");
        $stmt->execute([
            ':client' => $_POST['client'],
            ':adresse' => $_POST['adresse'],
            ':ville' => $_POST['ville'],
            ':pays' => $_POST['pays'],
            ':telephone' => $_POST['telephone'],
            ':fax' => $_POST['fax'],
            ':email' => $_POST['email'],
            ':personne' => $_POST['personne_a_contacter'],
            ':commercial' => $_POST['commercial']
        ]);
        header("Location: clients.php?success=Client+ajouté+avec+succès");
        exit;
    }

    // ✏️ Modifier client
    if($action == 'edit') {
        $stmt = $db->prepare("UPDATE clients SET client=:client, adresse=:adresse, ville=:ville, pays=:pays, telephone=:telephone, fax=:fax, email=:email, personne_a_contacter=:personne, commercial=:commercial WHERE id=:id");
        $stmt->execute([
            ':id' => $_POST['id'],
            ':client' => $_POST['client'],
            ':adresse' => $_POST['adresse'],
            ':ville' => $_POST['ville'],
            ':pays' => $_POST['pays'],
            ':telephone' => $_POST['telephone'],
            ':fax' => $_POST['fax'],
            ':email' => $_POST['email'],
            ':personne' => $_POST['personne_a_contacter'],
            ':commercial' => $_POST['commercial']
        ]);
        header("Location: clients.php?success=Client+modifié+avec+succès");
        exit;
    }
}

// 🔹 Suppression client
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $db->prepare("DELETE FROM clients WHERE id=:id");
    $stmt->execute([':id'=>$_GET['id']]);
    header("Location: clients.php?success=Client+supprimé+avec+succès");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Clients - Tableau de Bord</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body {
        font-family: sans-serif;
        background: #f5f6fa;
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

    .table-container {
        overflow-x: auto;
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
            <span>Bienvenue, <?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></span>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <div class="container-dashboard">
        <div class="sidebar">
            <h5 class="text-center mb-3 text-primary">Menu</h5>
            <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Tableau de Bord</a>
            <a href="clients.php" class="nav-link active"><i class="bi bi-people me-2"></i> Gestion Clients</a>
            <a href="produits.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Produits</a>
            <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
            <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
            <a href="facture_list.php" class="nav-link"><i class="bi bi-receipt"></i> Factures</a>
            <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
            <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
            <a href="users.php" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
        </div>

        <div class="main-content">
            <h2 class="mb-4"><i class="bi bi-people-fill me-2"></i>Gestion des Clients</h2>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= urldecode($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between mb-3">
                <form method="GET" action="clients.php" class="d-flex w-50">
                    <input type="text" name="search" class="form-control me-2" placeholder="🔍 Rechercher un client..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                </form>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addClientModal">
                    <i class="bi bi-plus-circle"></i> Ajouter Client
                </button>
            </div>

            <div class="card p-3">
                <div class="table-container">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Adresse</th>
                                <th>Ville</th>
                                <th>Pays</th>
                                <th>Téléphone</th>
                                <th>Fax</th>
                                <th>Email</th>
                                <th>Personne à contacter</th>
                                <th>Commercial</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                        $search = $_GET['search'] ?? '';
                        if(!empty($search)) {
                            $stmt = $db->prepare("SELECT * FROM clients WHERE client LIKE :search OR email LIKE :search OR telephone LIKE :search OR ville LIKE :search OR personne_a_contacter LIKE :search ORDER BY id DESC");
                            $stmt->execute([':search' => "%$search%"]);
                        } else {
                            $stmt = $db->query("SELECT * FROM clients ORDER BY id DESC");
                        }
                        foreach($stmt as $client): ?>
                            <tr>
                                <td><?= $client['id']; ?></td>
                                <td><?= htmlspecialchars($client['client']); ?></td>
                                <td><?= htmlspecialchars($client['adresse']); ?></td>
                                <td><?= htmlspecialchars($client['ville']); ?></td>
                                <td><?= htmlspecialchars($client['pays']); ?></td>
                                <td><?= htmlspecialchars($client['telephone']); ?></td>
                                <td><?= htmlspecialchars($client['fax']); ?></td>
                                <td><?= htmlspecialchars($client['email']); ?></td>
                                <td><?= htmlspecialchars($client['personne_a_contacter']); ?></td>
                                <td><?= htmlspecialchars($client['commercial']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editClientModal<?= $client['id']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="clients.php?action=delete&id=<?= $client['id']; ?>"
                                        onclick="return confirm('Supprimer ce client ?');" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <button class="btn btn-info btn-sm" onclick="genererDevis(<?= $client['id'] ?>)">
                                        <i class="bi bi-file-earmark-text"></i> Devis
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Client -->
    <div class="modal fade" id="addClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Ajouter un Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="clients.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client *</label>
                                <input type="text" class="form-control" name="client" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" name="telephone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fax</label>
                                <input type="text" class="form-control" name="fax">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="adresse" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control" name="ville">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pays</label>
                                <input type="text" class="form-control" name="pays">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Commercial</label>
                                <input type="text" class="form-control" name="commercial">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Personne à contacter</label>
                            <input type="text" class="form-control" name="personne_a_contacter">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals d'édition - PLACÉS EN DEHORS DU TABLEAU -->
    <?php
    // Ré-exécuter la requête pour les modals d'édition
    $search = $_GET['search'] ?? '';
    if(!empty($search)) {
        $stmt_modal = $db->prepare("SELECT * FROM clients WHERE client LIKE :search OR email LIKE :search OR telephone LIKE :search OR ville LIKE :search OR personne_a_contacter LIKE :search ORDER BY id DESC");
        $stmt_modal->execute([':search' => "%$search%"]);
    } else {
        $stmt_modal = $db->query("SELECT * FROM clients ORDER BY id DESC");
    }
    foreach($stmt_modal as $client): 
    ?>
    <div class="modal fade" id="editClientModal<?= $client['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="clients.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $client['id']; ?>">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client *</label>
                                <input type="text" class="form-control" name="client" value="<?= htmlspecialchars($client['client'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" name="telephone" value="<?= htmlspecialchars($client['telephone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($client['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fax</label>
                                <input type="text" class="form-control" name="fax" value="<?= htmlspecialchars($client['fax'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="adresse" rows="2"><?= htmlspecialchars($client['adresse'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control" name="ville" value="<?= htmlspecialchars($client['ville'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pays</label>
                                <input type="text" class="form-control" name="pays" value="<?= htmlspecialchars($client['pays'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Commercial</label>
                                <input type="text" class="form-control" name="commercial" value="<?= htmlspecialchars($client['commercial'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Personne à contacter</label>
                            <input type="text" class="form-control" name="personne_a_contacter" value="<?= htmlspecialchars($client['personne_a_contacter'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function genererDevis(clientId) {
        fetch('devis.php?client_id=' + clientId)
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Devis_Client_' + clientId + '.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
        })
        .catch(err => console.error('Erreur:', err));
    }
    </script>
</body>
</html>