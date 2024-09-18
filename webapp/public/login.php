<?php

session_start();

$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

unset($error_message);

if ($conn->connect_error) {
    $errorMessage = "Connection failed: " . $conn->connect_error;    
    die($errorMessage);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND approved = 1";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
       
        $userFromDB = $result->fetch_assoc();

        //$_COOKIE['authenticated'] = $username;
        setcookie('authenticated', $username, time() + 3600, '/');     

        if ($userFromDB['default_role_id'] == 1)
        {        
            setcookie('isSiteAdministrator', true, time() + 3600, '/');                
        }else{
            unset($_COOKIE['isSiteAdministrator']); 
            setcookie('isSiteAdministrator', '', -1, '/'); 
        }
        header("Location: index.php");
        exit();
    } else {
        $error_message = 'Invalid username or password.';  
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
    <title>Login Page</title>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center">Login</h2>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="mt-3 text-center">
                <a href="./users/request_account.php" class="btn btn-secondary btn-block">Request an Account</a>
            </div>
        </div>
    </div>
</body>
</html>
