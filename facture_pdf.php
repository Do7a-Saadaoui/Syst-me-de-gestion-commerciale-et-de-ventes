<?php
session_start();
if(!isset($_SESSION['USER_ID'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("ID de facture invalide");
}
$facture_id = intval($_GET['id']);

// اتصال PDO
$pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// جلب بيانات الفاتورة والكليان
$stmt = $pdo->prepare("SELECT f.*, c.client AS client_nom FROM factures f LEFT JOIN clients c ON f.client_id = c.id WHERE f.id = ?");
$stmt->execute([$facture_id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$facture) die("Facture introuvable");

// جلب المدفوعات
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement DESC");
$stmt->execute([$facture_id]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب المبالغ
$total_facture = floatval($facture['total']);
$taxe = 0.20; // TVA 20%
$total_ttc = $total_facture * (1 + $taxe);
$total_paye = 0;
foreach($paiements as $p){ $total_paye += floatval($p['montant']); }
$reste = $total_ttc - $total_paye;

// تحميل TCPDF - Vérifier le chemin
$tcpdf_path = __DIR__ . '/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die("Erreur : TCPDF n'est pas installé. Téléchargez-le depuis https://github.com/tecnickcom/tcpdf et placez-le dans le dossier 'tcpdf'");
}
require_once($tcpdf_path);

// إعداد PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Data Software');
$pdf->SetTitle('Facture #'.$facture_id);
$pdf->SetMargins(15, 35, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// إضافة صفحة
$pdf->AddPage();

// شعار الشركة (صغير ومتمركز)
$logo = __DIR__ . '/logo.png';
if(file_exists($logo)){
    $logoWidth = 40;  
    $logoHeight = 0;  
    $x = ($pdf->getPageWidth() - $logoWidth)/2;
    $pdf->Image($logo, $x, 10, $logoWidth, $logoHeight);
} else {
    $pdf->Cell(0,10,'[Logo manquant]',0,1,'C');
}

// ترويسة الشركة Data Software
$pdf->SetFont('helvetica','B',12);
$pdf->SetY(60);
$pdf->Cell(0,6,"Data Software",0,1,'C');
$pdf->SetFont('helvetica','',10);
$pdf->Cell(0,5,"19 Rue 20 aout Hay Elhouda Berrechid, Berrechid 26100",0,1,'C');
$pdf->Cell(0,5,"Téléphone: +212 64 818 9019 | Email: contact@datasoftware.inf",0,1,'C');
$pdf->Ln(10);

// عنوان الفاتورة
$pdf->SetFont('helvetica','B',14);
$pdf->SetTextColor(102,126,234);
$pdf->Cell(0,8,"FACTURE #".$facture_id,0,1,'C');
$pdf->Ln(5);

// بيانات العميل والتاريخ
$pdf->SetFont('helvetica','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,6,"Client: ".htmlspecialchars($facture['client_nom'] ?? '—'),0,1);
$pdf->Cell(0,6,"Date: ".date('d/m/Y', strtotime($facture['date_facture'] ?? date('Y-m-d'))),0,1);
$pdf->Ln(5);

// حالة الفاتورة
$statut = 'Non Payée';
if ($reste <= 0) {
    $statut = 'Payée';
} elseif ($total_paye > 0) {
    $statut = 'Partiellement Payée';
}

$pdf->SetFont('helvetica','B',11);
$pdf->Cell(0,6,"Statut: ".$statut,0,1);
$pdf->Ln(5);

// جدول المبالغ مع خلفية متدرجة وحواف مدورة
$html = '<table border="0" cellpadding="6" cellspacing="0" width="100%">
<tr style="background-color:#667eea;color:#fff;text-align:center;">
<th>Total HT</th><th>TVA (20%)</th><th>Total TTC</th><th>Total payé</th><th>Reste</th>
</tr>
<tr style="background-color:#e6ebff;text-align:center;">
<td>'.number_format($total_facture,2,',',' ').' MAD</td>
<td>'.number_format($total_facture*$taxe,2,',',' ').' MAD</td>
<td>'.number_format($total_ttc,2,',',' ').' MAD</td>
<td>'.number_format($total_paye,2,',',' ').' MAD</td>
<td>'.number_format($reste,2,',',' ').' MAD</td>
</tr>
</table><br><br>';

// جدول المدفوعات مع لون متدرج لكل صف
if($paiements){
    $html .= '<h4>Liste des Paiements</h4>';
    $html .= '<table border="0" cellpadding="5" cellspacing="0" width="100%">
    <tr style="background-color:#667eea;color:#fff;text-align:center;">
    <th>ID</th><th>Montant</th><th>Date</th><th>Mode</th><th>Note</th>
    </tr>';

    $i = 0;
    foreach($paiements as $p){
        $bg = ($i%2==0) ? '#f2f6ff' : '#dce3ff';
        $html .= '<tr style="background-color:'.$bg.';text-align:center;">
        <td>'.$p['id'].'</td>
        <td>'.number_format($p['montant'],2,',',' ').' MAD</td>
        <td>'.htmlspecialchars(date('d/m/Y', strtotime($p['date_paiement'] ?? date('Y-m-d')))).'</td>
        <td>'.htmlspecialchars($p['mode_paiement'] ?? '—').'</td>
        <td>'.htmlspecialchars($p['note'] ?? '').'</td>
        </tr>';
        $i++;
    }
    $html .= '</table>';
} else {
    $html .= '<p style="text-align:center;color:#666;font-style:italic;">Aucun paiement enregistré</p>';
}

// كتابة المحتوى للـ PDF
$pdf->writeHTML($html, true, false, true, false, '');

// معلومات إضافية
if(!empty($facture['note'])){
    $pdf->Ln(10);
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(0,6,"Note:",0,1);
    $pdf->SetFont('helvetica','',9);
    $pdf->MultiCell(0,5,htmlspecialchars($facture['note']),0,'L');
}

// فوتر احترافي
$pdf->SetY(-25);
$pdf->SetFont('helvetica','I',8);
$pdf->SetTextColor(128,128,128);
$pdf->Cell(0,5,'Page '.$pdf->getAliasNumPage().' / '.$pdf->getAliasNbPages(),0,1,'C');
$pdf->Cell(0,5,'Imprimé le '.date('d/m/Y H:i'),0,1,'C');
$pdf->Cell(0,5,'Data Software - Système de Gestion Commerciale',0,1,'C');

// توليد PDF
$pdf->Output('Facture_'.$facture_id.'.pdf','I');
?>