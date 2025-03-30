<?php
session_start();

// Ensure session variables are set before using them
$is_admin_session = $_SESSION['admin'] ?? false;
$username_session = $_SESSION['username'] ?? null;
$id_session = $_SESSION['user_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FK - Bergen</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your style.css file -->
    <link href="https://vjs.zencdn.net/7.2.3/video-js.css" rel="stylesheet">
    <script src="https://vjs.zencdn.net/ie8/ie8-version/videojs-ie8.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-contrib-hls/5.14.1/videojs-contrib-hls.js"></script>
    <script src="https://vjs.zencdn.net/7.2.3/video.js"></script>
</head>
<body>

<?php include 'meny.php'; ?>

<H1>FK - Bergen</H1>

<script>
    // Function to reload the PHP include
    function reloadInclude(url, containerId) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById(containerId).innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", url, true);
        xhttp.send();
    }

    // Load the include files immediately when the page loads
    window.onload = function() {
        reloadInclude("view_items_sendeplan_current.php", "include-container-current");
        reloadInclude("view_items_sendeplan.php", "include-container");
    };

    // Reload the include files every 10 seconds
    setInterval(function() {
        reloadInclude("view_items_sendeplan_current.php", "include-container-current");
        reloadInclude("view_items_sendeplan.php", "include-container");
    }, 10000); // 10,000 milliseconds = 10 seconds
</script>


<div class="container">
    <div class="column1">

        <?php
//        $filename = 'HLS/fk_live.m3u8';

//        if (file_exists($filename)) {
//            $filetime = filemtime($filename) + 30;
//echo "YES";
//        } else {
//            $filetime = 0; // Default timestamp if file doesn't exist
//echo "NO";
//        }

//        $now = time(); // Current timestamp


//echo "<br />" . $now . "<br />";
//echo "<br />" . $filetime . "<br />";

        // Compare current time with the file modification time
//        if ($now > $filetime) {
//            echo "<H4>Video serveren (CaspsrCG) er avslått,<br>og sender derfor heller ikke noe til nettsiden.</H4>";

// ----- FUNCTION TO START LINDY IP POWER SWITCH ----- //



// Function to get the current state of the outputs
function getCurrentState() {
    // Fetch the current state from the switch using SNMP
    $output = snmpget('192.168.112.34', 'public', '1.3.6.1.4.1.17420.1.2.9.1.13.0');
    // Return the result
    return $output;
}

// Function to set the state of a single output
function setOutputState($outputNumber, $state) {
    // Build the OID for the specific output
    $oid = "1.3.6.1.4.1.17420.1.2.9.1.13.0";
    // Get the current state of all 8 outputs
    $currentState = getCurrentState();

    // Strip the "STRING: " part from the result and split the state string by commas
    $stateString = trim(str_replace("STRING: ", "", $currentState));
    $states = explode(',', $stateString);

    // Modify the state of the selected output
    $states[$outputNumber - 1] = $state;

    // Modify the state of the TrueNAS out to follow the FKServer output
    $states[$outputNumber - 3] = $state;

    // Rebuild the state string
    $newStateString = implode(',', $states);

    // Corrected: Ensure no extra quotes in the SNMP command.
    $snmpCommand = "snmpset -v1 -c admin 192.168.112.34 1.3.6.1.4.1.17420.1.2.9.1.13.0 s $newStateString";
    //echo "<p>SNMP Command: $snmpCommand</p>"; // Print the SNMP command for validation

    // Execute the SNMP command using the correct format
    $result = shell_exec($snmpCommand); // Directly use the command in the shell
    //echo "<p>SNMP Result: $result</p>";  // Print out the result of the SNMP set command
}

// Check if the form was submitted to modify a specific output
//if ($_SERVER["REQUEST_METHOD"] == "POST" AND POST) {
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ippowerswitch'])) {
    // Get the selected output and new state
    $outputNumber = $_POST['outputNumber'];
    $newState = $_POST['state'];

    // Set the new state for the selected output
    setOutputState($outputNumber, $newState);
}

// Get the current state of all outputs
$currentState = getCurrentState();

// Strip the "STRING: " part from the result and split the state string by commas
$stateString = trim(str_replace("STRING: ", "", $currentState));
$stateString = trim(str_replace('"', '', $currentState));
$states = explode(',', $stateString);

$output = "7";

$output_val = $output - 1;

if($states[$output_val] == "0")
{
?>

    <form method="POST" name="ippowerswitch" action="">
        <?php
            // Display each output state with a checkbox (ON/OFF)
                $checked = ($states[$output] == "1") ? "checked" : "";
        ?>

        <input type="hidden" name="outputNumber" id="outputNumber" value="<?php echo $output;?>">

        <input type="hidden" name="state" id="state" value="1">

        <input type="submit" value="Turn the server ON">
    </form>

<?php
 }
else{}



// ----- FUNCTION TO START LINDY IP POWER SWITCH ----- //




























//        } else {
            // Display the video player
        ?>
        <br />
        <!-- HTML -->
        <video id='hls-example' class="video-js vjs-default-skin" width="640px" height="360px" controls>
<!--            <source type="application/x-mpegURL" src="HLS/fk_live.m3u8">   -->
            <source type="application/x-mpegURL" src="https://avideo.hardang.com:8443/live/677963f6e2b28-1/index.m3u8">
        </video>

        <script>
            window.onload = function() {
                var player = videojs('hls-example');
                player.play();
            };
        </script>
        <?php
//        }
        ?>

	<br />

        <div id="include-container-current">
            <!-- Content will be loaded here -->
        </div>

        <div id="include-container">
            <!-- Content will be loaded here -->
        </div>

    </div>

    <div class="column2">
        <?php include 'login.php'; ?>
    </div>
</div>

</body>
</html>
