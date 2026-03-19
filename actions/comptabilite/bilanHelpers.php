<?php

function getBilanTotalsForYear(PDO $db, int $year): array
{
    $sql = "SELECT
                DATE_FORMAT(STR_TO_DATE(`date`, '%d/%m/%Y'), '%Y-%m-%d') AS date_key,
                DATE_FORMAT(STR_TO_DATE(`date`, '%d/%m/%Y'), '%d/%m/%Y') AS date_label,
                SUM(prix_total_espece) AS montant_encaisse_espece,
                SUM(prix_total_carte) AS montant_encaisse_carte,
                SUM(prix_total_cheque) AS montant_encaisse_cheque,
                SUM(prix_total_virement) AS montant_encaisse_virement
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

function getChargeTravailStatsForYear(PDO $db, int $year): array
{
    $periodStart = sprintf('%d-01-01 00:00:00', $year);
    $periodEnd = sprintf('%d-12-31 23:59:59', $year);

    $eventDaysSql = "SELECT
                        COUNT(DISTINCT DATE(start)) AS total_activity_days,
                        COUNT(DISTINCT CASE WHEN DAYOFWEEK(start) <> 3 THEN DATE(start) END) AS total_sales_days,
                        COUNT(DISTINCT CASE WHEN DAYOFWEEK(start) <> 3 AND MOD(WEEKOFYEAR(start), 2) <> 0 THEN DATE(start) END) AS total_collection_days
                    FROM events
                    WHERE cat_creneau = 0
                      AND start BETWEEN :periodStart AND :periodEnd";

    $eventDaysStmt = $db->prepare($eventDaysSql);
    $eventDaysStmt->execute([
        'periodStart' => $periodStart,
        'periodEnd' => $periodEnd,
    ]);
    $eventDays = $eventDaysStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $hoursSql = "SELECT
                    COALESCE(SUM(CASE WHEN employes.uuid_user IS NULL THEN TIMESTAMPDIFF(MINUTE, events.start, events.end) END), 0) DIV 60 AS benevolat_hours,
                    COALESCE(SUM(CASE WHEN employes.uuid_user IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, events.start, events.end) END), 0) DIV 60 AS employee_hours
                 FROM events
                 INNER JOIN inscription_creneau ON inscription_creneau.id_event = events.id
                 INNER JOIN users ON users.uuid_user = inscription_creneau.id_user
                 LEFT JOIN employes ON employes.uuid_user = users.uuid_user
                 WHERE events.start >= :periodStart
                   AND events.end <= :periodEnd";

    $hoursStmt = $db->prepare($hoursSql);
    $hoursStmt->execute([
        'periodStart' => $periodStart,
        'periodEnd' => $periodEnd,
    ]);
    $hours = $hoursStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
        'total_activity_days' => (int) ($eventDays['total_activity_days'] ?? 0),
        'total_sales_days' => (int) ($eventDays['total_sales_days'] ?? 0),
        'total_collection_days' => (int) ($eventDays['total_collection_days'] ?? 0),
        'benevolat_hours' => (int) ($hours['benevolat_hours'] ?? 0),
        'employee_hours' => (int) ($hours['employee_hours'] ?? 0),
    ];
}
