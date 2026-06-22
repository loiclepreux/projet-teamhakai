<?php
session_start();

require_once __DIR__ . '/../dompdf/autoload.inc.php';
require_once __DIR__ . '/../SMTP/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../SMTP/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../SMTP/PHPMailer/src/Exception.php';
require_once 'config.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Numéro de commande
$numero_commande = 'CMD-' . date('YmdHis');

// Vérification CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("Erreur CSRF.");
}

// Action : preview ou send
$action = $_POST['action'] ?? 'send';

// Validation des champs
$nom       = trim($_POST['nom'] ?? '');
$prenom    = trim($_POST['prenom'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$adresse   = trim($_POST['adresse'] ?? '');
$relais    = trim($_POST['point-relais'] ?? '');

if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || empty($adresse)) {
    $_SESSION['message'] = "Tous les champs sont obligatoires.";
    header('Location: ../php/boutique.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Adresse email invalide.";
    header('Location: ../php/boutique.php');
    exit();
}

// Données panier
$produits  = $_POST['produits'] ?? [];
$quantites = $_POST['quantites'] ?? [];
$montants  = $_POST['montants'] ?? [];

if (empty($produits) || count($produits) !== count($quantites) || count($produits) !== count($montants)) {
    $_SESSION['message'] = "Votre panier est vide ou invalide.";
    header('Location: ../php/boutique.php');
    exit();
}

foreach ($quantites as $qte) {
    if (!is_numeric($qte) || $qte <= 0) {
        $_SESSION['message'] = "Quantité invalide dans le panier.";
        header('Location: ../php/boutique.php');
        exit();
    }
}

// Génération HTML PDF

$logoPath = realpath(__DIR__ . '/../img/logo2.png');
$logoWebPath = 'file://' . $logoPath;

$html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: #333; }
    .header { text-align: center; margin-bottom: 20px; }
    .header img { width: 120px; margin-bottom: 10px; }
    .header h1 { color: #ff004c; font-size: 22px; margin: 0; }
    .section { margin-bottom: 20px; }
    .info-client p { margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #444; padding: 8px; text-align: left; }
    th { background-color: #ffe6f0; }
    .total { text-align: right; margin-top: 20px; font-size: 16px; font-weight: bold; }
    .footer { text-align: center; margin-top: 30px; font-style: italic; }
</style>

<div class="header">
    <img src="' . $logoWebPath . '" alt="Logo">
    <h1>Boutique HAKAI - Récapitulatif de Commande</h1>
    <p><strong>Numéro de commande :</strong> ' . $numero_commande . '</p>
</div>

<div class="section info-client">
    <strong>Informations client :</strong>
    <p>Nom : ' . htmlspecialchars($nom) . ' ' . htmlspecialchars($prenom) . '</p>
    <p>Email : ' . htmlspecialchars($email) . '</p>
    <p>Adresse : ' . htmlspecialchars($adresse) . '</p>
    <p>Point relais : ' . htmlspecialchars($relais) . '</p>
</div>

<div class="section">
    <strong>Détails de la commande :</strong>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>';

$total = 0;
for ($i = 0; $i < count($produits); $i++) {
    $nomProduit = htmlspecialchars($produits[$i]);
    $quantite   = intval($quantites[$i]);
    $prixUnitaire = ($quantite > 0 && isset($montants[$i])) ? floatval($montants[$i]) / $quantite : 0;
    $sousTotal = $quantite * $prixUnitaire;
    $total += $sousTotal;

    $html .= "<tr>
        <td>{$nomProduit}</td>
        <td>{$quantite}</td>
        <td>" . number_format($prixUnitaire, 2, ',', ' ') . " EUR</td>
        <td>" . number_format($sousTotal, 2, ',', ' ') . " EUR</td>
    </tr>";
}

$html .= '
        </tbody>
    </table>
</div>

<div class="total">Total de la commande : ' . number_format($total, 2, ',', ' ') . ' EUR</div>
<div class="footer">Merci pour votre commande. À bientôt sur Boutique HAKAI !</div>';

// Initialisation de mPDF
$Dompdf = new Dompdf();
$Dompdf->loadHtml($html);

// Si on demande la prévisualisation
// if ($action === 'preview') {
//     $Dompdf->render();
//     $Dompdf->stream("commande.pdf", ["Attachment" => false]);
// }

// Si on valide la commande et qu'on envoie l'e-mail
if ($action === 'send') {
    $pdfPath = __DIR__ . '/commande_' . $numero_commande . '.pdf';
    $Dompdf->render();
    file_put_contents($pdfPath, $Dompdf->output());

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->Debugoutput = "html";

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom(MAIL_USER, "Boutique Hakai");
        $mail->addAddress($email, $nom);
        $mail->addCC( MAIL_USER, "Fournisseur");

        $mail->isHTML(true);
        $mail->Subject = "Votre commande - Boutique Hakai";
        $mail->Body = "Merci pour votre commande ! Vous trouverez votre récapitulatif en pièce jointe.";
        $mail->addAttachment($pdfPath, "commande.pdf");

        $mail->send();
        unlink($pdfPath);

        $_SESSION["message"] = "📩 Votre commande a bien été envoyée à $email.";
        header("Location: ../index.php");
        exit();

    } catch (Exception $e) {
        if (file_exists($pdfPath)) unlink($pdfPath);
        $_SESSION['message'] = "Erreur lors de l'envoi du mail. Veuillez réessayer.";
        header('Location: ../php/boutique.php');
        exit();
    }
}
?>