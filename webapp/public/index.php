<?php include './components/authenticate.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UW HusKey Manager</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Bootstrap JS and other scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        .footer-custom {
            background-color: #4b2e83 !important;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php include './components/nav-bar.php'?>

<!-- Main Content Area -->
<div class="container mt-4">
    
    <h2>Welcome to the UW HusKey Manager Project!</h2>

    <p>Congratulations on getting your Docker Based web app up and running! This quarter we will be embarking on a journey to build a basic password manaager application. Throughout this course, you will have the opportunity to develop, enhance, and secure an application that plays a crucial role in managing assets effectively.</p>

    <p>Our focus during this quarter will not only be on developing a password manager system but also on identifying and resolving real-world cybersecurity issues. This hands-on experience will provide you with valuable insights into securing web applications, a skill set in high demand in today's technology landscape.</p>

    <p>As we progress, you will encounter various challenges related to cybersecurity, ranging from authentication and authorization to secure data storage and transmission. These challenges are designed to simulate real-world scenarios, allowing you to apply and reinforce the concepts learned in class.</p>

    <p>Remember, security is not just a feature; it's a continuous process. By the end of this quarter, you will not only have a functional asset tracking application but also a deeper understanding of how to build and maintain secure web applications in an ever-evolving digital landscape.</p>

    <p>Best of luck, and let's make this quarter a journey of learning, exploration, and mastery!</p>
</div>

</div>

<!-- Footer -->
<footer class="footer mt-5 py-3 bg-dark text-white footer-custom">
    <div class="container text-center">
        <span>&copy; 2024 UW HusKey Manager. All rights reserved.</span>
    </div>
</footer>

</body>
</html>
