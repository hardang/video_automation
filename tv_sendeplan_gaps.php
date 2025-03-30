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
    <title>HardangTV - sendeplan</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your style.css file -->
</head>

<body>

    <?php include 'meny.php'; ?>

    <h1>FK - Bergen</h1>

    <div class="container">
        <div class="column1">
            <h3>Hull i sendeplanen</h3>
            <div style="overflow-y: auto; max-height: 1000px; border: 0px solid white;">
                <table>
                    <?php
                    // Database connection parameters
		    $servername = getenv('DB_SERVER');
		    $username = getenv('DB_USERNAME');
		    $password = getenv('DB_PASSWORD');
		    $dbname = getenv('DB_NAME');

                    // Create connection
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $current_time = new DateTime();
                    $formatted_time = $current_time->format('Y-m-d H:i:s.u');

                    // Query to retrieve data from the database
                    $sql = "SELECT sendetid, duration FROM fk_sendeplan WHERE sendetid > ? ORDER BY sendetid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('s', $formatted_time);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $start_time = null;
                        $start_timestamp = null;

                        while ($row = $result->fetch_assoc()) {
                            $next_startid = substr($row["sendetid"], 0, 19);
                            $next_startid_datetime = new DateTime($next_startid);
                            $next_timestamp = $next_startid_datetime->getTimestamp();

                            if (isset($start_timestamp)) {
                                $gap = $next_timestamp - $start_timestamp;

                                if ($gap > 120) {
                                    if (!empty($start_time)) {
                                        // Calculate time difference in a readable format
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

                                        $start_time_view = substr($start_time, 0, 19);
                                        echo "<tr><td>Gap detected: </td><td>" . $start_time_view . "</td><td>" . $gap_display . "</td></tr>\n";
                                    }
                                }
                            }

                            // Update $start_time and calculate $end_time for the next comparison
                            $start_time = $row['sendetid'];







//                            $duration_time_str = substr($row["duration"], 0, 8);
//                            $duration_time_parts = explode(':', $duration_time_str);
//                            $total_seconds = $duration_time_parts[0] * 3600 + $duration_time_parts[1] * 60 + $duration_time_parts[2] + 8;
$duration_time_str = substr($row["duration"], 0, 8);
$duration_time_parts = explode(':', $duration_time_str);

// Check if we have exactly three parts for hours, minutes, seconds
if (count($duration_time_parts) === 3) {
    // Ensure parts are numeric before performing calculations
    $hours = is_numeric($duration_time_parts[0]) ? (int)$duration_time_parts[0] : 0;
    $minutes = is_numeric($duration_time_parts[1]) ? (int)$duration_time_parts[1] : 0;
    $seconds = is_numeric($duration_time_parts[2]) ? (int)$duration_time_parts[2] : 0;

    // Calculate total seconds
    $total_seconds = $hours * 3600 + $minutes * 60 + $seconds + 8;
} else {
    // Handle error for invalid duration format



//    echo "<tr><td>Error: Invalid duration format for entry. Expected format is HH:MM:SS.</td></tr>\n";
    $total_seconds = 0; // Set to 0 or some default value if needed
}





                            $start_datetime = new DateTime($start_time);
                            $start_datetime->add(new DateInterval('PT' . $total_seconds . 'S'));
                            $start_timestamp = $start_datetime->getTimestamp();
                        }

                        // Handle the last entry
                        if (isset($start_time)) {
                            $start_time_view = substr($start_time, 0, 19);
                            echo "<tr><td>Gap detected: </td><td>" . $start_time_view . "</td><td>Siste episode</td></tr>\n";
                        }
                    } else {
                        echo "<tr><td>No schedule found</td></tr>";
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
