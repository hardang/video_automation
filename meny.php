<?php
// Ensure session variables are set before using them
$is_admin_session = $_SESSION['admin'] ?? false;
?>

<ul>
    <li><a href='index.php'>Hjem</a></li>
    <?php if ($is_admin_session == "") : ?>
    <li><a href='library.php'>FK-Play</a></li>
    <?php endif; ?>

    <?php if ($is_admin_session !== false) : ?>
        <!-- Display this item if any user is logged in -->
        <li><a href='video_archive.php'>Videoarkiv</a></li>
        <li><a href='library.php'>FK-Play</a></li>

        <li class='dropdown'>
            <a href='javascript:void(0)' class='dropbtn'>Sendeplan</a>
            <div class='dropdown-content'>
                <a href='add2schedule.php'>Sendeplan</a>

                <?php if ($is_admin_session == "1") : ?>
                    <a href='locked_airtimes.php'>Faste sendetider</a>
                <?php endif; ?>

                <a href='tv_sendeplan_gaps.php'>Hull i sendeplanen</a>
                <a href='tv_sendeplan_error.php'>Feil på sendeplanen</a>
            </div>
        </li>

        <?php if ($is_admin_session == "1") : ?>
            <li><a href='cg_producer.php'>Grafikk</a></li>
        <?php endif; ?>

        <li style='float:right'><a href='logout.php' id='logout'>Logout</a></li>

        <?php if ($is_admin_session == "1") : ?>
            <li style='float:right'><a href='users.php'>Brukere</a></li>
        <?php endif; ?>
    <?php endif; ?>
</ul>

