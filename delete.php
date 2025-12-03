<?php
// PHP Error Reporting (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();
// Include the database connection
require_once "config.php";
// Check if the user is NOT logged in. If not, redirect them to the login page.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;

}

// ... rest of the code

// Check if the 'id' parameter exists and is valid
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    
    // Prepare a delete statement
    $sql = "DELETE FROM documents WHERE id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameter
        $param_id = trim($_GET["id"]);
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Successful deletion. Redirect back to the main tracker page.
            header("location: view_documents.php");
            exit();
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
     
    // Close statement
    mysqli_stmt_close($stmt);
    
} else {
    // ID was not passed in the URL, redirect back.
    header("location: view_documents.php");
    exit();
}

// Close connection
mysqli_close($conn);
?>