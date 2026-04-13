<?php
// En haut du fichier ou dans config.php
$mode_dev = false; // Toggle ici
?>

<nav class="navbar navbar-expand-lg navforum px-2">
    <div class="container-fluid">
        <button id="menuForumToggle" class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuForum" aria-controls="menuForum" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="menuForum">
            <ul class="navbar-nav gap-2 text-center navforum_ul">
                <li class="nav-item navforum_li">
                    <a class="nav-link menu-btn <?= ($page == 1) ? 'active-link' : 'inactive-link' ?>" href="depot.php">Collecte</a>
                </li>
                <li class="nav-item navforum_li">
                    <a class="nav-link menu-btn <?= ($page == 2) ? 'active-link' : 'inactive-link' ?>" href="accueil_vente.php">Vente</a>
                </li>
                <li class="nav-item navforum_li">
                    <a class="nav-link menu-btn <?= ($page == 3) ? 'active-link' : 'inactive-link' ?>" href="bilan.php">Bilans</a>
                </li>
                <li class="nav-item navforum_li">
                    <a class="nav-link menu-btn <?= ($page == 4) ? 'active-link' : 'inactive-link' ?>" href="reparation.php">Reparation</a>
                </li>
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] >= 1): ?>
                    <li class="nav-item navforum_li">
                        <a class="nav-link menu-btn <?= ($page == 5) ? 'active-link' : 'inactive-link' ?>" href="administration.php">Administration</a>
                    </li>
                <?php endif; ?>

                <?php if ($mode_dev): ?>
                    <li class="nav-item navforum_li">
                        <a class="nav-link menu-btn dev-link" href="test_debug.php">🧪 Tests</a>
                    </li>
                    <li class="nav-item navforum_li">
                        <a class="nav-link menu-btn dev-link" href="logs.php">📄 Logs</a>
                    </li>
                    <li class="nav-item navforum_li">
                        <a class="nav-link menu-btn dev-link" href="db_inspect.php">🗄️ DB Inspect</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item navforum_li">
                    <a class="nav-link menu-btn inactive-link" href="actions/users/logoutAction.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
(function () {
    const menu = document.getElementById('menuForum');
    const toggle = document.getElementById('menuForumToggle');

    if (!menu || !toggle || typeof bootstrap === 'undefined') {
        return;
    }

    const collapse = bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false });
    let inactivityTimer = null;

    function clearInactivityTimer() {
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
            inactivityTimer = null;
        }
    }

    function startInactivityTimer() {
        clearInactivityTimer();
        inactivityTimer = setTimeout(() => {
            if (menu.classList.contains('show')) {
                collapse.hide();
            }
        }, 5000);
    }

    function resetInactivityTimer() {
        if (menu.classList.contains('show')) {
            startInactivityTimer();
        }
    }

    menu.addEventListener('shown.bs.collapse', startInactivityTimer);
    menu.addEventListener('hidden.bs.collapse', clearInactivityTimer);

    ['click', 'touchstart', 'mousemove', 'keydown', 'scroll'].forEach((eventName) => {
        menu.addEventListener(eventName, resetInactivityTimer);
    });

    toggle.addEventListener('click', () => {
        setTimeout(resetInactivityTimer, 50);
    });
})();
</script>
