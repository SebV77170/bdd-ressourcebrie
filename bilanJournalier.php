<?php
require('actions/users/securityAction.php');
require('actions/db.php');
?>

<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php"); ?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan Journalier';
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

            .bilan-details {
                margin-top: 20px;
                padding: 10px;
                border: 1px solid #000;
                background-color: #f9f9f9;
                max-width: 800px;
                text-align: center;
            }
            .bilan-details span {
                font-weight: bold;
            }
        </style>

        <!-- Corps de page -->
        <div class="container">
            <h1 class="gros_titre">Bilan Journalier</h1>
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevMonth">◀</button>
                    <h2 id="monthYear"></h2>
                    <button id="nextMonth">▶</button>
                </div>
                <div class="calendar" id="calendar"></div>
            </div>
            <div id="bilanDetails" class="bilan-details">
                <!-- Les détails du bilan pour la date sélectionnée seront affichés ici -->
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
                    dateDiv.addEventListener('click', () => fetchBilanDetails(dateDiv.dataset.date));
                    calendar.appendChild(dateDiv);
                }
            }

            // Fonction pour récupérer et afficher les détails du bilan pour une date
            function fetchBilanDetails(date) {
                fetch(`bilanJournalier.php?date=${date}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('bilanDetails').innerHTML = data;
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