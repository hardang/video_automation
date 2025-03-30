<?php
session_start();

    // If the user clicks on the logout button
    if(isset($_POST['logout'])) {
        // Unset all session variables
        session_unset();
        // Destroy the session
        session_destroy();
        // Redirect to the login page or wherever you want
//        header("Location: index.php");
//        exit;
    }

$is_admin_session = $_SESSION['admin'];
$username_session = $_SESSION['username'];
$id_session = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FK - Bergen</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your style.css file -->
</head>

<body>

<?php include 'meny.php'; ?>

<H1>CG - Producer</H1>


<H2>Start videobakgrunn, program og BUG</H2>

<p>- Video loop</p>
<p>- BUG</p>
<p>- Program</p>

    <form action="trigger_initial_elements.php" method="post">
        <input type="hidden" name="type" value="initial">
        <input type="submit" name="submit" value="Send">
    </form>

<H2>Send spesial informasjon til info felt nederst (7 sekunds synlighet)</H2>
    <form action="trigger_initial_elements.php" method="post">
        <input type="hidden" name="type" value="manual_1">
        <input type="text" name="f0" placeholder="Øvre linje (F0)"><br />
        <input type="text" name="f1" placeholder="Nedre linje (F1)"><br />
        <input type="submit" name="submit" value="Send">
    </form>

<H2>Top-informasjon</H2>
    <form action="trigger_initial_elements.php" method="post">
        <input type="hidden" name="type" value="manual_top_show">
        <input type="text" name="f0" value="Direkte" placeholder="Direkte"><br />
        <input type="submit" name="submit" value="Start">
    </form>
    <form action="trigger_initial_elements.php" method="post">
        <input type="hidden" name="type" value="manual_top_remove">
        <input type="submit" name="submit" value="Stop">
    </form>

<H2>Start Live</H2>

<?php
$url = "rtmp://encoder.hardang.com/fk/live";

// Define a regular expression pattern to match RTMP stream URLs
$pattern = "/^rtmp:\/\/[\w\-\.]+(:\d+)?\/[\w\-\/]+$/";

// Validate the URL using preg_match
if (preg_match($pattern, $url)) {
    echo "<p>The RTMP stream URL is valid.</p>";
} else {
    echo "<p>The RTMP stream URL is not valid.</p>";
}

$url = "https://system.hardang.com/HLS/fk_live.m3u8";

// Validate the URL using filter_var()
if (filter_var($url, FILTER_VALIDATE_URL)) {
    echo "<p>The URL is valid.</p>";
} else {
    echo "<p>The URL is not valid.</p>";
}
?>
    <form action="trigger_initial_elements.php" method="post">
	<label>
	Streaming URL: <b>rtmp://encoder.hardang.com/fk/live</b><br />
	Website URL: <b>[HTML] https://www.hardang.no</b><br />
	</label><br />
        <input type="hidden" name="type" value="start-live">
        <input type="text" name="f0" value="rtmp://127.0.0.1/fk/live"><br />
        <input type="submit" name="submit" value="Start live">
    </form>

    <form action="trigger_initial_elements.php" method="post">
        <input type="hidden" name="type" value="go-live">
        <input type="submit" name="submit" value="1-3 <-- LIVE --> 2-3">
    </form>

    <form action="trigger_initial_elements.php" method="post">
        <input type="hidden" name="type" value="stop-live">
        <input type="submit" name="submit" value="Stop live">
    </form>
</body>
</html>
