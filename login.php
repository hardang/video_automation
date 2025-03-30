<?php
// Start the session if not already started
//if (session_status() === PHP_SESSION_NONE) {
//    session_start();

	// Redirect only if no output has been sent
//	if (!isset($_SESSION['user_id'])) {
	    // Start output buffering if necessary
//	    ob_start();

	    // Make sure no output has occurred
//	    header("Location: login.php");
//	    exit; // Always exit after calling header()
//	}
//}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If the user clicks on the logout button
if (isset($_POST['logout']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    session_unset();
    session_destroy();
    //header("Location: index.php");
    exit;
}

// Ensure session variables are set before using them
$is_admin_session = $_SESSION['admin'] ?? false;
$username_session = $_SESSION['username'] ?? null;
$id_session = $_SESSION['user_id'] ?? null;

if ($username_session) {
    $welcome_message = "<p>Velkommen, " . htmlspecialchars($username_session, ENT_QUOTES, 'UTF-8') . "! Du er nå logget inn.</p>";
}

// Database connection (Consider moving sensitive data like credentials to a secure config file)
$servername = "";
$db_username = "";
$db_password = ""; // For better security, use environment variables or a separate config file.
$database = "";

$conn = new mysqli($servername, $db_username, $db_password, $database);
if ($conn->connect_error) {
    // More secure error handling
    error_log("Connection failed: " . $conn->connect_error);
    die("An error occurred, please try again later.");
}


ob_start();  // Start output buffering

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepared statement for login query
    $sql = "SELECT id, username, password, admin FROM fk_users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Update session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['admin'] = $row['admin'];
                
                // Redirect to index.php to ensure the new session data is used
                //header("Location: index.php");
                exit;
            } else {
                $error = "<p>Invalid username or password</p>";
            }
        } else {
            $error = "<p>Invalid username or password</p>";
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement: " . $conn->error);
        $error = "<p>An error occurred, please try again later.</p>";
    }
}

ob_end_flush();  // Flush the output buffer

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>
<div class="set_center">
    <h2>Velkommen!</h2>
    <?php if (isset($welcome_message)) : ?>
        <p><?php echo $welcome_message; ?></p>
        <?php if (isset($_SESSION['username'])) : ?>
            <!-- Logout form with CSRF token -->
            <form action="index.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="submit" name="logout" value="Logg ut">
            </form>

            <!-- If admin, display admin functions -->
            <?php if ($_SESSION['admin'] == "1") : ?>
                <div class="rramme">
                    <p>Legg til ny bruker</p>
                    <form method="post" action="register.php">
                        <input type="text" id="username" name="username" placeholder="Brukernavn" required><br>
                        <input type="password" id="password" name="password" placeholder="Passord" required><br>
                        <input type="submit" value="Registrer">
                    </form>
                </div>
            <?php endif; ?>

            <?php include 'uploads/index.php'; ?>
        <?php endif; ?>
    <?php else : ?>
        <h2>Login</h2>
        <div class="ramme">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" id="username" name="username" placeholder="Brukernavn" required><br>
                <input type="password" id="password" name="password" placeholder="Passord" required><br>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="submit" value="Login">
            </form>
        </div>
        <?php if (isset($error)) { echo $error; } ?>
    <?php endif; ?>
</div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

