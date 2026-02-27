<?php
session_start();
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

// 🔹 Gestion CRUD Utilisateurs
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if($action == 'add' && $_POST) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (:username,:email,:password,:role)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $role);
        if($stmt->execute()){
            header("Location: users.php?success=Utilisateur ajouté avec succès");
            exit;
        }
    }

    if($action == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $db->prepare("DELETE FROM users WHERE id=:id");
        $stmt->bindParam(":id", $id);
        if($stmt->execute()){
            header("Location: users.php?success=Utilisateur supprimé avec succès");
            exit;
        }
    }
}

// 🔹 Statistiques simples
$stmt_count = $db->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_roles = $db->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
$roles_stats = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Liste des utilisateurs
$stmt_users = $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des Utilisateurs</title>
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
        <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Tableau de Bord </a>
        <a href="clients.php" class="nav-link"><i class="bi bi-people me-2"></i> Clients</a>
        <a href="produits.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Produits</a>
        <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
        <a href="achats.php" class="nav-link"><i class="bi bi-bag-check me-2"></i> Achats</a>
        <a href="facture_list.php" class="nav-link "><i class="bi bi-receipt"></i> Factures</a>
        <a href="paiement_list.php" class="nav-link"><i class="bi bi-cash-coin"></i> Paiements</a>
        <a href="rapports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="users.php" class="nav-link active"><i class="bi bi-person-lines-fill me-2"></i> Utilisateurs</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-4"><i class="bi bi-person-lines-fill me-2"></i>Gestion des Utilisateurs</h1>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle"></i> Ajouter Utilisateur
            </a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <!-- Statistiques utilisateurs -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Utilisateurs</h5>
                        <h2 class="card-value"><?php echo $total_users; ?></h2>
                    </div>
                </div>
            </div>
            <?php foreach($roles_stats as $role_stat): ?>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($role_stat['role']); ?></h5>
                        <h2 class="card-value"><?php echo $role_stat['total']; ?></h2>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="users.php?action=add">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Ajouter un Utilisateur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-select" required>
              <option value="Admin">Admin</option>
              <option value="Utilisateur">Utilisateur</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Ajouter</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
