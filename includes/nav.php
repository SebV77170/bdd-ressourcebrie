<nav class="navforum">
        <ul class="navforum_ul">
                <li class="navforum_li <?php if($page == 1){echo 'vert';}else{echo 'bleu';} ?>"><a class="lien_li" href="depot.php">Collecte</a></li>
                <li class="navforum_li <?php if($page == 2){echo 'vert';}else{echo 'bleu';} ?>"><a class="lien_li" href="accueil_vente.php">Vente</a></li>
                <li class="navforum_li <?php if($page == 3){echo 'vert';}else{echo 'bleu';} ?>"><a class="lien_li" href="bilan.php">Bilans</a></li>
                <li class="navforum_li <?php if($page == 4){echo 'vert';}else{echo 'bleu';} ?>"><a class="lien_li" href="reparation.php">Reparation</a></li>
                <li class="navforum_li bleu"><a class="lien_li" href="actions/users/logoutAction.php">Logout</a></li>     
        </ul>
</nav>