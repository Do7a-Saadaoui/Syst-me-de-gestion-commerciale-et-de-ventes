<?php
session_start();
if(!isset($_SESSION['USER_ID'])){
    header("Location: login.php");
    exit;
}

// اتصال PDO
$pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// التحقق من العميل
if(!isset($_GET['client_id'])){
    die("Aucun client sélectionné");
}
$client_id = intval($_GET['client_id']);
$stmt = $pdo->prepare("SELECT nom FROM clients WHERE id=?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$client) die("Client introuvable");
$client_nom = $client['nom'];

// تحميل TCPDF
require_once('tcpdf/tcpdf.php');
$pdf = new TCPDF();
$pdf->AddPage();

// شعار الشركة
$logo = __DIR__.'/logo.png';
if(file_exists($logo)){
    $pdf->Image($logo, ($pdf->getPageWidth()-40)/2, 10, 40, 0);
}

// ترويسة الشركة
$pdf->SetFont('helvetica','B',12);
$pdf->SetY(60);
$pdf->Cell(0,6,"Data Software",0,1,'C');
$pdf->SetFont('helvetica','',10);
$pdf->Cell(0,5,"19 Rue 20 aout Hay Elhouda Berrechid, Berrechid 26100",0,1,'C');
$pdf->Cell(0,5,"Téléphone: +212 64 818 9019 | Email: contact@datasoftware.inf",0,1,'C');
$pdf->Ln(10);

// عنوان Devis
$pdf->SetFont('helvetica','B',14);
$pdf->SetTextColor(102,126,234);
$pdf->Cell(0,8,"DEVIS CLIENT: ".$client_nom,0,1,'C');
$pdf->Ln(5);

// جلب جميع commandes مع المنتجات
$stmt = $pdo->prepare("
    SELECT c.id AS commande_id, c.date_commande, c.total AS total_commande,
    p.nom AS produit_nom, ci.quantite, ci.prix_unitaire
    FROM commandes c
    JOIN commande_items ci ON ci.commande_id = c.id
    JOIN produits p ON p.id = ci.produit_id
    WHERE c.id_client = ?
    ORDER BY c.date_commande ASC
");
$stmt->execute([$client_id]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تحويل البيانات لهيكلية أفضل لكل commande
$commandes_grouped = [];
foreach($commandes as $row){
    $cid = $row['commande_id'];
    if(!isset($commandes_grouped[$cid])){
        $commandes_grouped[$cid] = [
            'date_commande' => $row['date_commande'],
            'total_commande' => $row['total_commande'],
            'produits' => []
        ];
    }
    $commandes_grouped[$cid]['produits'][] = [
        'nom' => $row['produit_nom'],
        'quantite' => $row['quantite'],
        'prix_unitaire' => $row['prix_unitaire']
    ];
}

// حساب المجموع النهائي
$total_ht = 0;
foreach($commandes_grouped as $c){
    foreach($c['produits'] as $p){
        $total_ht += $p['quantite'] * $p['prix_unitaire'];
    }
}
$taxe = 0.20;
$total_ttc = $total_ht * (1 + $taxe);

// عرض كل commandes
$pdf->SetFont('helvetica','',11);
foreach($commandes_grouped as $cid => $c){
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(0,6,"Commande #$cid - Date: ".date('d/m/Y', strtotime($c['date_commande'])),0,1);
    
    $html = '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
    $html .= '<tr style="background-color:#667eea;color:#fff;text-align:center;">
                <th>Produit</th><th>Quantité</th><th>Prix Unitaire</th><th>Total</th>
              </tr>';
    foreach($c['produits'] as $p){
        $total_item = $p['quantite'] * $p['prix_unitaire'];
        $html .= '<tr style="text-align:center;">
                    <td>'.$p['nom'].'</td>
                    <td>'.$p['quantite'].'</td>
                    <td>'.number_format($p['prix_unitaire'],2,',',' ').' MAD</td>
                    <td>'.number_format($total_item,2,',',' ').' MAD</td>
                  </tr>';
    }
    $html .= '</table><br>';
    $pdf->writeHTML($html,true,false,true,false,'');
}

// جدول المجموع النهائي
$html = '<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr style="background-color:#667eea;color:#fff;text-align:center;">
<th>Total HT</th><th>TVA (20%)</th><th>Total TTC</th>
</tr>
<tr style="background-color:#e6ebff;text-align:center;">
<td>'.number_format($total_ht,2,',',' ').' MAD</td>
<td>'.number_format($total_ht*$taxe,2,',',' ').' MAD</td>
<td>'.number_format($total_ttc,2,',',' ').' MAD</td>
</tr>
</table>';
$pdf->writeHTML($html,true,false,true,false,'');

// Footer
$pdf->SetY(-25);
$pdf->SetFont('helvetica','I',8);
$pdf->SetTextColor(128,128,128);
$pdf->Cell(0,5,'Page '.$pdf->getAliasNumPage().' / '.$pdf->getAliasNbPages(),0,1,'C');
$pdf->Cell(0,5,'Imprimé le '.date('d/m/Y H:i'),0,1,'C');

// توليد PDF
$pdf->Output('Devis_Client_'.$client_nom.'.pdf','I');
?>
