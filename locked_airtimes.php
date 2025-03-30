<?php
// Start session
session_start();

// Check if the user is logged in as admin
$eier_session = $_SESSION['admin'];

if ($eier_session == "1") {
    // DB connection info
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

    // Check if form is submitted for field update
    if (isset($_POST["update"]) && $_POST["update"] == "Oppdater") {
        $la_id = $_POST['la_id'];
        $eier_id = $_POST['eier_id'];
        $broadcast_day = $_POST['broadcast_day'];
        $broadcast_time = $_POST['broadcast_time'];
        $spilletid = $_POST['spilletid'];

	$broadcast_day_array = array("Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag", "Søndag");

        // Update the record in the database based on the selected user ID
        $sql_update = "UPDATE fk_faste_sendetider SET
         eier_id = '$eier_id',
         broadcast_day = '$broadcast_day',
         broadcast_time = '$broadcast_time',
         spilletid = '$spilletid'
         WHERE la_id = '$la_id'";

        if ($conn->query($sql_update) === TRUE) {
//            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    // Check if form is submitted for field update
    if (isset($_POST["update"]) && $_POST["update"] == "update_cat") {
        $la_id = $_POST['la_id'];
        $category = $_POST['category'];

        // Update the record in the database based on the selected user ID
        $sql_update_cat = "UPDATE fk_faste_sendetider SET
         category = '$category'
         WHERE la_id = '$la_id'";

        if ($conn->query($sql_update_cat) === TRUE) {
//            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    // Check if form is submitted for field update
    if (isset($_POST["update"]) && $_POST["update"] == "update_filter") {
        $la_id = $_POST['la_id'];
        $filter_by = $_POST['filter_by'];
//echo $filter_by;
        // Update the record in the database based on the selected user ID
        $sql_update_filter = "UPDATE fk_faste_sendetider SET
         filter_by = '$filter_by'
         WHERE la_id = '$la_id'";

        if ($conn->query($sql_update_filter) === TRUE) {
//            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    // Check if form is submitted for user removal
    if (isset($_POST["remove"])) {
        $la_id = $_POST['la_id'];

            // Remove the user from the database
            $sql_remove = "DELETE FROM fk_faste_sendetider WHERE la_id = '$la_id'";

            if ($conn->query($sql_remove) === TRUE) {
//                echo "<p>Program removed successfully</p>";
            } else {
                echo "<p>Error removing program: " . $conn->error . "</p>";
            }
    }

    // Check if form is submitted for adding a new locked broadcast time
    if (isset($_POST["add_la"])) {
        $eier_id = $_POST['eier_id'];
        $broadcast_day = $_POST['broadcast_day'];
        $broadcast_time = $_POST['broadcast_time'];
        $spilletid = $_POST['spilletid'];
	$broadcast_time_modified = $broadcast_time . ":00.000000";

	// Insert new record into the database
	$sql_add_la = "INSERT INTO fk_faste_sendetider (eier_id, broadcast_day, broadcast_time, spilletid) VALUES ('$eier_id', '$broadcast_day', '$broadcast_time_modified', '$spilletid')";

	if ($conn->query($sql_add_la) === TRUE) {
	    // Redirect back to the original HTML page
	    header('Location: locked_airtimes.php');
	    exit;
	} else {
	    echo "Error: " . $sql_add_la . "<br>" . $conn->error;
            }
    }

    // Retrieve data from the database
    $sql_select = "SELECT * FROM fk_faste_sendetider ORDER BY broadcast_day ASC, broadcast_time ASC";
    $result_select = $conn->query($sql_select);

    // Retrieve data from the database
    $sql_select_category = "SELECT * FROM fk_category";
    $result_select_category = $conn->query($sql_select_category);

    // Retrieve data from the database
    $sql_select_eier_ny = "SELECT * FROM fk_users";
    $result_select_eier_ny = $conn->query($sql_select_eier_ny);

    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faste sendetider</title>
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
    </style>
</head>
<body>

                <?php include 'meny.php'; ?>

    <h2>Faste sendetider</h2>


<div class="container">
    <div class="column3l">

<div style="overflow-y: auto; max-height: 600px; border: 0px solid white;">
    <table id="scrollable-table">
<thead>
    <tr>
    <th>Oppdatert</th>
    <th>Eier</th>
    <th>Sendetid</th>
    <th>Spilletid</th>
    <th></th>
    <th>Kategori</th>
    <th>Filter</th>
    <th></th>
    </tr>
</thead>
<tbody>

    <?php

    if ($result_select->num_rows > 0) {
        while ($row = $result_select->fetch_assoc()) {

	$broadcast_day_array = array("Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag", "Søndag");
	$broadcast_day = $row["broadcast_day"];
	$eier_id = $row['eier_id'];

                    $sql_select_eier = "SELECT * FROM fk_users WHERE id like '$eier_id'";
                    $result_select_eier = $conn->query($sql_select_eier);
                while ($row_users = $result_select_eier->fetch_assoc()) {
                        $username = $row_users['username'];
			$full_name = $row_users['full_name'];
                }

        	// Extracting only the time part (hours and minutes)
	        $time_edited = date("H:i", strtotime($row["broadcast_time"]));

                echo "<tr><td>" . $row["added"] . "</td><td>" . $full_name . " (" . $username . " ID-" . $row["eier_id"] . ")</td><td>" . $broadcast_day_array[$broadcast_day] . " " . $time_edited . "</td><td>" . $row["spilletid"] . " min</td><td><div class='user-actions'>
		   <button onclick=\"showUpdateForm('" . addslashes($row["la_id"]) . "', '" . addslashes($row["eier_id"]) . "', '" . $broadcast_day . "', '" . addslashes($row["broadcast_time"]) . "', '" . addslashes($row["spilletid"]) . "')\">Endre</button></span>";

	$la_id = $row["la_id"];
        $category = $row["category"];
	$filter_by = $row["filter_by"];
	?>

            <td>
            <?php $form_id = "categoryForm_" . $row['la_id'];?>
            <form id="<?php echo $form_id; ?>" method="post" action="">
                <input type="hidden" name="la_id" value="<?php echo $la_id;?>">
                <input type="hidden" name="update" value="update_cat">

                <select id="category" name="category" onchange="submitForm('<?php echo $form_id; ?>')">
               <?php echo "<option value=''>Velg kategori</option>";
                    // Retrieve data from the database
                    $sql_select_category = "SELECT * FROM fk_category";
                    $result_select_category = $conn->query($sql_select_category);
                while ($row_category = $result_select_category->fetch_assoc()) {
                        $selected = ($row_category['cat_id'] == $category) ? 'selected' : '';
                        echo "<option value='" . $row_category['cat_id'] . "' $selected>" . $row_category['cat_name'] . " (" . $row_category['cat_id'] . ")</option>";
                }
               echo "</select>
            </form></td>";?>

            <td>
            <?php $form_id = "filterForm_" . $row['la_id'];?>
            <form id="<?php echo $form_id; ?>" method="post" action="">
                <input type="hidden" name="la_id" value="<?php echo $la_id;?>">
                <input type="hidden" name="update" value="update_filter">

                <select id="filter_by" name="filter_by" onchange="submitForm('<?php echo $form_id; ?>')">
               <?php echo "<option value=''>Velg filter</option>";

			$filter = array("ASC", "DESC", "RAND", "DATE", "LEAST SCHEDULED");
                        $filter_name = array("ASC (Eldste)", "DESC (Nyeste)", "RAND (Tilfeldig)", "DATE (Premieredato)", "LEAST SCHEDULED (Minst brukt)");
			foreach ($filter as $index => $x) {
                        $selected = ($x == $filter_by) ? 'selected' : '';
			  echo "<option value='" . $x . "' $selected>" . $filter_name[$index] . "</option>";
			}

               echo "</select>

            </form></td>


                   <td>
		   <form method='post' action=''>
                      <input type='hidden' name='la_id' value='" . $row["la_id"] . "'>
                       <input type='submit' name='remove' value='Slett'>
                   </form></td>
                   </div>
                   </div></tr>";
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



    <div id="updateForm">
        <div class="ramme">

            <form method="post" action="">
                <input type="hidden" name="la_id" id="la_id">
		<label for="eier_id">Eier</label>
                <input type="text" name="eier_id" id="eier_id" readonly><br>

<label for="broadcast_day">Dag</label>
<div><input type="range" name="broadcast_day" id="broadcast_day" min="0" max="6" step="1" oninput="updateDayName(this.value)">
<span class="spilletid" id="dayName"></span></div>

<label for="broadcast_time">Tid</label>
<input type="text" name="broadcast_time" id="broadcast_time"><br>

<label for="spilletid">Spilletid</label><br>
<div><input type="range" name="spilletid" id="spilletid" min="5" max="120" step="5" oninput="updateRangeValue(this.value)">
<span class="spilletid" id="spilletidValue"></span></div>

                <input type="submit" name="update" value="Oppdater">
            </form>
        </div>
    </div>



<h4>Legg til ny sendetid</h4>

            <form method="post" action="">
                <input type="hidden" name="la_id" id="la_id">
                <label for="eier_id">Eier</label><br>
                <div><select id="eier_id" name="eier_id">
<?php
                while ($row_users_ny = $result_select_eier_ny->fetch_assoc()) {
                        $username = $row_users_ny['username'];
                        $full_name = $row_users_ny['full_name'];
                        $id = $row_users_ny['id'];

                        echo "<option value='" . $id . "'>" . $full_name . " (" . $username . ")</option>";
                }
               echo "</select></div>";
?>

<label for="broadcast_day">Dag</label>
<div><input type="range" name="broadcast_day" id="broadcast_day" min="0" max="6" step="1" oninput="updateDayNameNew(this.value)">
<span class="spilletid" id="dayNameNew">Torsdag</span></div>

<label for="broadcast_time">Tid</label>
<div><input type="time" name="broadcast_time" id="broadcast_time" value="17:00"></div><br>

<label for="spilletid">Spilletid</label><br>
<div><input type="range" name="spilletid" id="spilletid" min="5" max="120" step="5" oninput="updateRangeValueNew(this.value)">
<span class="spilletid" id="spilletidValueNew">65</span></div>

                <input type="submit" name="add_la" value="Legg til">
            </form>



  </div>
</div>




    <script>

    function submitForm(formId) {
        document.getElementById(formId).submit();
    }

function updateRangeValue(value) {
    document.getElementById("spilletidValue").textContent = value;
}

function updateDayName(value) {
    const dayNames = ['Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'];
    document.getElementById('dayName').textContent = dayNames[value];
}

function showUpdateForm(la_id, eier_id, broadcast_day, broadcast_time, spilletid) {
    document.getElementById('updateForm').style.display = 'block';
    document.getElementById('la_id').value = la_id;
    document.getElementById('eier_id').value = eier_id;
    document.getElementById('broadcast_day').value = broadcast_day;
    document.getElementById('broadcast_time').value = broadcast_time;
    document.getElementById('spilletid').value = spilletid;

    // Map numeric value to day name
    const dayNames = ['Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'];
    document.getElementById('dayName').textContent = dayNames[broadcast_day];

    // Update the displayed value of spilletid
    document.getElementById('spilletidValue').textContent = spilletid;
}

    function updateRangeValueNew(value) {
        document.getElementById("spilletidValueNew").textContent = value;
    }

function updateDayNameNew(value) {
    const dayNamesNew = ['Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'];
    document.getElementById('dayNameNew').textContent = dayNamesNew[value];
}

    </script>

</body>
</html>

    <?php
    // Close connection
    $conn->close();
} else {
    echo "<meta http-equiv='refresh' content='0; url=https://frikanalen.hardang.com/index.php'>";
}
?>
