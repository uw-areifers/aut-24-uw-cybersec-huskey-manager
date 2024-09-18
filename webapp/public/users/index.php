<?php

include '../components/authenticate.php';

$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {    
    die('A fatal error occurred and has been logged.');
    // die("Connection failed: " . $conn->connect_error);
}

// Fetch users from the database
$queryUsers = "SELECT * FROM users";
$resultUsers = $conn->query($queryUsers);
$editUsers = $conn->query($queryUsers);

// Handle form submissions (e.g., Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'add_user':
                // Handle adding a user
                if (isset($_POST['username']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['password'])) {
                    $username = $conn->real_escape_string($_POST['username']);
                    $first_name = $conn->real_escape_string($_POST['first_name']);
                    $last_name = $conn->real_escape_string($_POST['last_name']);
                    $email = $conn->real_escape_string($_POST['email']);
                    $password = $conn->real_escape_string($_POST['password']);
                    

                    $query = "INSERT INTO users (username, first_name, last_name, email, password, default_role_id) VALUES ('$username', '$first_name', '$last_name', '$email', '$password', 3, 1)";
                    $result = $conn->query($query);

                    if (!$result) {
                      
                        die('A fatal error occurred and has been logged.');
                        // die("Error adding user: " . $conn->error);
                    }

                      
                      // Redirect to the current page after handling a POST
                      header("Location: {$_SERVER['PHP_SELF']}");
                      exit();
                }
                break;

            case 'edit_user':
                // Handle editing a user
                if (isset($_POST['user_id']) && isset($_POST['username']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])) {
                    $user_id = $_POST['user_id'];
                    $username = $conn->real_escape_string($_POST['username']);
                    $first_name = $conn->real_escape_string($_POST['first_name']);
                    $last_name = $conn->real_escape_string($_POST['last_name']);
                    $email = $conn->real_escape_string($_POST['email']);
                    
                    //convert the appoved return value to a database value
                    if (isset($_POST['approved']) && isset($_POST['approved']) == 'on') {
                        $approved = 1;
                    } else {
                        $approved = 0;
                    }


                    $query = "UPDATE users SET username='$username', first_name='$first_name', last_name='$last_name', email='$email', approved='$approved' WHERE user_id=$user_id";
                    $result = $conn->query($query);

                    if (!$result) {
                        
                        die("Error editing user: " . $conn->error);
                        // die('A fatal error occurred and has been logged.');
                        
                    }

                    
                    header("Location: {$_SERVER['PHP_SELF']}");
                    exit();
                }
                break;

            case 'delete_user':
                // Handle deleting a user
                if (isset($_POST['user_id'])) {
                    $user_id = $_POST['user_id'];

                    $query = "DELETE FROM users WHERE user_id=$user_id";
                    $result = $conn->query($query);

                    if (!$result) {
                        
                        die('A fatal error occurred and has been logged.');
                        // die("Error deleting user: " . $conn->error);
                    }
                    

                        header("Location: {$_SERVER['PHP_SELF']}");
                        exit();
                }
                break;
        }
    }
}

?>

<?php include '../components/nav-bar.php'?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<!-- Add additional scripts as needed -->
<script>
    // JavaScript to set the user_id in the modals
    $(document).ready(function () {
        $('.edit-btn').click(function () {
            var userId = $(this).data('userid');
            $('#editUserId').val(userId);
            var username = $(this).data('username');
            $('#editUsername').val(username);
            var first_name = $(this).data('first_name');
            $('#editFirstName').val(first_name);
            var last_name = $(this).data('last_name');
            $('#editLastName').val(last_name);
            var email = $(this).data('email');
            $('#editEmail').val(email);
            var approved = $(this).data('approved');
            $('#editApproved').prop('checked', approved == 1);
        });

        $('.delete-btn').click(function () {            
            var userId = $(this).data('userid');
            $('#deleteUserId').val(userId);
        });
    });
</script>

<script>    
               function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("usersTable");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0]; // Change index based on the column you want to search
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
</script>
</head>
<body>

<div class="container mt-4">
    <h2>User Management</h2>

    <!-- Add User button to open a modal for adding a new user -->
    <button class="btn btn-success" data-toggle="modal" data-target="#addUserModal" style="margin-bottom: 10px;">Add User</button>

    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for users..." class="form-control mb-3">
    <!-- User Table -->
    <table id="usersTable" class="table">
        <thead>
        <tr>
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($user = $resultUsers->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['first_name']; ?></td>
                <td><?php echo $user['last_name']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td>
                    <!-- Edit button to open a modal for editing a user -->
                    <button class="btn btn-warning btn-sm edit-btn" data-toggle="modal"  data-first_name="<?php echo $user['first_name']; ?>"  data-last_name="<?php echo $user['last_name']; ?>" data-approved="<?php echo $user['approved']; ?>" data-email="<?php echo $user['email']; ?>" data-username="<?php echo $user['username']; ?>" data-userid="<?php echo $user['user_id']; ?>" data-target="#editUserModal">
                        Edit
                    </button>
                    <!-- Delete button to open a modal for deleting a user -->
                    <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-userid="<?php echo $user['user_id']; ?>" data-target="#deleteUserModal">
                        Delete
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add your form for adding a user here -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="action" value="add_user">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="firstName">First Name:</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name:</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Approved User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add your form for editing a user here -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="editUserId" value="">
                        
                    <div class="form-group">
                        <label for="editUsername">Username:</label>
                        <input type="text" class="form-control" id="editUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="editFirstName">First Name:</label>
                        <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editLastName">Last Name:</label>
                        <input type="text" class="form-control" id="editLastName" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email:</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="approved" id="editApproved">
                        <label class="form-check-label" for="editApproved">User Approved</label>
                    </div>                   
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <!-- Add your form for deleting a user here -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="deleteUserId" value="">
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>


</body>

</html>

<?php
$conn->close();
?>
