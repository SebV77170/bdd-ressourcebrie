<?php
require('../users/securityAction.php');
require('../db.php');
require('sessionCaisseHelpers.php');
require(__DIR__ . '/../../vendor/autoload.php');

if ($_SESSION['admin'] < 1) {
    echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp.';
    exit;
}

/* ============================================================
   RÉCUPÉRATION DES DONNÉES (INCHANGÉ)
============================================================ */

$columns = [];
$dateColumn = null;
$ecartColumn = null;
$errors = [];
$rows = [];
$years = [];
$selectedYear = null;

try {
    $columns = getSessionCaisseColumns($db);
    $columnNames = array_column($columns, 'Field');
    $dateColumn = findSessionCaisseDateColumn($columnNames);
    $ecartColumn = findSessionCaisseEcartColumn($columnNames);

    if (!$ecartColumn) {
        $errors[] = "La colonne 'ecart' est introuvable dans la table session_caisse.";
    }

    if ($dateColumn) {
        $years = getSessionCaisseYears($db, $dateColumn);
        if (isset($_GET['year'])) {
            $selectedYear = (int) $_GET['year'];
        } elseif (!empty($years)) {
            $selectedYear = (int) $years[0];
        } else {
            $selectedYear = (int) date('Y');
        }

        if ($selectedYear) {
            $rows = getSessionCaisseRowsForYear($db, $dateColumn, $selectedYear);
        }
    } else {
        $errors[] = "Aucune colonne de date n'a été trouvée pour filtrer par année.";
    }
} catch (Exception $exception) {
    $errors[] = 'Impossible de récupérer les sessions de caisse : ' . $exception->getMessage();
}

if (!empty($errors)) {
    echo implode('<br>', array_map('htmlspecialchars', $errors));
    exit;
}

if (!$ecartColumn || !$dateColumn) {
    echo 'Impossible de générer le PDF : colonnes manquantes.';
    exit;
}

/* ============================================================
   CALCULS (INCHANGÉ)
============================================================ */

$rowsWithEcart = array_values(array_filter(
    $rows,
    fn($row) => (float)($row[$ecartColumn] ?? 0) !== 0.0
));

$recap = buildEcartRecap($rowsWithEcart, $ecartColumn);
$recapNet = $recap['positive'] + $recap['negative'];

$sessionCount = count($rows);
$sessionCountWithEcart = count($rowsWithEcart);

$totals = [
    'fond_initial' => 0,
    'montant_reel' => 0,
    'montant_reel_carte' => 0,
    'montant_reel_cheque' => 0,
    'montant_reel_virement' => 0,
];

foreach ($rowsWithEcart as $row) {
    foreach ($totals as $key => $value) {
        if (isset($row[$key])) {
            $totals[$key] += (float)$row[$key];
        }
    }
}

/* ============================================================
   OUTILS PDF
============================================================ */

function pdfText(string $text): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}

class PDF_Table extends FPDF
{
    public function NbLines($w, $txt)
    {
        $cw = $this->CurrentFont['cw'];
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") {
                $i++; $sep = -1; $j = $i; $l = 0; $nl++;
                continue;
            }
            if ($c === ' ') $sep = $i;
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                $i = ($sep === -1) ? $i + 1 : $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}

function pdfRow(PDF_Table $pdf, array $cells, array $widths, int $lineHeight = 5)
{
    $maxLines = 1;
    foreach ($cells as $i => $txt) {
        $maxLines = max($maxLines, $pdf->NbLines($widths[$i], $txt));
    }
    $rowHeight = $lineHeight * $maxLines;

    if ($pdf->GetY() + $rowHeight > $pdf->GetPageHeight() - 15) {
        $pdf->AddPage();
    }

    foreach ($cells as $i => $txt) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Rect($x, $y, $widths[$i], $rowHeight);
        $pdf->MultiCell($widths[$i], $lineHeight, $txt, 0, 'L');
        $pdf->SetXY($x + $widths[$i], $y);
    }
    $pdf->Ln($rowHeight);
}

/* ============================================================
   PDF – CONTENU (IDENTIQUE + TABLEAU CORRIGÉ)
============================================================ */

$pdf = new PDF_Table('P', 'mm', 'A4');
$pdf->SetTitle(pdfText('Bilan des sessions de caisse'), false);
$pdf->SetAuthor(pdfText('La Ressourcerie de Brie'), false);
$pdf->AddPage();

/* === En-tête === */
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, pdfText('Bilan des sessions de caisse'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, pdfText('Année : ' . $selectedYear), 0, 1, 'C');
$pdf->Cell(0, 6, pdfText('Date de génération : ' . date('d/m/Y')), 0, 1, 'C');
$pdf->Ln(6);

/* === Récap === (inchangé) */
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, pdfText('Détail des sessions avec écart'), 0, 1, 'L');

/* ============================================================
   TABLEAU DÉTAIL – VERSION LISIBLE
============================================================ */

$displayColumns = [];
foreach (['id_session'] as $c) if (in_array($c, $columnNames, true)) $displayColumns[] = $c;
foreach ([$dateColumn] as $c) if ($c && in_array($c, $columnNames, true)) $displayColumns[] = $c;
foreach (['utilisateur_ouverture','utilisateur_fermeture'] as $c) if (in_array($c, $columnNames, true)) $displayColumns[] = $c;
foreach (['fond_initial','montant_reel',$ecartColumn] as $c) if ($c && in_array($c, $columnNames, true)) $displayColumns[] = $c;
foreach (['commentaire'] as $c) if (in_array($c, $columnNames, true)) $displayColumns[] = $c;

$widthMap = [
    'id_session' => 22,
    $dateColumn => 22,
    'utilisateur_ouverture' => 30,
    'utilisateur_fermeture' => 30,
    'fond_initial' => 18,
    'montant_reel' => 18,
    $ecartColumn => 18,
    'commentaire' => 32,
];

$pdf->SetFont('Arial', 'B', 9);

$headers = [];
$widths = [];
foreach ($displayColumns as $col) {
    $headers[] = pdfText(ucwords(str_replace('_',' ', $col)));
    $widths[] = $widthMap[$col];
}
pdfRow($pdf, $headers, $widths, 6);

$pdf->SetFont('Arial', '', 8);

foreach ($rowsWithEcart as $row) {
    $cells = [];
    foreach ($displayColumns as $col) {
        $cells[] = pdfText((string)($row[$col] ?? ''));
    }
    pdfRow($pdf, $cells, $widths, 5);
}

/* === Signatures === */
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(90, 20, pdfText('Signature Trésorier'), 1);
$pdf->Cell(90, 20, pdfText('Signature Président'), 1);

/* === Sortie === */
if (ob_get_length()) ob_end_clean();
$pdf->Output('D', 'bilan_sessions_caisse_' . $selectedYear . '.pdf');
