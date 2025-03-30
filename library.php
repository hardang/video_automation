<?php
// Start session
session_start();

// DB connection info
    $servername = getenv('DB_SERVER');
    $username = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');
    $dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure search term is sanitized
$searchTerm = htmlspecialchars(filter_input(INPUT_GET, 'searchTerm', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8');

// Check if the URL parameters 'filnavn' and 'prog_id' are set
if (isset($_GET['filnavn']) && isset($_GET['prog_id'])) {
    $filnavn = urldecode($_GET['filnavn']); // Decode the file name
    $prog_id = intval($_GET['prog_id']);    // Sanitize prog_id as an integer

    // Adjust the file path to replace the original folder and file extension with the desired ones
    $video_url = preg_replace(
        ["/^frikanalen\//", "/original|broadcast\//", "/\.\w+$/"],
        ["webfiles/", "mp4web/", ".mp4"],
        $filnavn
    );

    // Retrieve program details for additional information
    //$sql = "SELECT prog_tittel, eier, duration FROM fk_programbank WHERE prog_id = $prog_id";
    $sql = "SELECT fk_programbank.prog_tittel, fk_programbank.eier, fk_programbank.duration, fk_users.full_name FROM fk_programbank LEFT JOIN fk_users ON fk_programbank.eier = fk_users.username WHERE fk_programbank.prog_id = $prog_id";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $program = $result->fetch_assoc();
        $prog_tittel = htmlspecialchars($program['prog_tittel']); // Program title
        $eier = htmlspecialchars($program['eier']); // Producer (eier)
        $full_name = htmlspecialchars($program['full_name']); // Producer (eier)
	// Extract HH:MM:SS from the duration
	$duration = substr($program['duration'], 0, 8); // Take only the first 8 characters
    } else {
        $prog_tittel = "Unknown Program"; // Default title if not found
        $full_name = "";
    }

    // Query to get other videos from the same producer (eier)
    $sql_other_videos = "SELECT filnavn, prog_tittel, prog_id, duration, eier FROM fk_programbank WHERE eier = '$eier' AND prog_id != $prog_id ORDER BY added DESC";
    $other_videos_result = $conn->query($sql_other_videos);






}




elseif(isset($_GET['selected_producer'])) {
        $eier = htmlspecialchars($_GET['selected_producer']); // Sanitize the input

    // Query to get other videos from the same producer (eier)
//    $sql_other_videos = "SELECT filnavn, prog_tittel, prog_id, duration, eier FROM fk_programbank WHERE eier = '$eier' ORDER BY added DESC";
    $sql_other_videos = "SELECT fk_programbank.filnavn, fk_programbank.prog_tittel, fk_programbank.prog_id, fk_programbank.eier, fk_programbank.duration, fk_users.full_name 
	FROM fk_programbank, fk_users 
	WHERE eier = '$eier' 
	ORDER BY added DESC";
    $other_videos_result = $conn->query($sql_other_videos);


    // No video selected, so fetch the most recent video
//    $sql_default_video = "SELECT filnavn, prog_tittel, prog_id, eier, duration FROM fk_programbank WHERE eier = '$eier' ORDER BY added DESC LIMIT 1";
    $sql_default_video = "SELECT fk_programbank.filnavn, fk_programbank.prog_tittel, fk_programbank.prog_id, fk_programbank.eier, fk_programbank.duration, fk_users.full_name 
	FROM fk_programbank LEFT JOIN fk_users ON fk_programbank.eier = fk_users.username 
	WHERE eier = '$eier' ORDER BY added DESC LIMIT 1";
    $result_default_video = $conn->query($sql_default_video);

    if ($result_default_video && $result_default_video->num_rows > 0) {
        $default_video = $result_default_video->fetch_assoc();
        $filnavn = $default_video['filnavn'];
        $prog_id = $default_video['prog_id'];
        // Extract HH:MM:SS from the duration
        $duration = substr($default_video['duration'], 0, 8); // Take only the first 8 characters

        $prog_tittel = htmlspecialchars($default_video['prog_tittel']);
        $eier = htmlspecialchars($default_video['eier']);
        $full_name = htmlspecialchars($default_video['full_name']); // Producer (eier)

        // Adjust the file path to replace the original folder and file extension with the desired ones
        $video_url = preg_replace(
            ["/^frikanalen\//", "/original|broadcast\//", "/\.\w+$/"],
            ["webfiles/", "mp4web/", ".mp4"],
            $filnavn
        );

        // Query to get other videos from the same producer (eier)
        $sql_other_videos = "SELECT filnavn, prog_tittel, prog_id, duration, eier FROM fk_programbank WHERE eier = '$eier' AND prog_id != $prog_id ORDER BY RAND()";
        $other_videos_result = $conn->query($sql_other_videos);
    } else {
//        echo "No videos available.";
//        exit;
    }}




















elseif(isset($_GET['searchTerm'])) {

$searchTermWildcard = '%' . $searchTerm . '%';
    // No video selected, so fetch the most recent video
    $sql_default_video = "SELECT fk_programbank.filnavn, 
	fk_programbank.prog_tittel, 
	fk_programbank.prog_id, 
	fk_programbank.eier, 
	fk_programbank.duration, fk_users.full_name 
	FROM 
	fk_programbank LEFT JOIN fk_users ON fk_programbank.eier = fk_users.username 
	WHERE 
	fk_programbank.prog_tittel LIKE '$searchTermWildcard' 
        OR fk_programbank.eier LIKE '$searchTermWildcard' 
        OR fk_programbank.filnavn LIKE '$searchTermWildcard' 
	ORDER BY added DESC LIMIT 1";
    $result_default_video = $conn->query($sql_default_video);

    if ($result_default_video && $result_default_video->num_rows > 0) {
        $default_video = $result_default_video->fetch_assoc();
        $filnavn = $default_video['filnavn'];
        $prog_id = $default_video['prog_id'];
        // Extract HH:MM:SS from the duration
        $duration = substr($default_video['duration'], 0, 8); // Take only the first 8 characters

        $prog_tittel = htmlspecialchars($default_video['prog_tittel']);
        $eier = htmlspecialchars($default_video['eier']);
        $full_name = htmlspecialchars($default_video['full_name']); // Producer (eier)

        // Adjust the file path to replace the original folder and file extension with the desired ones
        $video_url = preg_replace(
            ["/^frikanalen\//", "/original|broadcast\//", "/\.\w+$/"],
            ["webfiles/", "mp4web/", ".mp4"],
            $filnavn
        );
        // Query to get other videos from the same producer (eier)
        $sql_other_videos = "SELECT filnavn, prog_tittel, prog_id, duration, eier 
	FROM 
	fk_programbank 
	WHERE 
	prog_tittel LIKE '$searchTermWildcard' 
	OR eier LIKE '$searchTermWildcard' 
	OR filnavn LIKE '$searchTermWildcard' 
	ORDER BY prog_tittel ASC";
        $other_videos_result = $conn->query($sql_other_videos);
    } else {
//        echo "No videos available.";
        //exit;
    }
}














else {
    // No video selected, so fetch the most recent video

    $sql_default_video = "SELECT fk_programbank.filnavn, fk_programbank.prog_tittel, 
	fk_programbank.prog_id, fk_programbank.eier, 
	fk_programbank.duration, fk_users.full_name 
	FROM 
	fk_programbank, fk_users 
	ORDER BY RAND() LIMIT 1";
    $result_default_video = $conn->query($sql_default_video);

    if ($result_default_video && $result_default_video->num_rows > 0) {
        $default_video = $result_default_video->fetch_assoc();
        $filnavn = $default_video['filnavn'];
        $prog_id = $default_video['prog_id'];
        // Extract HH:MM:SS from the duration
        $duration = substr($default_video['duration'], 0, 8); // Take only the first 8 characters

        $prog_tittel = htmlspecialchars($default_video['prog_tittel']);
        $eier = htmlspecialchars($default_video['eier']);
//        $full_name = htmlspecialchars($default_video['full_name']); // Producer (eier)
	$full_name = "the archive"; // Producer (eier)
        // Adjust the file path to replace the original folder and file extension with the desired ones
        $video_url = preg_replace(
            ["/^frikanalen\//", "/original|broadcast\//", "/\.\w+$/"],
            ["webfiles/", "mp4web/", ".mp4"],
            $filnavn
        );

        // Query to get other videos from the same producer (eier)
        $sql_other_videos = "SELECT filnavn, prog_tittel, prog_id, duration, eier FROM fk_programbank ORDER BY prog_tittel ASC";
        $other_videos_result = $conn->query($sql_other_videos);
    } else {
//        echo "No videos available.";
//        exit;
    }
}


// Check if there are results and print the number of rows
$db_total_count = $other_videos_result->num_rows > 0 ? $other_videos_result->num_rows : 0;


if (isset($_GET['searchTerm'])) {
	$eier = "";
}
else{
	$eier = $eier;
}


// Prepare the SQL statement with placeholders
$sql_other_eiere = "SELECT id, username, full_name FROM fk_users WHERE username != ? ORDER BY full_name ASC";

// Prepare the statement
$stmt_eier = $conn->prepare($sql_other_eiere);

// Bind the parameter (safeguarding against SQL injection)
$stmt_eier->bind_param("s", $eier);

// Execute the statement
$stmt_eier->execute();

// Get the result
$other_eiere_result = $stmt_eier->get_result();













//function countFilesInDirectory($directory) {
//    $fileCount = 0;
//    // Open the directory
//    $files = new RecursiveIteratorIterator(
//        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
//    );
//    // Count files
//    foreach ($files as $file) {
//        if ($file->isFile()) { // Only count regular files
//            $fileCount++;
//        }
//    }
//    return $fileCount;
//}
//// Set the base directory
//$baseDirectory = 'webfiles/';
//// Check if the directory exists
//if (is_dir($baseDirectory)) {
//    $totalFiles = countFilesInDirectory($baseDirectory);
////$totalFiles = "converting...";
//    //echo "Total files in '$baseDirectory': $totalFiles";
//} else {
//    echo "Directory '$baseDirectory' does not exist.";
//}








?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FK - Play</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=1.0">
    <link href="https://vjs.zencdn.net/7.2.3/video-js.css" rel="stylesheet">
    <script src="https://vjs.zencdn.net/ie8/ie8-version/videojs-ie8.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-contrib-hls/5.14.1/videojs-contrib-hls.js"></script>
    <script src="https://vjs.zencdn.net/7.2.3/video.js"></script>




    <title>FK - Play</title>
    <script>
        // Function to update the video player when a new video is selected
        function updatePlayer(filnavn, prog_id, prog_tittel, duration, eier) {
            var videoPlayer = document.getElementById('videoPlayer');
            var videoSource = document.getElementById('videoSource');
            var newVideoUrl = filnavn.replace('frikanalen', 'webfiles').replace('broadcast', 'mp4web').replace(/\.\w+$/, '.mp4');

            // Update the video source and title
            videoSource.src = newVideoUrl;
            videoPlayer.load(); // Reload the video player to play the new video
            document.getElementById('progTitle').innerText = prog_tittel;
            document.getElementById('progDuration').innerText = duration;
            document.getElementById('progEier').innerText = eier;

            // Scroll to the video player (jump to the top)
            videoPlayer.scrollIntoView({ behavior: "smooth" });
        }
    </script>
</head>

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

<body>

<?php include 'meny.php'; ?>

<!--<H2><?php echo $totalFiles;?> / 3901 files converted</H2>-->

<!-- Search start -->
<div class="search_box">
    <form action="library.php" method="GET">
        <input type="text" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>" placeholder="Søk i arkivet" required>
        <br /><button type="submit">Søk</button>
    </form>

<div class="resett_button">
    <form action="library.php" method="POST">
        <button type="submit">Resett</button>
    </form>
</div>
</div>
<!-- Search stop -->

<!-- Andre brukere Start -->
<div class="other_users_list">
<?php
if ($other_eiere_result->num_rows > 0) {
    echo '<form method="get" action="">';
    echo '<select name="selected_producer" required>';

if (isset($_GET['searchTerm'])) {
        echo '<option value="">Velg en produsent</option>';
}
else{
    echo '<option value="">Velg annen produsent</option>'; // Default empty option
}

    while ($row = $other_eiere_result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['full_name']) . '</option>';
    }

    echo '</select><br />';
    echo '<button type="submit">Se mer</button>';
    echo '</form>';
} else {
    echo "<p>No other producers found.</p>";
}

// Check if the form is submitted
if (isset($_GET['selected_producer'])) {
    $selectedProducerFull_Name = htmlspecialchars($_GET['selected_producer']); // Sanitize the input
}
// Check if the form is submitted
if (isset($_GET['searchTerm'])) {
    $full_name = "The Search :-)"; // Sanitize the input
}
?>
</div>
<!-- Andre brukere stop -->

<?php
if($db_total_count == 0)
{
echo "<H4>Ingen videor fra denne produsenten er publisert enda.</H4>";
}
else
{?>
<div class="frame_video">
    <!-- Video Player -->
    <video id="videoPlayer" controls width="640" height="360" poster="/images/fk-play.jpg">
        <source id="videoSource" src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>
<div class="frame_info">
    <div class="text_info" id="progTitle"><?php echo $prog_tittel; ?></div>
    <div class="spilletid_ramme" id="progDuration"><?php echo $duration; ?></div>

    <div class="count_from_database"><?php echo $db_total_count;?> treff</div>
</div>




    <!-- List of other videos from the same producer -->
    <h3>Other videos from "<?php echo $full_name; ?>"</h3>

<div style="overflow-y: auto; max-height: 600px; border: 0px solid white;">
    <table id="scrollable-table">
	<thead>
	      <tr>
	        <th></th>
	        <th>Tittel</th>
        	<th>Spilletid</th>
	      </tr>
	</thead>
	<tbody>

        <?php
        if ($other_videos_result && $other_videos_result->num_rows > 0) {
            while ($other_video = $other_videos_result->fetch_assoc()) {

                $other_video_url = preg_replace(
                    [
                        "/^frikanalen\//",  // Match "frikanalen/" at the start of the string
                        "/original\//",     // Match "original/" at the start of the string (if it exists)
                        "/\.\w+$/"          // Match file extension at the end of the string
                    ],
                    [
                        "webfiles/",          // Replace "frikanalen/" with "mp4web/"
                        "mp4web/",        // Replace "original/" with "web_base/"
                        ".mp4"              // Replace any file extension with ".mp4"
                    ],
                    $other_video['filnavn']
                );


		echo '<tr><td><button onclick="updatePlayer(\'' . addslashes($other_video_url) . '\', ' . intval($other_video['prog_id']) . ', \'' . addslashes($other_video['prog_tittel']) . '\', \'' . addslashes(substr($other_video['duration'], 0, 8)) . '\')">Play</button></td><td>' . htmlspecialchars($other_video['prog_tittel']) . '</td><td>' . htmlspecialchars(substr($other_video['duration'], 0, 8)) . '</td></tr>';

            }
        } else {
            echo "<li>No other videos from this producer.</li>";
        }
        ?>

	</tbody>
    </table>
</div>
<?php
}
?>

</body>
</html>

