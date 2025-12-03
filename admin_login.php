<?php
session_start();
// NOTE: We don't need config.php here since we are not connecting to the database

$username = $password = "";
$login_err = "";

// Simple Hardcoded Credentials for the project
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'password123'); // You can change this simple password

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Check if credentials match
    if($username === ADMIN_USER && $password === ADMIN_PASS){
        // Login successful! Store data in session variables
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        
        // Redirect user to the tracker page
        header("location: view_documents.php");
        exit;
    } else {
        $login_err = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTrack - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card p-4 shadow-lg">
                    <h2 class="mb-4 text-center text-danger">🔐 Admin Area Login</h2>
                    <p class="text-center">Enter your credentials to access Edit/Delete features.</p>
                    
                    <?php if(!empty($login_err)): ?>
                        <div class="alert alert-danger"><?php echo $login_err; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                        </div>    
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include 'footer.php'; ?>
</body>
</html>