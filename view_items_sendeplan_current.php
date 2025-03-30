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
echo "<H4>Nå på kanalen</H4>";

// Query to retrieve data from the fk_sendepan table
//$sql1 = "SELECT * FROM fk_sendeplan WHERE sendetid < '$currentDateTime' ORDER BY sendetid DESC LIMIT 1";
$sql1 = "SELECT fk_sendeplan.*, fk_programbank.prog_tittel
 FROM fk_sendeplan
 JOIN fk_programbank ON fk_sendeplan.prog_id = fk_programbank.prog_id
 WHERE fk_sendeplan.sendetid < '$currentDateTime'
 ORDER BY fk_sendeplan.sendetid DESC
 LIMIT 1";


$result1 = $conn->query($sql1);

// Check if any rows are returned
if ($result1->num_rows > 0) {
    // Output data of each row
    while($row1 = $result1->fetch_assoc()) {
        echo "<H3>" . $row1["prog_tittel"]. "</H3>";
    }
} else {
    echo "No results found";
}

// Close connection
$conn->close();
?>


