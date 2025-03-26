<?php
require('actions/users/securityAction.php');
require('actions/db.php');

if (isset($_GET['date']) && isset($_GET['count']) && $_GET['count'] === 'true') {
    $date = $_GET['date'];

    // Compter le nombre de tickets pour la date donnée
    $query = $db->prepare('SELECT COUNT(*) AS count FROM ticketdecaisse WHERE DATE(date_achat_dt) = ?');
    $query->execute([$date]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Retourner le résultat en JSON
    echo json_encode(['count' => $result['count']]);
    exit;
}

if (isset($_GET['date'])) {
    $date = $_GET['date'];

    // Récupérer les tickets de caisse associés à la date
    $query = $db->prepare('SELECT * FROM ticketdecaisse WHERE DATE(date_achat_dt) = ? ORDER BY date_achat_dt DESC');
    $query->execute([$date]);
    $tickets = $query->fetchAll(PDO::FETCH_ASSOC);

    if ($tickets) {
        echo '<table>';
        echo '<tr>
                <th>N° Ticket</th>
                <th>Nom du vendeur</th>
                <th>Date</th>
                <th>Nombre d\'articles</th>
                <th>Moyen de Paiement</th>
                <th>Prix</th>
                <th>Lien vers ticket</th>
              </tr>';
        foreach ($tickets as $ticket) {
            $prixEuro = $ticket['prix_total'] / 100;
            echo '<tr>
                    <td>' . $ticket['id_ticket'] . '</td>
                    <td>' . $ticket['nom_vendeur'] . '</td>
                    <td>' . $ticket['date_achat_dt'] . '</td>
                    <td>' . $ticket['nbr_objet'] . '</td>
                    <td>' . $ticket['moyen_paiement'] . '</td>
                    <td>' . $prixEuro . '€</td>
                    <td><a href="ticketdecaisseapresvente.php?id_ticket=' . $ticket['id_ticket'] . '">Voir le ticket</a></td>
                  </tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Aucun ticket de caisse trouvé pour cette date.</p>';
    }
    exit;
}
?>

<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php"); ?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan des Tickets de caisse';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");

            if ($_SESSION['admin'] >= 1) {
        ?>
        <style type="text/css">
            .calendar-container {
                text-align: center;
                margin: 20px auto;
            }

            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .calendar-header button {
                padding: 5px 10px;
                background-color: #23A3D9;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }

            .calendar-header button:hover {
                background-color: #1a82b1;
            }

            .calendar-header h2 {
                margin: 0;
            }

            .calendar {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 1px;
                margin: 0 auto;
                width: 100%;
                max-width: 500px;
                border: 1px solid #000;
            }

            .calendar div {
                padding: 10px;
                text-align: center;
                background-color: #f0f0f0;
                border: 1px solid #000;
                cursor: pointer;
            }

            .calendar div:hover {
                background-color: #e0e0e0;
            }

            .calendar div.header {
                background-color: #000;
                color: #fff;
            }

            .tickets {
                margin-top: 20px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f0f0f0;
            }

            tr:nth-child(even) {
                background-color: #f2f2f2;
            }

            tr:hover {
                background-color: #e0e0e0;
            }
        </style>

        <!-- Corps de page -->
        <div class="container">
            <h1 class="gros_titre">Bilan des Tickets de Caisse</h1>
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevMonth">◀</button>
                    <h2 id="monthYear"></h2>
                    <button id="nextMonth">▶</button>
                </div>
                <div class="calendar" id="calendar"></div>
            </div>
            <div class="tickets" id="tickets">
                <!-- Les tickets de caisse associés à la date sélectionnée seront affichés ici -->
            </div>
        </div>

        <script>
            const calendar = document.getElementById('calendar');
            const monthYear = document.getElementById('monthYear');
            const prevMonthButton = document.getElementById('prevMonth');
            const nextMonthButton = document.getElementById('nextMonth');

            let currentDate = new Date();

            // Fonction pour afficher le calendrier
            function renderCalendar(date) {
                calendar.innerHTML = ''; // Réinitialiser le calendrier
                const year = date.getFullYear();
                const month = date.getMonth();
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                // Afficher le mois et l'année
                const monthNames = [
                    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
                ];
                monthYear.textContent = `${monthNames[month]} ${year}`;

                // Ajouter les jours de la semaine
                const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
                daysOfWeek.forEach(day => {
                    const header = document.createElement('div');
                    header.textContent = day;
                    header.classList.add('header');
                    calendar.appendChild(header);
                });

                // Ajouter les jours du mois
                const firstDay = new Date(year, month, 1).getDay(); // Jour de la semaine du 1er jour
                const offset = (firstDay === 0 ? 6 : firstDay - 1); // Ajuster pour que lundi soit le premier jour

                // Ajouter des cases vides pour les jours avant le 1er du mois
                for (let i = 0; i < offset; i++) {
                    const emptyDiv = document.createElement('div');
                    calendar.appendChild(emptyDiv);
                }

                // Ajouter les jours du mois
                for (let day = 1; day <= daysInMonth; day++) {
                    const dateDiv = document.createElement('div');
                    dateDiv.textContent = day;
                    dateDiv.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    dateDiv.addEventListener('click', () => fetchTickets(dateDiv.dataset.date));
                    calendar.appendChild(dateDiv);

                    // Récupérer le nombre de tickets pour chaque date
                    fetch(`bilanticketDeCaisse.php?date=${dateDiv.dataset.date}&count=true`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.count > 0) {
                                const countSpan = document.createElement('span');
                                countSpan.textContent = ` (${data.count})`;
                                countSpan.style.fontSize = '0.8em';
                                countSpan.style.color = 'red';
                                dateDiv.appendChild(countSpan);
                            }
                        })
                        .catch(error => console.error('Erreur:', error));
                }
            }

            // Fonction pour récupérer les tickets de caisse associés à une date
            function fetchTickets(date) {
                fetch(`bilanticketDeCaisse.php?date=${date}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('tickets').innerHTML = data;
                    })
                    .catch(error => console.error('Erreur:', error));
            }

            // Gestion des boutons de navigation
            prevMonthButton.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar(currentDate);
            });

            nextMonthButton.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar(currentDate);
            });

            // Initialiser le calendrier
            renderCalendar(currentDate);
        </script>

        <?php
            } else {
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
        ?>
    </body>
</html>