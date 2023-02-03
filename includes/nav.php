<nav class="navforum">
        <ul>
                <li <?php if($page == 1){echo 'class="vert"';}else{echo 'class="bleu"';} ?>><a href="depot.php">Collecte</a></li>
                <li <?php if($page == 2){echo 'class="vert"';}else{echo 'class="bleu"';} ?>><a href="accueil_vente.php">Vente</a></li>
                <li <?php if($page == 3){echo 'class="vert"';}else{echo 'class="bleu"';} ?>><a href="bilan.php">Bilans</a></li>
                <li <?php if($page == 4){echo 'class="vert"';}else{echo 'class="bleu"';} ?>><a href="reparation.php">Reparation</a></li>
                <li class="bleu"><a href="actions/users/logoutAction.php">Logout</a></li>     
        </ul>
</nav>