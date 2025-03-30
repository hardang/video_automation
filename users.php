<?php
session_start(); // Place session_start() at the beginning of the file

// Check if the user is logged in as admin
$eier_session = $_SESSION['admin'];

if($eier_session == "1") {

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

    // Check if form is submitted for field update
    if (isset($_POST["update"]) && $_POST["update"] == "UPDATE") {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $admin = $_POST['admin'];
        $full_name = $_POST['full_name'];
        $kontaktperson = $_POST['kontaktperson'];
        $epost = $_POST['epost'];
        $telefon = $_POST['telefon'];
        $nettsted = $_POST['nettsted'];

        // Update the record in the database based on the selected user ID
        $sql_update = "UPDATE fk_users SET
         username = '$username',
         password = '$password',
         admin = '$admin',
         full_name = '$full_name',
         kontaktperson = '$kontaktperson',
         epost = '$epost',
         telefon = '$telefon',
         nettsted = '$nettsted'
         WHERE id = '$id'";

        if ($conn->query($sql_update) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    // Check if form is submitted for user removal
    if (isset($_POST["remove"])) {
        $user_id = $_POST['user_id'];

        // Avoid removing the currently logged-in user
        if ($_SESSION['user_id'] != $user_id) {
            // Remove the user from the database
            $sql_remove = "DELETE FROM fk_users WHERE id = '$user_id'";

            if ($conn->query($sql_remove) === TRUE) {
                echo "User removed successfully";
            } else {
                echo "Error removing user: " . $conn->error;
            }
        } else {
            echo "You cannot remove yourself";
        }
    }

    // Retrieve data from the database
    $sql_select = "SELECT * FROM fk_users";
    $result_select = $conn->query($sql_select);
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brukere</title>
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
    </style>
</head>
<body>

                <?php include 'meny.php'; ?>

<H2>Legg til ny bruker</H2>
    <form method="post" action="register.php">
        <input type="text" id="add_username" name="add_username" placeholder="Brukernavn" required><br>
        <input type="password" id="add_password" name="add_password" placeholder="Passord" required><br>
        <input type="submit" value="Registrer">
    </form>

<h2>Brukere</h2>
    <table>
    <?php
    if ($result_select->num_rows > 0) {
        while ($row = $result_select->fetch_assoc()) {
            if($row["username"] != $_SESSION['username']) {
                echo "<tr><th>ID: '" . $row["id"] . "'</th><th>Brukernavn: '" . $row["username"] . "'</th><th>Fullt navn: '" . $row["full_name"] . "'
                   </th><th><div class='user-actions'>
                   <button onclick=\"showUpdateForm('" . $row["id"] . "', '" . $row["username"] . "', '" . $row["password"] . "', '" . $row["admin"] . "', '" . $row["full_name"] . "', '" . $row["kontaktperson"] . "', '" . $row["epost"] . "', '" . $row["telefon"] . "', '" . $row["nettsted"] . "')\">OPPDATER</button>
                   </th><th><form method='post' action=''>
                       <input type='hidden' name='user_id' value='" . $row["id"] . "'>
                       <input type='submit' name='remove' value='FJERN'>
                   </form>
                   </div>
                   </th><tr>";
            } else {
                echo "<tr><th>ID: '" . $row["id"] . "'</th><th>Brukernavn: '" . $row["username"] . "'</th><th>Fullt navn: '" . $row["full_name"] . "'
                   </th><th><div class='user-actions'>
                   <button onclick=\"showUpdateForm('" . $row["id"] . "', '" . $row["username"] . "', '" . $row["password"] . "', '" . $row["admin"] . "', '" . $row["full_name"] . "', '" . $row["kontaktperson"] . "', '" . $row["epost"] . "', '" . $row["telefon"] . "', '" . $row["nettsted"] . "')\">OPPDATER</button>
                   </div>
                   </th><th></th><tr>";
            }
        }
    } else {
        echo "No records found";
    }
    ?>
    </table>

<div id="updateForm">
      <div class="ramme">
        <h3>Update Form</h3>
        <form method="post" action="">
            <input type="hidden" name="id" id="id">
            <input type="text" name="username" id="username" readonly><br>
            <input type="password" name="password" id="password" placeholder="Passord"><br>
            <input type="text" name="admin" id="admin" placeholder="Admin = 1, Bruker = 0"><br>
            <input type="text" name="full_name" id="full_name" placeholder="Fullt navn"><br>
            <input type="text" name="kontaktperson" id="kontaktperson" placeholder="Kontakperson"><br>
            <input type="email" name="epost" id="epost" placeholder="Epost"><br>
            <input type="text" name="telefon" id="telefon" placeholder="Telefon"><br>
            <input type="text" name="nettsted" id="nettsted" placeholder="Nettsted"><br>
            <input type="submit" name="update" value="UPDATE">
        </form>
      </div>
    </div>

<script>
    function showUpdateForm(id, username, password, admin, full_name, kontaktperson, epost, telefon, nettsted) {
        document.getElementById('updateForm').style.display = 'block';
        document.getElementById('id').value = id;
        document.getElementById('username').value = username;
        document.getElementById('password').value = password;
        document.getElementById('admin').value = admin;
        document.getElementById('full_name').value = full_name;
        document.getElementById('kontaktperson').value = kontaktperson;
        document.getElementById('epost').value = epost;
        document.getElementById('telefon').value = telefon;
        document.getElementById('nettsted').value = nettsted;
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
