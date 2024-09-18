<?php

$servername = "backend-mysql-database";
$username = "user";
$password = "supersecretpw";
$dbname = "password_manager";

$conn = new mysqli($servername, $username, $password, $dbname);

unset($error_message);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    

    $sql = "INSERT INTO users (username, first_name, last_name, email, password, default_role_id, approved) 
            VALUES ('$username', '$firstName', '$lastName', '$email', '$password', 3, 0)";

    if ($conn->query($sql) === TRUE) {
        header("Location: /login.php");
        exit();
    } else {
        $error_message = 'Error creating account: ' . $conn->error;
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Request Account</title>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center">Request Account</h2>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="request_account.php" method="post">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Request Account</button>
            </form>
        </div>
    </div>
</body>
</html>
