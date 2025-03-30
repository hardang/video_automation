<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HardangTV - sendeplan</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your style.css file -->
</head>

<?php
// DB connection info
$servername = "";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current date and time
date_default_timezone_set('Europe/Oslo');
$currentDateTime = date('Y-m-d H:i:s');

// Next 10 items
echo "<H4>Senere på kanalen</H4>";

// Query to retrieve data from the fk_sendepan table
$sql = "SELECT fk_sendeplan.*, fk_programbank.prog_tittel
 FROM fk_sendeplan
 JOIN fk_programbank ON fk_sendeplan.prog_id = fk_programbank.prog_id
 WHERE fk_sendeplan.sendetid > '$currentDateTime'
 ORDER BY fk_sendeplan.sendetid ASC
 LIMIT 10";

$result = $conn->query($sql);





echo "<p><table>";
// Check if any rows are returned
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $reduced_datetime_str = substr($row["sendetid"], 11, 5);
        $reduced_duration_str = substr($row["duration"], 0, 8);
        $filnavn = $row["filnavn"];
        $prog_id = $row["prog_id"]; // Retrieve the prog_id from the query result
        
        // Create the "Se Nå" link with both filnavn and prog_id
        $link = "library.php?filnavn=" . urlencode($filnavn) . "&prog_id=" . urlencode($prog_id);
        
        // Render the row
        echo "<tr>
                <td>" . $reduced_datetime_str . "</td>
                <td>" . htmlspecialchars($row["prog_tittel"]) . "</td>
                <td><a href='" . $link . "'>Se nå</a></td>
              </tr>";
    }
} else {
    echo "No results found";
}
echo "</table></p>";




// Close connection
$conn->close();
?>


