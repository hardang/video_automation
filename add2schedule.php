<?php
// Start session
session_start();

// Check if the user is logged in as admin
$eier_session = $_SESSION['admin'];
$is_admin_session = $_SESSION['admin'];
$username_session = $_SESSION['username'];
$id_session = $_SESSION['user_id'];


// Initialize selected date with the current date
$selected_date = date("Y-m-d");

// Initialize selected date with the current date
$selected_date_add = date("Y-m-d H:i");

if ($is_admin_session >= "0") {
    // DB connection info
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

    if(empty($first_day)) {
        $first_day = "0000-00-00";
    } else {
        $first_day = $first_day;
    }

    if(isset($_POST['selected_date']))
	{
	    $selected_date = $_POST['selected_date'];
	}

    // Check if form is submitted for user removal
    if (isset($_POST["remove"])) {
        $db_id = $_POST['db_id'];

        // Remove the user from the database
        $sql_remove = "DELETE FROM fk_sendeplan WHERE db_id = '$db_id'";

        if ($conn->query($sql_remove) === TRUE) {
            // echo "<p>Program removed successfully</p>";
        } else {
            echo "<p>Error removing program: " . $conn->error . "</p>";
        }
    }

    // Check if form is submitted for user removal
    if (isset($_POST["add"])) {
        $compiled_string = explode("_", $_POST['prog_id']);

	$element_count = count($compiled_string);

        $sendetid = $_POST['sendetid'];
        $prog_id = $compiled_string[0];
        $programtittel = $compiled_string[1];
        $duration = $compiled_string[2];

if($element_count == 5)
{
        $filnavn = $compiled_string[3] . "_" . $compiled_string[4];
}
else{
        $filnavn = $compiled_string[3];
}

        // Create a DateTime object from the input string
        $sendetid = new DateTime($sendetid);

        // Format the DateTime object to the desired output format
        $sendetid = $sendetid->format('Y-m-d H:i:s.u');

        function generate_uuid() {
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
        }

        // Generate a UUID using the pure PHP function
        $db_id = generate_uuid();

        // Convert the UUID to string
        $db_id_str = (string)$db_id;

        // Insert new record into the database
        $sql_add2schedule = "INSERT INTO fk_sendeplan (db_id, sendetid, filnavn, duration, prog_id, producer) VALUES ('$db_id', '$sendetid', '$filnavn', '$duration', '$prog_id', 'manual')";

        if ($conn->query($sql_add2schedule) === TRUE) {
            // echo "<p>Program aded successfully</p>";
        } else {
            echo "Error: " . $sql_add2schedule . "<br>" . $conn->error;
        }
    }

    $currentDateTime = new DateTime();
    // Format the current date-time to the desired format
    //$current_date_time = $currentDateTime->format('Y-m-d H:i:s.u');

// Create a DateTime object for the selected date
$currentDateTime = new DateTime($selected_date);

// Format the DateTime object to accept all times within the selected date
$start_time = $currentDateTime->format('Y-m-d 00:00:00.000000'); // Start of the selected date
$end_time = $currentDateTime->format('Y-m-d 23:59:59.999999'); // End of the selected date

// Example usage:
//echo "Start Time: $start_time\n";
//echo "End Time: $end_time\n";

    // Retrieve data from the database
    //$sql_select = "SELECT * FROM fk_sendeplan WHERE sendetid > '$start_time' AND sendetid < '$end_time' ORDER BY sendetid ASC";
$sql_select = "SELECT fk_sendeplan.*, fk_programbank.prog_tittel, fk_programbank.filnavn
 FROM fk_sendeplan
 JOIN fk_programbank ON fk_sendeplan.prog_id = fk_programbank.prog_id
 WHERE fk_sendeplan.sendetid > '$start_time' AND fk_sendeplan.sendetid < '$end_time'
 ORDER BY fk_sendeplan.sendetid ASC";

$result_select = $conn->query($sql_select);

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Legg til på sendeplanen</title>
        <link rel="stylesheet" href="style.css"> <!-- Link to your style.css file -->
        <style>
            .user-actions {
                display: flex;
                align-items: center;
            }
            .user-actions button {
                margin-left: 10px;
            }
            #updateForm {
                display: none;
            }

            input[type=text], input[type=password], input[type=email], textarea {
                width: 100%;
                padding: 7px;
                margin: 4px 2px 4px 2px;
                border: 0; // remove default border
                border-bottom: 1px solid #eee; // add only bottom border
                border: 0;
                box-shadow:0 0 15px 4px rgba(255,255,255,0.06);
                border-radius:10px;
            }

            .ramme {
                width: 600px;
                margin: 20px;
                padding: 20px;
                border: 1px solid #fff;
            }
            table {
                border: 0px solid #fff;
            }
            tr, td {
                border: 0px solid #fff;
            }
        </style>
    </head>
    <body>

    <?php include 'meny.php'; ?>

    <H4>Du er logget inn med brukernavn,
        <?php echo $username_session;?>
    </H4>

    <h2>Sendeplanen</h2>

    <form id="dateForm" action="" method="POST">
        <input type="date" id="date" name="selected_date" value="<?php echo $selected_date;?>" onchange="submitForm()">

    </form>

    <div class="container">
        <div class="column3l">

	  <div style="overflow-y: auto; max-height: 600px; border: 0px solid white;">
    <table id="scrollable-table">
<thead>
      <tr>
        <th>Sendetid</th>
        <th>Tittel</th>
        <th>Spilletid</th>
        <th></th>
	<th></th>
      </tr>
</thead>
<tbody>


                <?php
                if ($result_select->num_rows > 0) {
                    while ($row = $result_select->fetch_assoc()) {
                        $reduced_datetime_str = substr($row["sendetid"], 0, 19);
                        $reduced_duration_str = substr($row["duration"], 0, 8);
                        echo "<tr><td><b>" . $reduced_datetime_str . "</b></td><td>" . $row["prog_tittel"] . "</td><td><b>" . $reduced_duration_str . "</b></td>";




$filnavn = $row["filnavn"]; // e.g., "frikanalen/626445/broadcast/Infoplakat Frikanalen.dv"

// Transform the filename
$filnavn = str_replace("frikanalen", "webfiles", $filnavn); // Replace "frikanalen" with "mp4web"
$filnavn = str_replace("broadcast", "mp4web", $filnavn); // Replace "broadcast" with "web_base"
$filnavn = preg_replace("/\.[a-zA-Z0-9]+$/", ".mp4", $filnavn);

// Now $filnavn will look like: "mp4web/626445/web_base/Infoplakat Frikanalen.mp4"


                        if($is_admin_session == '1') {
                            ?>
                            <td>
                                <form method='post' action=''>
                                    <input type='hidden' name='db_id' value='<?php echo $row["db_id"];?>'>
                                    <input type='submit' name='remove' value='Slett'>
                                </form>
                            </td>
		            <td> <?php
				echo "<button onclick=\"playVideo('$filnavn')\">Play</button>";
				?>
			    </td>
                            <?php
                        } else{}
                        echo "<tr>";
                    }
                } else {
                    echo "No records found";
                }
                ?>
</tbody>
            </table>






	  </div>








<style>
  #scrollable-table thead {
    position: sticky; /* Make the header sticky */
    top: 0; /* Stick the header to the top of the container */
    background-color: rgba(24, 24, 24, 0.7);
    z-index: 1; /* Ensure header appears above other content */
  }
  #scrollable-table th {
    padding: 8px; /* Optional: Add padding for better appearance */
    text-align: center; /* Optional: Align text to the left */
    border-bottom: 1px solid #dddddd; /* Optional: Add border at the bottom */
  }
</style>

        </div>
        <div class="column3l">









<!-- HTML5 Video Player -->
<div id="videoPlayer" style="display: none; text-align: center; margin-top: 20px;">
    <video id="player" width="640" height="360" controls>
        <source id="videoSource" src="" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <br>
    <button onclick="closePlayer()">Close</button>
</div>

<script>
    // Function to play the selected video
    function playVideo(filename) {
        const videoPlayer = document.getElementById('videoPlayer');
        const videoSource = document.getElementById('videoSource');
        const player = document.getElementById('player');

        // Set the video source and show the player
        videoSource.src = filename;
        player.load();
        videoPlayer.style.display = 'block';
    }

    // Function to close the player
    function closePlayer() {
        const videoPlayer = document.getElementById('videoPlayer');
        const player = document.getElementById('player');

        // Stop the video and hide the player
        player.pause();
        player.currentTime = 0;
        videoPlayer.style.display = 'none';
    }
</script>








            <div class="ramme">
                <h3>Legg til på sendeplanen</h3>
                <form method="post" action="">
                    <input type='hidden' name='add' value='add2schedule'>
                    <label for="prog_tittel">Video</label>
                    <div><select id="prog_id" name="prog_id"">
                        <?php
                        if($is_admin_session == '1') {
                            // Retrieve data from the database
                            $sql_select_programbank = "SELECT * FROM fk_programbank";
                        } else {
                            // Retrieve data from the database
                            $sql_select_programbank = "SELECT * FROM fk_programbank WHERE eier like '$username_session'";
                        }
                        $result_select_programbank = $conn->query($sql_select_programbank);
                        while ($row_programbank = $result_select_programbank->fetch_assoc()) {
                            echo "<option value='" . $row_programbank['prog_id'] . "_" . $row_programbank['prog_tittel'] . "_" . $row_programbank['duration'] . "_" . $row_programbank['filnavn'] . "'>" . $row_programbank['prog_tittel'] . " (" . $row_programbank['duration'] . ")</option>";
                        }
                        echo "</select></div>";?>
                    <label for="sendetid">Sendetid</label><br>
                    <input type="datetime-local" name="sendetid" id="sendetid"
 value="<?php echo $selected_date_add;?>"
 min="<?php echo date('Y-m-d H:s', strtotime('+3 days')); ?>"
 max="<?php echo date('Y-m-d H:s', strtotime('+8 days')); ?>"
 required><br>
                    <input type="submit" name="add" value="Legg til">
                </form>
            </div>
        </div>
    </div>

    <script>

        // Function to submit the form automatically when the date changes
        function submitForm() {
            document.getElementById('dateForm').submit();
        }

    </script>

    </body>
    </html>

    <?php
    // Close connection
    $conn->close();
} else {
    echo "<meta http-equiv='refresh' content='0; url=https://system.hardang.com/HLS/'>";
}
?>
