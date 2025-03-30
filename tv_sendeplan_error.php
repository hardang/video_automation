<?php
// Start session
session_start();

// Check if the user is logged in as admin
$eier_session = $_SESSION['admin'];
$is_admin_session = $_SESSION['admin'];
$username_session = $_SESSION['username'];
$id_session = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HardangTV - feil på sendeplan</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your style.css file -->
</head>

<body>

<?php include 'meny.php'; ?>

<H1>FK - Bergen</H1>

<div class="container">
    <div class="column1">
        <H3>Feil på sendeplanen</H3>

        <div style="overflow-y: auto; max-height: 1000px; border: 0px solid white; width: 1400px;">
            <table>
                <?php
                // Database connection parameters
                $servername = "localhost";
                $username = "c4frikanalen";
                $password = "olavH2610!";
                $dbname = "c4frikanalen_sendeplan";

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Check if form is submitted for user removal
                if (isset($_POST["remove"])) {
                    $db_id = $_POST['db_id'];

                    // Prepare and execute the removal query
                    $stmt = $conn->prepare("DELETE FROM fk_sendeplan WHERE db_id = ?");
                    $stmt->bind_param('i', $db_id);
                    if ($stmt->execute()) {
                        // echo "<p>Program removed successfully</p>";
                    } else {
                        echo "<p>Error removing program: " . $conn->error . "</p>";
                    }
                    $stmt->close();
                }

                $current_time = new DateTime();
                $formatted_time = $current_time->format('Y-m-d H:i:s.u');

                // Query to retrieve data from the database
                $sql = "SELECT fk_sendeplan.*, fk_programbank.prog_tittel 
                        FROM fk_sendeplan 
                        JOIN fk_programbank ON fk_sendeplan.prog_id = fk_programbank.prog_id 
                        WHERE fk_sendeplan.sendetid > ? 
                        ORDER BY fk_sendeplan.sendetid";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $formatted_time);
                $stmt->execute();
                $result = $stmt->get_result();

                $set_no_results = 0;

                if ($result->num_rows > 0) {
                    $start_time = null;
                    $start_timestamp = null;

                    while ($row = $result->fetch_assoc()) {
                        $next_startid = substr($row["sendetid"], 0, 19);
                        $next_startid_datetime = new DateTime($next_startid);
                        $next_timestamp = $next_startid_datetime->getTimestamp();

                        // Compare the end time of the current entry with the start time of the next to detect overlaps
                        if (isset($start_timestamp) && $next_timestamp < $start_timestamp) {
                            $set_no_results = 1;

                            $gap = $start_timestamp - $next_timestamp;
                            $days = floor($gap / (24 * 3600));
                            $remaining_seconds = $gap % (24 * 3600);
                            $hours = floor($remaining_seconds / 3600);
                            $remaining_seconds %= 3600;
                            $minutes = floor($remaining_seconds / 60);
                            $seconds = $remaining_seconds % 60;

                            if ($days > 0) {
                                $gap_display = sprintf('%d days, %02d:%02d:%02d', $days, $hours, $minutes, $seconds);
                            } else {
                                $gap_display = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                            }

                            $start_time_view = substr($row['sendetid'], 0, 19);

                            echo "<tr><td>Overlapping detected: </td>
                                      <td>{$start_time_view}</td>
                                      <td>Lagt til: " . htmlspecialchars($row['producer']) . "</td>
                                      <td>Tittel: " . htmlspecialchars($row['prog_tittel']) . "</td>
                                      <td>
                                          <form method='post' action=''>
                                              <input type='hidden' name='db_id' value='" . htmlspecialchars($row['db_id']) . "'>
                                              <input type='submit' name='remove' value='Slett'>
                                          </form>
                                      </td>
                                  </tr>\n";
                        }

                        // Calculate end time of the current program
                        $start_time = $row['sendetid'];
                        $duration_time_str = substr($row["duration"], 0, 8);
                        $duration_time_parts = explode(':', $duration_time_str);
                        $total_seconds = $duration_time_parts[0] * 3600 + $duration_time_parts[1] * 60 + $duration_time_parts[2] + 8;

                        $start_datetime = new DateTime($start_time);
                        $start_datetime->add(new DateInterval('PT' . $total_seconds . 'S'));
                        $start_timestamp = $start_datetime->getTimestamp();
                    }
                } else {
                    echo "0 results";
                }

                if ($set_no_results === 0) {
                    echo "<H4>Ingen feil funnet</H4>";
                }

                // Close connection
                $stmt->close();
                $conn->close();
                ?>
            </table>
        </div>
    </div>

    <div class="column2">
        <!-- Additional content can go here -->
    </div>
</div>

</body>
</html>
