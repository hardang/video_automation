<?php
// Start session
session_start();

// Check if the user is logged in as admin
$eier_session = $_SESSION['admin'] ?? null;
$is_admin_session = $_SESSION['admin'] ?? null;
$username_session = $_SESSION['username'] ?? null;
$id_session = $_SESSION['user_id'] ?? null;

if ($eier_session !== null && $eier_session >= "0") {
    // DB connection info
    $servername = getenv('DB_SERVER');
    $username = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');
    $dbname = getenv('DB_NAME');


    // Create connection using mysqli with improved error handling
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_errno) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use the connection here...

} else {
    // Handle cases where the session is invalid or user is not admin
    die("Access denied: Admin privileges required.");
}




// Check if form is submitted for field update
if (isset($_POST["update"]) && $_POST["update"] === "Oppdater") {
    // Sanitize input but retain special characters
    $prog_id = trim($_POST['prog_id']); // Assuming IDs are numeric or alphanumeric
    $prog_tittel = trim($_POST['prog_tittel']); // Allow æøå and other special characters
    $duration = trim($_POST['duration']);
    $live = isset($_POST['live']) && $_POST['live'] === "1" ? "1" : "0"; // Set live to 1 or 0
    $ori_filnavn = trim($_POST['ori_filnavn']);
    $info = trim($_POST['info']); // Allow æøå and other special characters
    $first_day = !empty($_POST['first_day']) ? $_POST['first_day'] : "0000-00-00"; // Default date

    // Update the record in the database
    $sql_update = "UPDATE fk_programbank SET prog_tittel = ?, live = ?, info = ?, first_day = ? WHERE prog_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('sssss', $prog_tittel, $live, $info, $first_day, $prog_id);

    if ($stmt->execute()) {
        // Record updated successfully
    } else {
        // Handle error
    }
    $stmt->close();
}













if (isset($_POST["update"]) && $_POST["update"] === "update_cat") {
    $category = $_POST['category'] ?? null;
    $prog_id = $_POST['prog_id'] ?? null;

    // Check if both are valid integers
    if (is_numeric($category) && is_numeric($prog_id)) {
        $sql_update_cat = "UPDATE fk_programbank SET cat_id = ? WHERE prog_id = ?";
        $stmt = $conn->prepare($sql_update_cat);
        $stmt->bind_param('ii', $category, $prog_id);

        if ($stmt->execute()) {
            // Record updated successfully
        } else {
            echo "Error updating category: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid category or program ID.";
    }
}





if (isset($_POST["update"]) && $_POST["update"] === "update_eier") {
    $eier_username = $_POST['eier_username'] ?? null;
    $prog_id = $_POST['prog_id'] ?? null;

    // Check if both are valid integers
    if (is_numeric($prog_id)) {
        $sql_update_eier = "UPDATE fk_programbank SET eier = ? WHERE prog_id = ?";
        $stmt = $conn->prepare($sql_update_eier);
        $stmt->bind_param('si', $eier_username, $prog_id);

        if ($stmt->execute()) {
            // Record updated successfully
        } else {
            echo "Error updating eier: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid eier or program ID.";
    }
}




// Update the collection based on the selected user ID
if (isset($_POST["update"]) && $_POST["update"] === "update_col") {
    $collection = $_POST['collection'] ?? null;
    $prog_id = $_POST['prog_id'] ?? null;

    $sql_update_col = "UPDATE fk_programbank SET col_id = ? WHERE prog_id = ?";
    $stmt = $conn->prepare($sql_update_col);
    $stmt->bind_param('ss', $collection, $prog_id);

    if ($stmt->execute()) {
        // Record updated successfully
    } else {
        echo "Error updating collection: " . $stmt->error;
    }
    $stmt->close();
}

// Remove a program from the database
if (isset($_POST["remove"])) {
    $sql_remove = "DELETE FROM fk_programbank WHERE prog_id = ?";
    $stmt = $conn->prepare($sql_remove);
    $stmt->bind_param('s', $prog_id);

    if ($stmt->execute()) {
        // Program removed successfully
    } else {
        echo "Error removing program: " . $stmt->error;
    }
    $stmt->close();
}

































// Remove a program from the database
if (isset($_POST["remove"])) {
    // Fetch the prog_id from the posted data
    $prog_id = $_POST['prog_id'];

    // Ensure a valid database connection is established
    if ($conn) {
        $sql_remove = "DELETE FROM fk_programbank WHERE prog_id = ?"; // Ensure prog_id is the correct column

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql_remove);
        if ($stmt) {
            // Bind the parameter (use 'i' for integer if prog_id is an integer)
            $stmt->bind_param('i', $prog_id); // Adjust the 'i' if prog_id is not a string

            // Execute the statement
            if ($stmt->execute()) {
                // Program removed successfully
//                echo "Program removed successfully.";
            } else {
//                echo "Error removing program: " . $stmt->error; // Show any error that occurs during execution
            }

            // Close the statement
            $stmt->close();
        } else {
//            echo "Failed to prepare statement: " . $conn->error; // Debugging statement
        }
    } else {
//        echo "Database connection not established."; // Ensure connection is valid
    }
}











// Insert new program record into the database
if (isset($_POST["dummy_name"])) {
    $duration = $hours . ":" . $minutes . ":" . $seconds . ".000000";
    $filnavn = empty($filnavn) ? "EMPTY" : $filnavn;

    $sql_dummy_name = "INSERT INTO fk_programbank (prog_tittel, filnavn, duration, live, ori_filnavn, eier) VALUES (?, ?, ?, '0', ?, ?)";
    $stmt = $conn->prepare($sql_dummy_name);
    $stmt->bind_param('sssss', $dummy_name, $filnavn, $duration, $filnavn, $username_session);

    if ($stmt->execute()) {
        header('Location: video_archive.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Insert new category into the database
if (isset($_POST["add_cat"])) {

echo "HER";


echo $id_session;


    $cat_name = trim($_POST['cat_name']); // Allow æøå and other special characters
    $user_id_trim = trim($_POST['user_id']); // Allow æøå and other special characters

    $sql_cat_name = "INSERT INTO fk_category (cat_name, eier) VALUES (?, ?)";
    $stmt = $conn->prepare($sql_cat_name);
    $stmt->bind_param('si', $cat_name, $user_id_trim);

    if ($stmt->execute()) {
        header('Location: video_archive.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Insert new collection into the database
if (isset($_POST["add_col"])) {

    $col_name = trim($_POST['col_name']); // Allow æøå and other special characters
    $user_id_trim = trim($_POST['user_id']); // Allow æøå and other special characters

    $sql_col_name = "INSERT INTO fk_collection (col_name, eier) VALUES (?, ?)";
    $stmt = $conn->prepare($sql_col_name);
    $stmt->bind_param('si', $col_name, $user_id_trim);

    if ($stmt->execute()) {
        header('Location: video_archive.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
































// Ensure search term is sanitized
$searchTerm = htmlspecialchars(filter_input(INPUT_POST, 'searchTerm', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8');

// Pagination handling
$page_nr = isset($_POST["page_nr"]) ? (int)$_POST["page_nr"] : 1;
$list_start_var = $page_nr - 1;
$list_start = 0 + (10 * $list_start_var);
$list_limit = 10; // Limit to 50 records

$searchTermWildcard = '%' . $searchTerm . '%';

















if ($eier_session === 0) {
    // Query for regular users
    if (empty($searchTerm)) {

        // No search term provided
        $sql_select_total = "SELECT * FROM fk_programbank WHERE eier LIKE '$username_session'";
        $sql_select = "SELECT * FROM fk_programbank WHERE eier LIKE ? ORDER BY prog_id DESC LIMIT ?, ?";

        $stmt_total = $conn->prepare($sql_select_total);
        if (!$stmt_total) {
            die("Error preparing query: " . $conn->error);
        }

//        $stmt_total->bind_param('s', $username_session);

        $stmt = $conn->prepare($sql_select);
        if (!$stmt) {
            die("Error preparing select query: " . $conn->error);
        }
        $stmt->bind_param('sii', $username_session, $list_start, $list_limit);

    } else {

        // Search term provided
        $searchTermWildcard = '%' . $searchTerm . '%';
        $sql_select_total = "SELECT * FROM fk_programbank WHERE 
                             (prog_tittel LIKE '$searchTermWildcard' OR info LIKE '$searchTermWildcard' OR duration LIKE '$searchTermWildcard' OR first_day LIKE '$searchTermWildcard') 
                             AND eier LIKE '$username_session'";
        $sql_select = "SELECT * FROM fk_programbank WHERE 
                       (prog_tittel LIKE ? OR info LIKE ? OR duration LIKE ? OR first_day LIKE ?) 
                       AND eier LIKE ? 
                       ORDER BY prog_tittel ASC LIMIT ?, ?";

        $stmt_total = $conn->prepare($sql_select_total);
        if (!$stmt_total) {
            die("Error preparing total query with search: " . $conn->error);
        }
//        $stmt_total->bind_param('sssss', $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $username_session);

        $stmt = $conn->prepare($sql_select);
        if (!$stmt) {
            die("Error preparing select query with search: " . $conn->error);
        }
        $stmt->bind_param('sssssii', $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $username_session, $list_start, $list_limit);
    }
} elseif ($eier_session === 1) {
    // Query for admins
    if (empty($searchTerm)) {

        // No search term provided
        $sql_select_total = "SELECT * FROM fk_programbank";
        $sql_select = "SELECT * FROM fk_programbank ORDER BY prog_id DESC LIMIT ?, ?";

        $stmt_total = $conn->prepare($sql_select_total);
        if (!$stmt_total) {
            die("Error preparing total admin query: " . $conn->error);
        }

        $stmt = $conn->prepare($sql_select);
        if (!$stmt) {
            die("Error preparing select admin query: " . $conn->error);
        }
        $stmt->bind_param('ii', $list_start, $list_limit);

    } else {

        // Search term provided
        $searchTermWildcard = '%' . $searchTerm . '%';
        $sql_select_total = "SELECT * FROM fk_programbank WHERE prog_tittel LIKE '$searchTermWildcard' OR info LIKE '$searchTermWildcard' OR duration LIKE '$searchTermWildcard' OR first_day LIKE '$searchTermWildcard'";
        $sql_select = "SELECT * FROM fk_programbank WHERE prog_tittel LIKE ? OR info LIKE ? OR duration LIKE ? OR first_day LIKE ? ORDER BY prog_tittel ASC LIMIT ?, ?";

        $stmt_total = $conn->prepare($sql_select_total);
        if (!$stmt_total) {
            die("Error preparing admin query with search: " . $conn->error);
        }
//        $stmt_total->bind_param('ssss', $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard);

        $stmt = $conn->prepare($sql_select);
        if (!$stmt) {
            die("Error preparing select admin query with search: " . $conn->error);
        }
        $stmt->bind_param('ssssii', $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $list_start, $list_limit);
    }
}

else{}




















// Execute the queries
$stmt_total->execute();
$result_total = $stmt_total->get_result();

$stmt->execute();
$result_select = $stmt->get_result();

// Retrieve category data based on user type
$sql_select_category = $eier_session === "0" ? 
                       "SELECT * FROM fk_category WHERE eier LIKE ?" : 
                       "SELECT * FROM fk_category";
$stmt_category = $conn->prepare($sql_select_category);
if ($eier_session === "0") {
    $stmt_category->bind_param('s', $username_session);
}
$stmt_category->execute();
$result_select_category = $stmt_category->get_result();

// Close statements after execution
$stmt_total->close();
$stmt->close();
$stmt_category->close();















    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videoarkiv</title>
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
          width: 300px;
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

    <h2>Dine videoklipp</h2>

<div class="container">
    <div class="column3l">

    <form action="video_archive.php" method="POST">
        <input type="text" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>" placeholder="Søk i arkivet" required>
        <button type="submit">Søk</button>
    </form>

    <form action="video_archive.php" method="POST">
        <button type="submit">Resett</button>
    </form>




<!-- pagintaion -->





<?php

// Construct the SQL query
$sql_count = $sql_select_total;  // Already defined SQL query based on search and user access
//$sql_count = $stmt_total;

// Execute the query using prepared statements
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute();
$result_count = $stmt_count->get_result();

// Check if there are results and print the number of rows
$db_total_count = $result_count->num_rows > 0 ? $result_count->num_rows : 0;

$i = 0;
while ($i < $db_total_count) {
  $i += 10;
}
$last_button = $i / 10;

$page_nr_neste = $page_nr + 1;
$page_nr_forrige = $page_nr - 1;
?>

<div style="display: flex; align-items: center;">

<?php if($page_nr != 1){ ?>
    <form method="post" action="" style="margin-right: 10px;">
        <input type="hidden" name="searchTerm" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="page_nr" value="1">
        <input type="submit" value="1">
    </form>
<?php } ?>

<?php if($page_nr > 2){ ?>
    <form method="post" action="" style="margin-right: 10px;">
        <input type="hidden" name="searchTerm" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="page_nr" value="<?= $page_nr_forrige; ?>">
        <input type="submit" value="<--">
    </form>
<?php } ?>

<p style="margin: 0px 10px;"><?= $page_nr; ?></p>

<?php
$db_view_start = $page_nr - 1;
$db_view = $db_view_start * 10;
$db_view_end = $db_view + 10;

if ($db_total_count > $db_view_end) { ?>
    <form method="post" action="" style="margin-right: 10px;">
        <input type="hidden" name="searchTerm" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="page_nr" value="<?= $page_nr_neste; ?>">
        <input type="submit" value="-->">
    </form>
<?php } else {
    $db_view_end = $db_total_count;
} ?>

<?php if($page_nr != $last_button){ ?>
    <form method="post" action="" style="margin-right: 10px;">
        <input type="hidden" name="searchTerm" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="page_nr" value="<?= $last_button; ?>">
        <input type="submit" value="<?= $last_button; ?>">
    </form>
<?php } ?>

<p style="margin: 0px 10px;">(viser <?= $db_view; ?> - <?= $db_view_end; ?> av <?= $db_total_count; ?>)</p>

</div>

<!-- Pagination complete -->

<div style="overflow-y: auto; max-height: 600px; border: 0px solid white;">
    <table id="scrollable-table">
        <thead>
            <tr>
                <th>Tittel</th>
                <th>Spilletid</th>
                <th>Premieredato</th>
                <th></th>
                <th>Kategori</th>
                <th>Samling</th>
<?php if ($eier_session === 1) {?>
                <th>Eier</th>
<?php }?>
                <th></th><th>Loudness</th>
<?php if ($eier_session === 1) {?>
                <th></th>
<?php }?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_select->num_rows > 0) {
                while ($row = $result_select->fetch_assoc()) {
                    $prog_tittel = htmlspecialchars($row["prog_tittel"] ?? '', ENT_QUOTES, 'UTF-8');
                    $duration = htmlspecialchars($row["duration"] ?? '', ENT_QUOTES, 'UTF-8');
                    $prog_id = htmlspecialchars($row["prog_id"] ?? '', ENT_QUOTES, 'UTF-8');
                    $category = htmlspecialchars($row["cat_id"] ?? '', ENT_QUOTES, 'UTF-8');
                    $collection = htmlspecialchars($row["col_id"] ?? '', ENT_QUOTES, 'UTF-8');
                    $first_day = htmlspecialchars($row["first_day"] ?? '', ENT_QUOTES, 'UTF-8');
                    $eier_username = htmlspecialchars($row["eier"] ?? '', ENT_QUOTES, 'UTF-8');
                    $loudness = "I = " . $row["integrated_loudness"] . 
				"<br />LRA = " . $row["loudness_range"];

            ?>
            <tr>
                <td><?= $prog_tittel; ?></td>
                <td><?= $duration; ?></td>
                <td><div><?= $first_day; ?></div></td>
                <td>
                    <button onclick="showUpdateForm('<?= addslashes($prog_id); ?>', '<?= addslashes($prog_tittel); ?>', '<?= addslashes($duration); ?>', 
			'<?= addslashes($row["live"]); ?>', '<?= addslashes($row["ori_filnavn"]); ?>', '<?= addslashes($row["info"]); ?>', '<?= addslashes($category); ?>', 
			'<?= addslashes($collection); ?>', '<?= addslashes($first_day); ?>', '<?= addslashes($row["filnavn"]); ?>')">Mer</button>
                </td>



                <td>
                    <form id="categoryForm_<?= $prog_id; ?>" method="post" action="">
                        <input type="hidden" name="page_nr" value="<?= $page_nr; ?>">
                        <input type="hidden" name="prog_id" value="<?= $prog_id; ?>">
                        <input type="hidden" name="update" value="update_cat">
                        <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>">

                        <select name="category" onchange="submitForm('categoryForm_<?= $prog_id; ?>')">
                            <option value="">Velg kategori</option>
                            <?php
if($id_session == "1"){
                            $stmt_category = $conn->prepare("SELECT * FROM fk_category");
}
else{
                            $stmt_category = $conn->prepare("SELECT * FROM fk_category WHERE eier LIKE ?");
                            $stmt_category->bind_param('s', $id_session);
}
                            $stmt_category->execute();
                            $result_category = $stmt_category->get_result();
                            while ($row_category = $result_category->fetch_assoc()) {
                                $selected = ($row_category['cat_id'] == $category) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row_category['cat_id']) . "' $selected>" . htmlspecialchars($row_category['cat_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </form>
                </td>





                <td>
                    <form  id="collectionForm_<?= $prog_id; ?>" method="post" action="">
                        <input type="hidden" name="page_nr" value="<?= $page_nr; ?>">
                        <input type="hidden" name="prog_id" value="<?= $prog_id; ?>">
                        <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>">

                        <input type="hidden" name="update" value="update_col">
                        <select name="collection" onchange="submitForm('collectionForm_<?= $prog_id; ?>')">
                            <option value="">Velg samling</option>
                            <?php
if($id_session == "1"){
                            $stmt_collection = $conn->prepare("SELECT * FROM fk_collection");
}
else{
                            $stmt_collection = $conn->prepare("SELECT * FROM fk_collection WHERE eier LIKE ?");
                            $stmt_collection->bind_param('s', $id_session);
}
                            $stmt_collection->execute();
                            $result_collection = $stmt_collection->get_result();
                            while ($row_collection = $result_collection->fetch_assoc()) {
                                $selected = ($row_collection['col_id'] == $collection) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row_collection['col_id']) . "' $selected>" . htmlspecialchars($row_collection['col_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </form>
                </td>



<?php if ($eier_session === 1) {?>
                <td>
                    <form id="eierForm_<?= $prog_id; ?>" method="post" action="">
                        <input type="hidden" name="page_nr" value="<?= $page_nr; ?>">
                        <input type="hidden" name="prog_id" value="<?= $prog_id; ?>">
                        <input type="hidden" name="update" value="update_eier">

                        <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>">

                        <select name="eier_username" onchange="submitForm('eierForm_<?= $prog_id; ?>')">
                            <option value="">Velg eier</option>
                            <?php
                            $stmt_eier = $conn->prepare("SELECT * FROM fk_users");
                            $stmt_eier->execute();
                            $result_eier = $stmt_eier->get_result();
                            while ($row_eier = $result_eier->fetch_assoc()) {
                                $selected = ($row_eier['username'] == $eier_username) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row_eier['username']) . "' $selected>" . htmlspecialchars($row_eier['full_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </form>
                </td>
<?php }?>

                <td>
                    <?php
                    $currentDateTime = date('Y-m-d H:i:s');
                    $stmt_history1 = $conn->prepare("SELECT COUNT(*) AS count FROM fk_sendeplan WHERE prog_id = ? AND sendetid < ?");
                    $stmt_history1->bind_param('ss', $prog_id, $currentDateTime);
                    $stmt_history1->execute();
                    $result_history1 = $stmt_history1->get_result();
                    $count_1 = $result_history1->fetch_assoc()['count'] ?? 0;

                    $stmt_history2 = $conn->prepare("SELECT COUNT(*) AS count FROM fk_sendeplan WHERE prog_id = ? AND sendetid >= ?");
                    $stmt_history2->bind_param('ss', $prog_id, $currentDateTime);
                    $stmt_history2->execute();
                    $result_history2 = $stmt_history2->get_result();
                    $count_2 = $result_history2->fetch_assoc()['count'] ?? 0;
                    ?>
                    <label>(H <?= $count_1; ?> / S <?= $count_2; ?>)</label>
                    <form method="post" action="prog_historikk.php">
                        <input type="hidden" name="prog_id" value="<?= $prog_id; ?>">
                        <input type="hidden" name="prog_tittel" value="<?= $prog_tittel; ?>">
                        <input type="submit" value="Historikk">
                    </form>
                </td>

<td><?= $loudness; ?></td>

<?php if ($eier_session === 1) {?>
                <td>
                    <form method="post" action="">
                        <input type="hidden" name="remove" value="remove">
                        <input type="hidden" name="filename_del" value="<?= htmlspecialchars($row["filnavn"]); ?>">
                        <input type="hidden" name="prog_id" value="<?= $prog_id; ?>">
                        <input type="submit" name="remove" value="Slett">
                    </form>
                </td>
<?php }?>

            </tr>

<?php		}
            } else {
                echo "<p>Ingen videoklipp funnet. Last opp innhold.</p>";
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

<!--
	<video id="videoPlayer" width="320" height="240" controls>
		<source id="videoSource" src="" type="video/mp4">
		Your browser does not support the video tag.
	</video>
-->


        <div class="ramme">
            <h3>Mer informasjon</h3>
            <form method="post" action="">
                <input type="hidden" name="prog_id" id="prog_id">

                <input type="hidden" name="page_nr" value="<?= $page_nr; ?>">
	        <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>">

                <label for="prog_tittel">Tittel</label>
                <input type="text" name="prog_tittel" id="prog_tittel"><br>
                <label for="duration">Spilletid</label>
                <input type="text" name="duration" id="duration" readonly><br>
                <label for="live">Karusell</label><br>
                <div class="box"><input type="checkbox" name="live" id="live" value="1"></div><br>
                <label for="ori_filnavn">Originalt filnavn</label>
                <input type="text" name="ori_filnavn" id="ori_filnavn" readonly><br>
                <label for="filnavn">Filnavn på server</label>
                <input type="text" name="filnavn" id="filnavn" readonly><br>
                <label for="info">Informasjon</label>
                <textarea name="info" id="info" placeholder="Skriv informatsjon of videoklippet her..."></textarea><br>
                <label for="category">Kategori</label><br>
                <input type="text" name="cat_id" id="cat_id" readonly><br>
                <label for="collection">Samling</label><br>
                <input type="text" name="col_id" id="col_id" readonly><br>
                <label for="first_day">Premiere dato</label><br>
                <input type="date" name="first_day" id="first_day"><br>
                <input type="submit" name="update" value="Oppdater">
            </form>









        </div>
    </div>


<?php if ($eier_session === 1) {?>

<h4>Legg til manuelt klipp</h4>
            <form method="post" action="">
                <input type="text" name="dummy_name" id="dummy_name" placeholder="Navn op klipp"><br>
                <label for="filnavn">Filnavn (om stream, rtmp://DOMAIN/NAME/KEY) skriv EMPTY om det ikke foreligger URL enda</label><br>
                <input type="text" name="filnavn" id="filnavn" value="EMPTY" required><br>

    <label for="hours">Time:Minutt:Sekund</label><br>
    <input style="width: 50px;" type="number" id="hours" name="hours" min="0" max="23"  step="1" maxlength="2" pattern="\d{2}" oninput="formatValue(this)" value="00" required>
    <input style="width: 50px;" type="number" id="minutes" name="minutes" min="0" max="59" step="1" maxlength="2" pattern="\d{2}" oninput="formatValue(this)" value="00" required>
    <input style="width: 50px;" type="number" id="seconds" name="seconds" min="0" max="59" step="1" maxlength="2" pattern="\d{2}" oninput="formatValue(this)" value="00" required><br>

                <input type="submit" name="add_dummy" value="Legg til ny i videoarkivet">
            </form><br>
<hr>
<?php }?>

<h4>Legg til Kategori</h4>

            <form method="post" action="">
                <input type="hidden" name="page_nr" value="<?= $page_nr; ?>">
                <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>">
                <input type="hidden" name="user_id" value="<?= $id_session; ?>">
                <input type="text" name="cat_name" id="cat_name" placeholder="Kategori"><br>
                <input type="submit" name="add_cat" value="Legg til ny kategori">
            </form><br>
<hr>

<h4>Legg til Samling</h4>
            <form method="post" action="">
                <input type="hidden" name="page_nr" value="<?= $page_nr; ?>">
                <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo $searchTerm;?>">
                <input type="hidden" name="user_id" value="<?= $id_session; ?>">
                <input type="text" name="col_name" id="col_name" placeholder="Samling"><br>
                <input type="submit" name="add_col" value="Legg til ny samling">
            </form><br>

<hr>


  </div>
</div>




    <script>

    function submitForm(formId) {
        document.getElementById(formId).submit();
    }





function showUpdateForm(prog_id, prog_tittel, duration, live, ori_filnavn, info, cat_id, col_id, first_day, filnavn) {
    // Show the update form
    document.getElementById('updateForm').style.display = 'block';

    // Populate the form fields
    document.getElementById('prog_id').value = prog_id;
    document.getElementById('prog_tittel').value = prog_tittel;
    document.getElementById('duration').value = duration;
    document.getElementById('ori_filnavn').value = ori_filnavn;
    document.getElementById('info').value = info;
    document.getElementById('cat_id').value = cat_id;
    document.getElementById('col_id').value = col_id;
    document.getElementById('first_day').value = first_day;
    document.getElementById('filnavn').value = filnavn;

    // Adjust the live checkbox based on the value of 'live'
    var liveCheckbox = document.getElementById('live');
    liveCheckbox.checked = (live === '1'); // Assuming 'live' is a string representing '1' or '0'

    // Update the video source
    updateVideoSrc(ori_filnavn, filnavn);
}

// Prevent form submission when Enter key is pressed in the textarea
document.getElementById('info').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
});



// Function to update the video source
function updateVideoSrc(ori_filnavn, filnavn) {
    console.log("Updating video source...");

    // Check if the necessary values are available
    if (!ori_filnavn || !filnavn) {
        console.error("Error: 'ori_filnavn' or 'filnavn' is empty or undefined!");
        return;
    }

    // Extract subfolder from 'filnavn'
    const pathSegments = filnavn.split('/');
    if (pathSegments.length < 2) {
        console.error("Error: 'filnavn' does not have enough segments!");
        return;
    }

    const subfolder = pathSegments[1]; // Get the second part
    console.log("Extracted subfolder:", subfolder);

    // Ensure the file extension is included (e.g., .mp4)
    let fileNameWithExtension = ori_filnavn.trim();
    if (!fileNameWithExtension.endsWith('.mp4')) {
        fileNameWithExtension += '.mp4';
    }

    // Construct the new video source path
    const newSrc = `webfiles/${subfolder}/mp4web/${fileNameWithExtension}`;
    console.log("Constructed video source:", newSrc);

    // Update the video player
    const videoSource = document.getElementById('videoSource');
    videoSource.src = newSrc;

    // Reload the video to apply the new source
    const videoPlayer = document.getElementById('videoPlayer');
    videoPlayer.load();
}







function formatValue(input) {
    // Remove any non-digit characters
    var value = input.value.replace(/\D/g, '');
    // Add leading zero if necessary
    if (value.length == 1) {
        value = '0' + value;
    }
    // Update the input field value
    input.value = value;
}


    </script>



</body>
</html>

<?php
// Check if the connection exists and close it properly
if (isset($conn) && $conn->ping()) {
    $conn->close();
} else {
    // Redirect to another URL if the connection is not valid
    header('Location: https://frikanalen.hardang.com/video_archive.php');
    exit(); // Stop script execution after the redirect
}
?>
