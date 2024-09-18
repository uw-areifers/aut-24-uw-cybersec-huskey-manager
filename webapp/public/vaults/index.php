<?php

include '../components/authenticate.php';


$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {

    die ('A fatal error occurred and has been logged.');
    //die("Connection failed: " . $conn->connect_error);
}

// Add Vault
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['vaultName'])) {
    $vaultName = $_POST['vaultName'];
    $userId = 1; // Replace with the actual user ID

    $query = "INSERT INTO vaults (vault_name) VALUES ('$vaultName')";
    $result = $conn->query($query);

    if (!$result) {

        die ('A fatal error occurred and has been logged.');
        // die("Error adding vault: " . $conn->error);
    }

    // Retrieve the ID of the inserted vault
    $insertedVaultId = $conn->insert_id;

    // We need to fetch the user_id based off the username in order to complete the permission insert, we are going to default to Owner for the role so we can hardcode that without looking it up

    $user = $_COOKIE['authenticated'];
    $queryFetchUserId = "SELECT user_id FROM users WHERE username = '$user'";
    $resultFetchUserId = $conn->query($queryFetchUserId);

    if ($resultFetchUserId && $resultFetchUserId->num_rows > 0) {
        // Fetch user_id from the result set
        $row = $resultFetchUserId->fetch_assoc();
        $userId = $row['user_id'];
        $roleId = 1;

        // If user_id is found, insert the permission
        $queryInsertPermission = "INSERT INTO vault_permissions (user_id, vault_id, role_id) VALUES ($userId, $insertedVaultId, $roleId)";
        $resultInsertPermission = $conn->query($queryInsertPermission);

        if (!$resultInsertPermission) {

            //  die("Error adding permission, Query : " .  $queryInsertPermission . " Error Info : " . $conn->error);
            die ('A fatal error occurred while adding permission.');
        }
    } else {
        die ("User with username '$user' not found.");
    }

    // Redirect to the current page after adding the vault
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Edit Vault
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['editVaultName']) && isset ($_POST['editVaultId'])) {
    $editVaultName = $_POST['editVaultName'];
    $editVaultId = $_POST['editVaultId'];

    $query = "UPDATE vaults SET vault_name = '$editVaultName' WHERE vault_id = $editVaultId";
    $result = $conn->query($query);

    if (!$result) {
        die ('A fatal error occurred and has been logged.');
        // die("Error editing vault: " . $conn->error);
    }

    // Redirect to the current page after editing the vault
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Delete Vault
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['deleteVaultId']) && !empty ($_POST['deleteVaultId'])) {
    $deleteVaultId = $_POST['deleteVaultId'];

    $query = "DELETE FROM vaults WHERE vault_id = $deleteVaultId";

    $result = $conn->query($query);

    if (!$result) {
        die ('A fatal error occurred and has been logged.');
        //die("Error deleting vault: " . $conn->error);
    }

    // Redirect to the current page after deleting the vault
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Retrieve vaults from the database
if ($_COOKIE['isSiteAdministrator'] == true) {
    $query = "SELECT vaults.vault_id, vaults.vault_name
               FROM vaults";
} else {
    $query = "SELECT vaults.vault_id, vaults.vault_name
    FROM vaults, vault_permissions, users
    WHERE vaults.vault_id = vault_permissions.vault_id
    AND vault_permissions.user_id = users.user_id
    AND users.username = '" . $_COOKIE['authenticated'] . "'";
}

$searchQuery = "";
//Handle a Search request
if (isset ($_GET['searchQuery']) && !empty ($_GET['searchQuery'])) {
    $searchQuery = $_GET['searchQuery'];    
    $query = "SELECT vaults.vault_id, vaults.vault_name
            FROM vaults
            WHERE vaults.vault_name LIKE '%$searchQuery%'";
}

$result = $conn->query($query);

if (!$result) {
    die ("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Vaults</title>
    <!-- Add Bootstrap CSS link here -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>

    <?php include '../components/nav-bar.php'; ?>

    <div class="container mt-4">
        <h2>Password Vaults</h2>

        <!-- Add button to open a modal for adding a new vault -->
        <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#addVaultModal">
            Add Vault
        </button>

        <!-- Table to display vaults -->
        <table class="table">
            <thead>
                
                <input type="text" id="searchInput" onkeypress="searchTable(event)" placeholder="Search for vaults..."
                    class="form-control mb-3">
                    <?php if (!empty ($searchQuery)) {
                    // If $searchQuery is not blank, display the label with its value
                    echo "<label>Search Results for : " . $searchQuery . "</label>"; 
                } ?>
                    <table class="table table-bordered" id="vaultTable">
                    <thead>
                        <tr>
                            <th>Vault Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>

                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['vault_name']; ?>
                                </td>
                                <td>
                                    <a href="vault_details.php?vault_id=<?php echo $row['vault_id']; ?>"
                                        class="btn btn-primary btn-sm" role="button" aria-disabled="true">View Vault</a>

                                    <!-- Edit button to open a modal for editing a vault -->
                                    <button class="btn btn-warning btn-sm edit-btn" data-toggle="modal"
                                        data-target="#editVaultModal" data-vault-name="<?php echo $row['vault_name']; ?>"
                                        data-vault-id="<?php echo $row['vault_id']; ?>">Edit</button>

                                    <!-- Delete button to open a modal for deleting a vault -->
                                    <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal"
                                        data-target="#deleteVaultModal" data-vault-name="<?php echo $row['vault_name']; ?>"
                                        data-vault-id="<?php echo $row['vault_id']; ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
    </div>

    <!-- Modal for adding a new vault -->
    <div class="modal" id="addVaultModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add New Vault</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Add form for adding a new vault here -->
                    <form method="POST" id="addVaultForm">
                        <div class="form-group">
                            <label for="vaultName">Vault Name:</label>
                            <input type="text" class="form-control" id="vaultName" name="vaultName" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Vault</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for editing a vault -->
    <div class="modal" id="editVaultModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Edit Vault</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Add form for editing a vault here -->
                    <form method="POST" id="editVaultForm">
                        <div class="form-group">
                            <input type="hidden" id="editVaultId" name="editVaultId">
                            <label for="editVaultName">Vault Name:</label>
                            <input type="text" class="form-control" id="editVaultName" name="editVaultName" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Update Vault</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for deleting a vault -->
    <div class="modal" id="deleteVaultModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Delete Vault</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <p id="deleteWarningPara"></p>
                    <!-- Add hidden input for vault ID -->
                    <form method="POST" id="deleteVaultForm">
                        <input type="hidden" id="deleteVaultId" name="deleteVaultId">
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS and Popper.js scripts here -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- Add your custom JavaScript script for handling modals, filtering, and row click redirection -->
    <script>


        function searchTable(event) {

            if (event.keyCode === 13) {
                var searchInput = document.getElementById("searchInput").value;
                window.location.href = "./index.php?searchQuery=" + searchInput;
            }


            // filter = input.value.toUpperCase();
            // table = document.getElementById("vaultTable");
            // tr = table.getElementsByTagName("tr");

            // for (i = 0; i < tr.length; i++) {
            //     td = tr[i].getElementsByTagName("td")[0]; // Change index based on the column you want to search
            //     if (td) {
            //         txtValue = td.textContent || td.innerText;
            //         if (txtValue.toUpperCase().indexOf(filter) > -1) {
            //             tr[i].style.display = "";
            //         } else {
            //             tr[i].style.display = "none";
            //         }
            //     }
            // }        
        }


        // Handle edit button click
        var editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var vaultId = button.getAttribute('data-vault-id');
                var vaultName = button.getAttribute('data-vault-name');
                // You can use vaultId to populate the edit modal, if needed
                document.getElementById('editVaultId').value = vaultId;
                document.getElementById('editVaultName').value = vaultName;
            });
        });

        // Handle delete button click
        var deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var vaultId = button.getAttribute('data-vault-id');
                var vaultName = button.getAttribute('data-vault-name');
                // You can use vaultId to populate the delete modal, if needed

                document.getElementById('deleteVaultId').value = vaultId;
                document.getElementById('deleteWarningPara').innerText = 'Are you sure you want to delete the ' + vaultName + ' vault?';
            });
        });


    </script>
</body>

</html>

<?php
$conn->close();
?>