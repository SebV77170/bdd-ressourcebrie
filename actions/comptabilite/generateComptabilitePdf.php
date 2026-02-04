<?php
require('../users/securityAction.php');
require('../db.php');
require('sessionCaisseHelpers.php');
require(__DIR__ . '/../../vendor/autoload.php');

if ($_SESSION['admin'] < 1) {
    echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp.';
    exit;
}

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

$rowsWithEcart = array_filter($rows, fn($row) => (float) ($row[$ecartColumn] ?? 0) !== 0.0);
$rowsWithEcart = array_values($rowsWithEcart);

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
            $totals[$key] += (float) $row[$key];
        }
    }
}

function formatEuro(int|float $centimes): string
{
    $euros = $centimes / 100;

    return number_format($euros, 2, ',', ' ') . ' €';
}


function pdfText(string $text): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}

/* ============================================================
   OUTILS TABLEAU (MultiCell + hauteurs auto + saut de page)
============================================================ */

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

    /**
     * Largeur réellement imprimable (page - marges)
     */
    public function getPrintableWidth(): float
    {
        return $this->w - $this->lMargin - $this->rMargin;
    }

    /**
     * Calcule des largeurs mises à l'échelle pour tenir dans la page
     */
    public function buildScaledWidths(array $displayColumns, array $baseWidthMap): array
    {
        $printable = $this->getPrintableWidth();

        $baseWidths = [];
        $sum = 0.0;

        foreach ($displayColumns as $col) {
            $w = $baseWidthMap[$col] ?? 18;
            $baseWidths[] = $w;
            $sum += $w;
        }

        if ($sum <= 0) {
            return array_fill(0, count($displayColumns), $printable / max(count($displayColumns), 1));
        }

        $scale = $printable / $sum;
        $scaled = [];

        foreach ($baseWidths as $w) {
            $scaled[] = max(10, $w * $scale); // min 10 mm
        }

        // Ajustement final
        $diff = $printable - array_sum($scaled);
        if (abs($diff) > 0.01) {
            $scaled[count($scaled) - 1] += $diff;
        }

        return $scaled;
    }
}


function pdfTableHeader(PDF_Table $pdf, array $headers, array $widths, int $lineHeight = 6): void
{
    $pdf->SetFont('Arial', 'B', 8);
    $cells = [];
    foreach ($headers as $h) {
        $cells[] = pdfText($h);
    }
    pdfRow($pdf, $cells, $widths, $lineHeight);
    $pdf->SetFont('Arial', '', 7);
}

function pdfRow(PDF_Table $pdf, array $cells, array $widths, int $lineHeight = 5): float
{
    $maxLines = 1;
    foreach ($cells as $i => $txt) {
        $maxLines = max($maxLines, $pdf->NbLines($widths[$i], $txt));
    }
    $rowHeight = $lineHeight * $maxLines;

    foreach ($cells as $i => $txt) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Rect($x, $y, $widths[$i], $rowHeight);
        $pdf->MultiCell($widths[$i], $lineHeight, $txt, 0, 'L');
        $pdf->SetXY($x + $widths[$i], $y);
    }
    $pdf->Ln($rowHeight);

    return $rowHeight;
}

/* ============================================================
   PDF (portrait) : EN-TÊTE + RÉCAP (INCHANGÉ)
============================================================ */

$pdf = new PDF_Table('P', 'mm', 'A4');
$pdf->SetTitle('Bilan des sessions de caisse', true);
$pdf->SetAuthor('La Ressourcerie de Brie', true);
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, pdfText('Bilan des sessions de caisse'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, pdfText('Année : ' . $selectedYear), 0, 1, 'C');
$pdf->Cell(0, 6, pdfText('Date de génération : ' . date('d/m/Y')), 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, pdfText('Récapitulatif des écarts (sessions avec écart uniquement)'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(70, 8, pdfText('Total des écarts négatifs'), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText('Total des écarts positifs'), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText('Nombre de sessions'), 1, 1, 'L');
$pdf->Cell(70, 8, pdfText(formatEuro($recap['negative'])), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText(formatEuro($recap['positive'])), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText((string) $sessionCountWithEcart), 1, 1, 'L');

$pdf->Ln(4);
$pdf->Cell(70, 8, pdfText('Écart net'), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText('Total sessions année'), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText('Sessions sans écart'), 1, 1, 'L');
$pdf->Cell(70, 8, pdfText(formatEuro($recapNet)), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText((string) $sessionCount), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText((string) ($sessionCount - $sessionCountWithEcart)), 1, 1, 'L');

$pdf->Ln(6);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 7, pdfText('Total fond initial'), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText('Total montant réel'), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText('Total carte / chèque / virement'), 1, 1, 'L');
$pdf->Cell(70, 7, pdfText(formatEuro($totals['fond_initial'])), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText(formatEuro($totals['montant_reel'])), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText(
    formatEuro($totals['montant_reel_carte']) . ' / ' .
    formatEuro($totals['montant_reel_cheque']) . ' / ' .
    formatEuro($totals['montant_reel_virement'])
), 1, 1, 'L');

/* ============================================================
   PDF (landscape) : TABLEAU DÉTAIL LISIBLE
============================================================ */

$pdf->AddPage('L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, pdfText('Détail des sessions avec écart'), 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 9);

$columnNames = array_column($columns, 'Field');
$displayColumns = [];

// Id
foreach (['id_session'] as $possibleIdColumn) {
    if (in_array($possibleIdColumn, $columnNames, true)) {
        $displayColumns[] = $possibleIdColumn;
        break;
    }
}
// Date ouverture (priorité opened_at_utc puis dateColumn)
foreach (['opened_at_utc', $dateColumn] as $possibleDateColumn) {
    if ($possibleDateColumn && in_array($possibleDateColumn, $columnNames, true)) {
        $displayColumns[] = $possibleDateColumn;
        break;
    }
}
// Date fermeture
foreach (['closed_at_utc', 'closed_at'] as $possibleCloseColumn) {
    if (in_array($possibleCloseColumn, $columnNames, true)) {
        $displayColumns[] = $possibleCloseColumn;
        break;
    }
}
// Personnes
foreach (['utilisateur_ouverture', 'responsable_ouverture', 'utilisateur_fermeture', 'responsable_fermeture'] as $personColumn) {
    if (in_array($personColumn, $columnNames, true)) {
        $displayColumns[] = $personColumn;
    }
}
// Montants
foreach (['fond_initial', 'montant_reel', 'montant_reel_carte', 'montant_reel_cheque', 'montant_reel_virement', $ecartColumn] as $moneyColumn) {
    if ($moneyColumn && in_array($moneyColumn, $columnNames, true)) {
        $displayColumns[] = $moneyColumn;
    }
}
// Meta
foreach (['commentaire', 'cashiers', 'poste', 'issecondaire'] as $metaColumn) {
    if (in_array($metaColumn, $columnNames, true)) {
        $displayColumns[] = $metaColumn;
    }
}

// Labels
$columnLabels = [];
foreach ($displayColumns as $columnName) {
    $columnLabels[] = ucwords(str_replace('_', ' ', $columnName));
}

// Largeurs “de base” (avant scaling) : adaptées à tes types de colonnes
$baseWidthMap = [
    'id_session' => 26,
    'opened_at_utc' => 26,
    'closed_at_utc' => 26,
    'closed_at' => 26,
    $dateColumn => 26,

    'utilisateur_ouverture' => 30,
    'responsable_ouverture' => 30,
    'utilisateur_fermeture' => 30,
    'responsable_fermeture' => 30,

    'fond_initial' => 20,
    'montant_reel' => 20,
    'montant_reel_carte' => 20,
    'montant_reel_cheque' => 20,
    'montant_reel_virement' => 20,
    $ecartColumn => 20,

    'commentaire' => 45,
    'cashiers' => 28,
    'poste' => 16,
    'issecondaire' => 16,
];

// On scale automatiquement pour tenir en A4 landscape
$widths = $pdf->buildScaledWidths($displayColumns, $baseWidthMap);

// En-tête du tableau
pdfTableHeader($pdf, $columnLabels, $widths, 6);

// Lignes
$pdf->SetFont('Arial', '', 7);

if (empty($rowsWithEcart)) {
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 6, pdfText('Aucune session avec écart pour cette année.'), 1, 1, 'L');
} else {
    foreach ($rowsWithEcart as $row) {
        // Calcul hauteur de ligne et saut de page + répétition en-tête
        $cells = [];
        foreach ($displayColumns as $col) {
            $cells[] = pdfText((string)($row[$col] ?? ''));
        }

        // Estimation hauteur
        $maxLines = 1;
        foreach ($cells as $i => $txt) {
            $maxLines = max($maxLines, $pdf->NbLines($widths[$i], $txt));
        }
        $rowHeight = 5 * $maxLines;

        if ($pdf->GetY() + $rowHeight > $pdf->GetPageHeight() - 15) {
            $pdf->AddPage('L');
            $pdf->Cell(0, 8, pdfText('Détail des sessions avec écart (suite)'), 0, 1, 'L');
            $pdf->Ln(2);
            pdfTableHeader($pdf, $columnLabels, $widths, 6);
            $pdf->SetFont('Arial', '', 7);
        }

        pdfRow($pdf, $cells, $widths, 5);
    }
}

/* ============================================================
   SIGNATURES (sur la page landscape, en bas)
============================================================ */

$pdf->Ln(8);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(135, 18, pdfText('Signature Trésorier'), 1, 0, 'L');
$pdf->Cell(135, 18, pdfText('Signature Président'), 1, 1, 'L');

/* ============================================================
   SORTIE
============================================================ */

$filename = 'bilan_sessions_caisse_' . $selectedYear . '.pdf';
if (ob_get_length()) {
    ob_end_clean();
}
$pdf->Output('D', $filename);
