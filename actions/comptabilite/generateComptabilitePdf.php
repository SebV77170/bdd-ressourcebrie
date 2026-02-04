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

function pdfText(string $text): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetTitle(pdfText('Bilan des sessions de caisse'), false);
$pdf->SetAuthor(pdfText("La Ressourcerie de Brie"), false);
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
$pdf->Cell(70, 8, pdfText(formatEcartValue($recap['negative'])), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText(formatEcartValue($recap['positive'])), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText((string) $sessionCountWithEcart), 1, 1, 'L');

$pdf->Ln(4);
$pdf->Cell(70, 8, pdfText('Écart net'), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText('Total sessions année'), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText('Sessions sans écart'), 1, 1, 'L');
$pdf->Cell(70, 8, pdfText(formatEcartValue($recapNet)), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText((string) $sessionCount), 1, 0, 'L');
$pdf->Cell(60, 8, pdfText((string) ($sessionCount - $sessionCountWithEcart)), 1, 1, 'L');

$pdf->Ln(6);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 7, pdfText('Total fond initial'), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText('Total montant réel'), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText('Total carte / chèque / virement'), 1, 1, 'L');
$pdf->Cell(70, 7, pdfText(formatEcartValue($totals['fond_initial'])), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText(formatEcartValue($totals['montant_reel'])), 1, 0, 'L');
$pdf->Cell(60, 7, pdfText(formatEcartValue($totals['montant_reel_carte']) . ' / ' . formatEcartValue($totals['montant_reel_cheque']) . ' / ' . formatEcartValue($totals['montant_reel_virement'])), 1, 1, 'L');

$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, pdfText('Détail des sessions avec écart'), 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 10);

$columnNames = array_column($columns, 'Field');
$displayColumns = [];
foreach (['id_session'] as $possibleIdColumn) {
    if (in_array($possibleIdColumn, $columnNames, true)) {
        $displayColumns[] = $possibleIdColumn;
        break;
    }
}
foreach (['opened_at_utc', $dateColumn] as $possibleDateColumn) {
    if ($possibleDateColumn && in_array($possibleDateColumn, $columnNames, true)) {
        $displayColumns[] = $possibleDateColumn;
        break;
    }
}
foreach (['closed_at_utc', 'closed_at'] as $possibleCloseColumn) {
    if (in_array($possibleCloseColumn, $columnNames, true)) {
        $displayColumns[] = $possibleCloseColumn;
        break;
    }
}
foreach (['utilisateur_ouverture', 'responsable_ouverture', 'utilisateur_fermeture', 'responsable_fermeture'] as $personColumn) {
    if (in_array($personColumn, $columnNames, true)) {
        $displayColumns[] = $personColumn;
    }
}
foreach (['fond_initial', 'montant_reel', 'montant_reel_carte', 'montant_reel_cheque', 'montant_reel_virement', $ecartColumn] as $moneyColumn) {
    if ($moneyColumn && in_array($moneyColumn, $columnNames, true)) {
        $displayColumns[] = $moneyColumn;
    }
}
foreach (['commentaire', 'cashiers', 'poste', 'issecondaire'] as $metaColumn) {
    if (in_array($metaColumn, $columnNames, true)) {
        $displayColumns[] = $metaColumn;
    }
}

$columnLabels = [];
foreach ($displayColumns as $index => $columnName) {
    $columnLabels[] = ucwords(str_replace('_', ' ', $columnName));
}

foreach ($displayColumns as $index => $columnName) {
    $width = 190 / max(count($displayColumns), 1);
    $pdf->Cell($width, 7, pdfText($columnLabels[$index]), 1, 0, 'L');
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 9);
if (empty($rowsWithEcart)) {
    $pdf->Cell(0, 6, pdfText('Aucune session avec écart pour cette année.'), 1, 1, 'L');
} else {
    foreach ($rowsWithEcart as $row) {
        foreach ($displayColumns as $index => $columnName) {
            $width = 190 / max(count($displayColumns), 1);
            $value = $row[$columnName] ?? '';
            $pdf->Cell($width, 6, pdfText((string) $value), 1, 0, 'L');
        }
        $pdf->Ln();
    }
}

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(90, 20, pdfText('Signature Trésorier'), 1, 0, 'L');
$pdf->Cell(90, 20, pdfText('Signature Président'), 1, 1, 'L');

$filename = 'bilan_sessions_caisse_' . $selectedYear . '.pdf';
if (ob_get_length()) {
    ob_end_clean();
}
$pdf->Output('D', $filename);
