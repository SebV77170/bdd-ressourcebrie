<?php

function getBilanTotalsForYear(PDO $db, int $year): array
{
    $sql = "SELECT
                DATE_FORMAT(STR_TO_DATE(`date`, '%d/%m/%Y'), '%Y-%m-%d') AS date_key,
                DATE_FORMAT(STR_TO_DATE(`date`, '%d/%m/%Y'), '%d/%m/%Y') AS date_label,
                SUM(prix_total) AS montant_encaisse
            FROM bilan
            WHERE `date` IS NOT NULL
              AND STR_TO_DATE(`date`, '%d/%m/%Y') IS NOT NULL
              AND YEAR(STR_TO_DATE(`date`, '%d/%m/%Y')) = :year
            GROUP BY date_key, date_label
            ORDER BY date_key DESC";

    $statement = $db->prepare($sql);
    $statement->execute(['year' => $year]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}
