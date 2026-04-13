<?php
// En haut du fichier ou dans config.php
$mode_dev = false; // Toggle ici
?>

<nav class="navbar navbar-expand-lg navforum px-2">
    <div class="container-fluid">

        <!-- Bouton hamburger -->
        <button class="navbar-toggler custom-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuForum"
                aria-controls="menuForum"
                aria-expanded="false"
                aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse justify-content-center" id="menuForum">
            <ul class="navbar-nav gap-2 text-center">

                <!-- Collecte -->
                <li class="nav-item">
                    <a class="nav-link menu-btn <?= ($page == 1) ? 'active-link' : 'inactive-link' ?>"
                       href="depot.php">
                        Collecte
                    </a>
                </li>

                <!-- Bilans -->
                <li class="nav-item">
                    <a class="nav-link menu-btn <?= ($page == 3) ? 'active-link' : 'inactive-link' ?>"
                       href="bilan.php">
                        Bilans
                    </a>
                </li>

                <!-- Administration -->
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] >= 1): ?>
                <li class="nav-item">
                    <a class="nav-link menu-btn <?= ($page == 5) ? 'active-link' : 'inactive-link' ?>"
                       href="administration.php">
                        Administration
                    </a>
                </li>
                <?php endif; ?>

                <!-- Mode dev -->
                <?php if ($mode_dev): ?>
                <li class="nav-item">
                    <a class="nav-link menu-btn orange inactive-link" href="test_debug.php">
                        🧪 Tests
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-btn orange inactive-link" href="logs.php">
                        📄 Logs
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-btn orange inactive-link" href="db_inspect.php">
                        🗄️ DB Inspect
                    </a>
                </li>
                <?php endif; ?>

                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link menu-btn inactive-link" href="actions/users/logoutAction.php">
                        Logout
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>