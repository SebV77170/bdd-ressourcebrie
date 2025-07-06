<?php
// En haut du fichier ou dans config.php
$mode_dev = true; // Toggle ici
?>

<nav class="navforum">
    <ul class="navforum_ul">
        <li class="navforum_li <?php echo ($page == 1) ? 'vert' : 'bleu'; ?>"><a class="lien_li" href="depot.php">Collecte</a></li>
        <li class="navforum_li <?php echo ($page == 2) ? 'vert' : 'bleu'; ?>"><a class="lien_li" href="accueil_vente.php">Vente</a></li>
        <li class="navforum_li <?php echo ($page == 3) ? 'vert' : 'bleu'; ?>"><a class="lien_li" href="bilan.php">Bilans</a></li>
        <li class="navforum_li <?php echo ($page == 4) ? 'vert' : 'bleu'; ?>"><a class="lien_li" href="reparation.php">Reparation</a></li>
        
        <?php if ($mode_dev): ?>
            <li class="navforum_li orange"><a class="lien_li" href="test_debug.php">🧪 Tests</a></li>
            <li class="navforum_li orange"><a class="lien_li" href="logs.php">📄 Logs</a></li>
            <li class="navforum_li orange"><a class="lien_li" href="db_inspect.php">🗄️ DB Inspect</a></li>
        <?php endif; ?>
        
        <li class="navforum_li bleu"><a class="lien_li" href="actions/users/logoutAction.php">Logout</a></li>     
    </ul>
</nav>
