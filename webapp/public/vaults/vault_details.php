<?php

// Replace with your database connection details
$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';


$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die ('A fatal error occurred and has been logged.');
    // die("Connection failed: " . $conn->connect_error);
}

$uploadDir = './uploads/'; // Specify the directory where you want to save the uploaded files


// Add Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['addUsername']) && isset ($_POST['addWebsite']) && isset ($_POST['addPassword']) && isset ($_POST['vaultId'])) {
    $addUsername = $_POST['addUsername'];
    $addWebsite = $_POST['addWebsite'];
    $addPassword = $_POST['addPassword'];
    $addNotes = $_POST['addNotes'];
    $vaultId = $_POST['vaultId'];

    // Check if a file is uploaded
    if (!empty ($_FILES['file']['name'])) {
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            $filePath = "'" . $uploadFile . "'";
        } else {
            // Handle file upload error            
            die ('Error uploading file.');
        }
    } else {
        // If no file is uploaded, set the file path to NULL
        $filePath = "NULL";
    }

    $queryAddPassword = "INSERT INTO vault_passwords (vault_id, username, website, password, notes, file_path) 
                     VALUES ($vaultId, '$addUsername', '$addWebsite', '$addPassword', '$addNotes', $filePath)";

    $resultAddPassword = $conn->query($queryAddPassword);

    if (!$resultAddPassword) {

        die ('A fatal error occurred and has been logged.');
        // die("Error adding password: " . $conn->error);
    }
    // Redirect to the current page after adding the password
    header("Location: {$_SERVER['PHP_SELF']}?vault_id=$vaultId");
    exit();
}

// Edit Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['editPasswordId']) && isset ($_POST['editUsername']) && isset ($_POST['editPassword']) && isset ($_POST['editWebsite']) && isset ($_POST['vaultId'])) {
    $editUsername = $_POST['editUsername'];
    $editWebsite = $_POST['editWebsite'];
    $editPassword = $_POST['editPassword'];
    $editNotes = $_POST['editNotes'];
    $editPasswordId = $_POST['editPasswordId'];
    $vaultId = $_POST['vaultId'];

    // Check if a new file is uploaded
    if (!empty ($_FILES['editFile']['name'])) {
        $updateFile = $uploadDir . basename($_FILES['editFile']['name']);

        if (move_uploaded_file($_FILES['editFile']['tmp_name'], $updateFile)) {
            $filePath = $updateFile;
        } else {

            die ('Error uploading file.');
        }
    } else {
        // If no new file is uploaded, preserve the existing file path
        $queryGetFilePath = "SELECT file_path FROM vault_passwords WHERE password_id = $editPasswordId";
        $resultGetFilePath = $conn->query($queryGetFilePath);

        if ($resultGetFilePath && $resultGetFilePath->num_rows > 0) {
            $row = $resultGetFilePath->fetch_assoc();
            $existingFilePath = $row['file_path'];
            $filePath = $existingFilePath;
        } else {
            // Handle error if existing file path retrieval fails

            die ('Error retrieving existing file path.');
        }
    }

    // Check if filePath is NULL to properly format it in the query
    if ($filePath === "NULL" || $filePath === null) {
        $filePathSQL = "NULL";
    } else {
        $filePathSQL = "'" . $filePath . "'";
    }

    $queryEditPassword = "UPDATE vault_passwords 
                        SET username = '$editUsername', website = '$editWebsite', 
                        password = '$editPassword', notes = '$editNotes', file_path = $filePathSQL
                        WHERE password_id = $editPasswordId";

    $resultEditPassword = $conn->query($queryEditPassword);

    if (!$resultEditPassword) {

        die ('A fatal error occurred and has been logged. File: ' . $filePathSQL . 'Query: ' . $queryEditPassword . ' Error: ' . $conn->error);
    }

    // Redirect to the current page after updating the password
    header("Location: {$_SERVER['PHP_SELF']}?vault_id=$vaultId");
    exit();
}


// Delete Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['deletePasswordId']) && isset ($_POST['vaultId'])) {
    $deletePasswordId = $_POST['deletePasswordId'];
    $vaultId = $_POST['vaultId'];

    $queryDeletePassword = "DELETE FROM vault_passwords WHERE password_id = $deletePasswordId";
    $resultDeletePassword = $conn->query($queryDeletePassword);

    if (!$resultDeletePassword) {

        die ('A fatal error occurred and has been logged.');
        // die("Error deleting password: " . $conn->error);
    }

    // Redirect to the current page after deleting the password
    header("Location: {$_SERVER['PHP_SELF']}?vault_id=$vaultId");
    exit();
}

// Retrieve vault information
$vaultId = isset ($_GET['vault_id']) ? $_GET['vault_id'] : 0;

$query = "SELECT vault_name FROM vaults WHERE vault_id = $vaultId";
$result = $conn->query($query);

if (!$result) {
    die ("Query failed: " . $conn->error);
}

$row = $result->fetch_assoc();
$vaultName = $row['vault_name'];


$queryPasswords = "SELECT * FROM vault_passwords WHERE vault_id = $vaultId";

$searchQuery = "";
//Handle a Search request
if (isset ($_GET['searchQuery']) && !empty ($_GET['searchQuery'])) {
    $searchQuery = $_GET['searchQuery'];    
    $queryPasswords = "SELECT * FROM vault_passwords            
            WHERE vault_id = $vaultId
            AND (vault_passwords.username LIKE '%$searchQuery%' OR vault_passwords.website LIKE '%$searchQuery%')";
}

// Retrieve passwords for the vault

$resultPasswords = $conn->query($queryPasswords);

if (!$resultPasswords) {
    die ("Query failed: " . $conn->error);
}

$queryVaultOwner = "SELECT *
                    FROM vault_permissions, users
                    WHERE vault_permissions.vault_id = $vaultId
                    AND vault_permissions.role_id = 1
                    AND vault_permissions.user_id = users.user_id
                    AND users.username = '" . $_COOKIE['authenticated'] . "'";

$resultIsOwner = $conn->query($queryVaultOwner);

$isVaultOwner = 0;

if ($resultIsOwner->num_rows > 0) {
    $isVaultOwner = true;
}



// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['deleteFilePasswordId']) && isset ($_POST['deleteFileSubmit'])) {
    $deleteFilePasswordId = $_POST['deleteFilePasswordId'];
    $vaultId = $_POST['deleteFileVaultId'];

    // Retrieve the file path from the database using the password id
    $queryGetFilePath = "SELECT file_path FROM vault_passwords WHERE password_id = $deleteFilePasswordId";
    $resultGetFilePath = $conn->query($queryGetFilePath);

    if ($resultGetFilePath && $resultGetFilePath->num_rows > 0) {
        $row = $resultGetFilePath->fetch_assoc();
        $filePathToDelete = $row['file_path'];

        // Delete the file from the server
        if ($filePathToDelete && file_exists($filePathToDelete)) {
            if (unlink($filePathToDelete)) {
                // File deleted successfully
                // Now update the file path in the database to NULL
                $queryUpdateFilePath = "UPDATE vault_passwords SET file_path = NULL WHERE password_id = $deleteFilePasswordId";
                $resultUpdateFilePath = $conn->query($queryUpdateFilePath);

                if (!$resultUpdateFilePath) {
                    die ('A fatal error occurred and has been logged.');
                }
            } else {
                die ('A fatal error occurred while deleting the file.');
            }
        } else {
            die ('The file to be deleted does not exist.');
        }
    } else {
        die ('The file path associated with the password was not found.');
    }

    // Redirect to the current page after deleting the file
    header("Location: {$_SERVER['PHP_SELF']}?vault_id=$vaultId");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo $vaultName; ?> Vault
    </title>
    <!-- Add Bootstrap CSS link here -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>

    <?php include '../components/nav-bar.php'; ?>


    <div class="container mt-4">
        <h2>
            <?php echo $vaultName; ?> Vault Passwords
        </h2>
        <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#addPasswordModal">
            Add Password
        </button>
        <?php if ($isVaultOwner): ?>
            <a href="./vault_permissions.php?vault_id=<?php echo $vaultId ?>" class="btn btn-warning mb-2"> Edit Vault
                Permissons </a>
        <?php endif; ?>

        <input type="text" id="searchInput" onkeyup="searchTable(event)" placeholder="Search for passwords..."
            class="form-control mb-3">
            <?php if (!empty ($searchQuery)) {
                    // If $searchQuery is not blank, display the label with its value
                    echo "<label>Search Results for : " . $searchQuery . "</label>"; 
                } ?>
        <table class="table table-bordered" id="passwordTable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Website</th>
                    <th>Password</th>
                    <th>Notes</th>
                    <th>Actions</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($rowPassword = $resultPasswords->fetch_assoc()): ?>
                    <tr data-password-id="<?php echo $rowPassword['password_id']; ?>">
                        <td>
                            <?php echo $rowPassword['username']; ?>
                        </td>
                        <td>
                            <?php echo $rowPassword['website']; ?>
                        </td>
                        <td>
                            <input type="password" class="password-field" value="<?php echo $rowPassword['password']; ?>"
                                disabled>
                        </td>
                        <td>
                            <?php echo $rowPassword['notes']; ?>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm show-password-btn">Show Password</button>
                            <button class="btn btn-warning btn-sm edit-password-btn" data-toggle="modal"
                                data-target="#editPasswordModal" data-password-notes="<?php echo $rowPassword['notes']; ?>"
                                data-password-password="<?php echo $rowPassword['password']; ?>"
                                data-password-website="<?php echo $rowPassword['website']; ?>"
                                data-password-username="<?php echo $rowPassword['username']; ?>"
                                data-password-id="<?php echo $rowPassword['password_id']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm delete-password-btn" data-toggle="modal"
                                data-target="#deletePasswordModal"
                                data-password-id="<?php echo $rowPassword['password_id']; ?>">Delete</button>
                        </td>
                        <td>
                            <?php if (!empty ($rowPassword['file_path'])): ?>
                                <a href="download_file.php?file=<?php echo urlencode($rowPassword['file_path']); ?>&vault_id=<?php echo urlencode($vaultId); ?>"
                                    target="_blank">Download File</a>
                                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                    <input type="hidden" name="deleteFilePasswordId"
                                        value="<?php echo $rowPassword['password_id']; ?>">
                                    <input type="hidden" name="deleteFileVaultId" value="<?php echo $vaultId; ?>">
                                    <button type="submit" name="deleteFileSubmit" class="btn btn-danger btn-sm">Delete
                                        File</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var showPasswordButtons = document.querySelectorAll('.show-password-btn');
            showPasswordButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var passwordField = button.closest('tr').querySelector('.password-field');
                    passwordField.type = (passwordField.type === 'password') ? 'text' : 'password';
                    if (button.textContent == 'Show Password') {
                        button.textContent = 'Hide Password';
                    } else {
                        button.textContent = 'Show Password';
                    }
                });
            });
        });
    </script>


    <div class="modal" id="addPasswordModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add New Password</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Add form for adding a new password here -->
                    <form method="POST" id="addPasswordForm" enctype="multipart/form-data">
                        <input type="hidden" id="addVaultId" name="vaultId" value="<?php echo $vaultId; ?>">
                        <div class="form-group">
                            <label for="addUsername">Username:</label>
                            <input type="text" class="form-control" id="addUsername" name="addUsername" required>
                        </div>
                        <div class="form-group">
                            <label for="addWebsite">Website:</label>
                            <input type="text" class="form-control" id="addWebsite" name="addWebsite" required>
                        </div>
                        <div class="form-group">
                            <label for="addPassword">Password:</label>
                            <input type="password" class="form-control" id="addPassword" name="addPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="addNotes">Notes:</label>
                            <textarea class="form-control" id="addNotes" name="addNotes" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="file">File:</label>
                            <input type="file" name="file">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Password</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for editing a password -->
    <div class="modal" id="editPasswordModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Edit Password</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Add form for editing a password here -->
                    <form method="POST" id="editPasswordForm" enctype="multipart/form-data">
                        <input type="hidden" id="editVaultId" name="vaultId" value="<?php echo $vaultId; ?>">
                        <div class="form-group">
                            <label for="editUsername">Username:</label>
                            <input type="text" class="form-control" id="editUsername" name="editUsername" required>
                        </div>
                        <div class="form-group">
                            <label for="editWebsite">Website:</label>
                            <input type="text" class="form-control" id="editWebsite" name="editWebsite" required>
                        </div>
                        <div class="form-group">
                            <label for="editPassword">Password:</label>
                            <input type="password" class="form-control" id="editPassword" name="editPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="editNotes">Notes:</label>
                            <textarea class="form-control" id="editNotes" name="editNotes" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editFile">File:</label>
                            <input type="file" name="editFile">
                        </div>
                        <input type="hidden" id="editPasswordId" name="editPasswordId">
                        <button type="submit" class="btn btn-warning">Update Password</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for deleting a password -->
    <div class="modal" id="deletePasswordModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Delete Password</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <p>Are you sure you want to delete this password?</p>
                    <!-- Add hidden input for password ID -->
                    <form method="POST" id="deletePasswordForm">
                        <input type="hidden" id="deleteVaultId" name="vaultId" value="<?php echo $vaultId; ?>">
                        <input type="hidden" id="deletePasswordId" name="deletePasswordId">
                        <button type="submit" class="btn btn-danger" id="confirmDeletePasswordBtn">Delete</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS and Popper.js scripts here -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- Add your custom JavaScript script for handling modals and row click redirection -->
    <script>

        function searchTable(event) {

            if (event.keyCode === 13) {
                var searchInput = document.getElementById("searchInput").value;
                window.location.href = "./vault_details.php?vault_id=<?php echo $vaultId ?>&searchQuery=" + searchInput;
            }
        }


        document.addEventListener("DOMContentLoaded", function () {
            // Handle edit button click for passwords
            var editPasswordButtons = document.querySelectorAll('.edit-password-btn');
            editPasswordButtons.forEach(function (button) {
                button.addEventListener('click', function () {

                    document.getElementById('editPasswordId').value = button.getAttribute('data-password-id');;
                    document.getElementById('editUsername').value = button.getAttribute('data-password-username');;
                    document.getElementById('editWebsite').value = button.getAttribute('data-password-website');;
                    document.getElementById('editPassword').value = button.getAttribute('data-password-password');;
                    document.getElementById('editNotes').value = button.getAttribute('data-password-notes');;
                });
            });

            // Handle delete button click for passwords
            var deletePasswordButtons = document.querySelectorAll('.delete-password-btn');
            deletePasswordButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var passwordId = button.getAttribute('data-password-id');
                    console.log('Setting Delete Password ID to : ' + passwordId);
                    document.getElementById('deletePasswordId').value = passwordId
                });
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>