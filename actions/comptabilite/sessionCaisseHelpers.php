<?php

function getSessionCaisseColumns(PDO $db): array
{
    return $db->query('SHOW COLUMNS FROM session_caisse')->fetchAll(PDO::FETCH_ASSOC);
}

function findSessionCaisseDateColumn(array $columnNames): ?string
{
    if (in_array('opened_at_utc', $columnNames, true)) {
        return 'opened_at_utc';
    }

    $possibleDateColumns = ['opened_at_utc', 'date_session', 'date_fermeture', 'date_ouverture', 'date', 'created_at', 'updated_at'];
    foreach ($possibleDateColumns as $possibleDateColumn) {
        if (in_array($possibleDateColumn, $columnNames, true)) {
            return $possibleDateColumn;
        }
    }

    foreach ($columnNames as $columnName) {
        if (stripos($columnName, 'date') !== false) {
            return $columnName;
        }
    }

    return null;
}

function findSessionCaisseEcartColumn(array $columnNames): ?string
{
    foreach ($columnNames as $columnName) {
        if (stripos($columnName, 'ecart') !== false) {
            return $columnName;
        }
    }

    return null;
}

function getSessionCaisseYears(PDO $db, string $dateColumn): array
{
    $yearQuery = $db->query("SELECT DISTINCT YEAR($dateColumn) AS annee FROM session_caisse WHERE $dateColumn IS NOT NULL ORDER BY annee DESC");
    $years = $yearQuery->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_filter($years, fn($year) => $year !== null));
}

function getSessionCaisseRowsForYear(PDO $db, string $dateColumn, int $year): array
{
    $sql = "SELECT * FROM session_caisse WHERE YEAR($dateColumn) = :year ORDER BY $dateColumn DESC";
    $statement = $db->prepare($sql);
    $statement->execute(['year' => $year]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function buildEcartRecap(array $rows, string $ecartColumn): array
{
    $recap = [
        'negative' => 0,
        'positive' => 0,
        'count' => 0,
    ];

    foreach ($rows as $row) {
        $ecartValue = isset($row[$ecartColumn]) ? (float) $row[$ecartColumn] : 0;
        if ($ecartValue < 0) {
            $recap['negative'] += $ecartValue;
        } elseif ($ecartValue > 0) {
            $recap['positive'] += $ecartValue;
        }
        $recap['count'] += 1;
    }

    return $recap;
}

function formatEcartValue(float $value): string
{
    return number_format($value/100, 2, ',', ' ') . ' €';
}
