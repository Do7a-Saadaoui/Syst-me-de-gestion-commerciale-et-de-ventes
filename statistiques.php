<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_commerciale");

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requêtes statistiques
$totalProduits = $conn->query("SELECT COUNT(*) AS total FROM produits")->fetch_assoc()['total'];
$totalClients = $conn->query("SELECT COUNT(*) AS total FROM clients")->fetch_assoc()['total'];
$totalCommandes = $conn->query("SELECT COUNT(*) AS total FROM commandes")->fetch_assoc()['total'];
$totalVentes = $conn->query("SELECT SUM(total) AS total FROM commandes")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f6f7fb;
            font-family: 'Poppins', sans-serif;
            padding: 30px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            background: white;
            margin-bottom: 20px;
            text-align: center;
        }
        h3 { color: #333; }
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
    </style>
</head>
<body>

<h2 class="mb-4 text-center">📊 Statistiques Générales</h2>

<div class="dashboard">
    <div class="card">
        <h3><?= $totalProduits ?></h3>
        <p>Produits</p>
    </div>
    <div class="card">
        <h3><?= $totalClients ?></h3>
        <p>Clients</p>
    </div>
    <div class="card">
        <h3><?= $totalCommandes ?></h3>
        <p>Commandes</p>
    </div>
    <div class="card">
        <h3><?= number_format($totalVentes, 2) ?> DH</h3>
        <p>Total des ventes</p>
    </div>
</div>

<canvas id="chartVentes" style="margin-top:40px; max-width:700px; display:block; margin:auto;"></canvas>

<script>
// Exemple de graphique
const ctx = document.getElementById('chartVentes');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
        datasets: [{
            label: 'Ventes mensuelles (DH)',
            data: [1200, 1900, 3000, 5000, 2300, 4100],
            borderWidth: 1,
            backgroundColor: 'rgba(75, 192, 192, 0.4)',
            borderColor: 'rgba(75, 192, 192, 1)'
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>
