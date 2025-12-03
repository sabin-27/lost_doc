<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Include the database connection file
require_once "config.php"; 

// Check if the user is NOT logged in. If not, redirect them to the login page.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

// ... the rest of the PHP logic ($doc_name = ... and the IF/ELSE blocks)

// ... rest of the code

// ... rest of the code

// Define variables
$doc_name = $owner_name = $status = $date_event = $location = $contact = "";
$message = "";

// --- 1. PROCESSING THE FORM SUBMISSION (UPDATE) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Get the ID from the hidden input field
    $id = $_POST["id"];

    // Collect and sanitize input data
    $doc_name = trim($_POST["doc_name"]);
    $owner_name = trim($_POST["owner_name"]);
    $status = trim($_POST["status"]);
    $date_event = trim($_POST["date_event"]);
    $location = trim($_POST["location"]);
    $contact = trim($_POST["contact"]);
    
    // Prepare an UPDATE statement
    $sql = "UPDATE documents SET doc_name=?, owner_name=?, status=?, date_event=?, location=?, contact=? WHERE id=?";
         
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement
        mysqli_stmt_bind_param($stmt, "ssssssi", $param_doc_name, $param_owner_name, $param_status, $param_date_event, $param_location, $param_contact, $param_id);
        
        // Set parameters
        $param_doc_name = $doc_name;
        $param_owner_name = $owner_name;
        $param_status = $status;
        $param_date_event = $date_event;
        $param_location = $location;
        $param_contact = $contact;
        $param_id = $id;

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $message = "Record updated successfully! Redirecting...";
            // Redirect after successful update (gives time to see success message)
            header("refresh:3; url=view_documents.php");
        } else{
            $message = "ERROR: Could not update record. " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
} 
// --- 2. FETCHING EXISTING DATA (READ) ---
else { 
    // Check if ID parameter is valid
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        $id = trim($_GET["id"]);
        
        $sql = "SELECT * FROM documents WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_assoc($result);
                    // Set variables to pre-fill the form fields
                    $doc_name = $row["doc_name"];
                    $owner_name = $row["owner_name"];
                    $status = $row["status"];
                    $date_event = $row["date_event"];
                    $location = $row["location"];
                    $contact = $row["contact"];
                } else{
                    header("location: view_documents.php");
                    exit();
                }
            } else{
                echo "Oops! Something went wrong.";
            }
            mysqli_stmt_close($stmt);
        }
    } else{
        header("location: view_documents.php");
        exit();
    }
}
// Close connection here, after all processing is done
mysqli_close($conn); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTrack - Edit Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4 text-center text-warning">📝 Edit Document Report (ID: <?php echo $id; ?>)</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success text-center"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post" class="card p-4 shadow-sm">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="mb-3">
                <label for="doc_name" class="form-label fw-bold">Document Name</label>
                <input type="text" name="doc_name" class="form-control" id="doc_name" value="<?php echo htmlspecialchars($doc_name); ?>" required>
            </div>

            <div class="mb-3">
                <label for="owner_name" class="form-label fw-bold">Name on the Document</label>
                <input type="text" name="owner_name" class="form-control" id="owner_name" value="<?php echo htmlspecialchars($owner_name); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="statusLost" value="Lost" <?php echo ($status == 'Lost') ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="statusLost">Lost 😔</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="statusFound" value="Found" <?php echo ($status == 'Found') ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="statusFound">Found 🎉</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="date_event" class="form-label fw-bold">Date Lost or Found</label>
                <input type="date" name="date_event" class="form-control" id="date_event" value="<?php echo htmlspecialchars($date_event); ?>" required>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label fw-bold">Location</label>
                <input type="text" name="location" class="form-control" id="location" value="<?php echo htmlspecialchars($location); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="contact" class="form-label fw-bold">Reporter Contact Information</label>
                <input type="text" name="contact" class="form-control" id="contact" value="<?php echo htmlspecialchars($contact); ?>" required>
            </div>

            <button type="submit" class="btn btn-warning btn-lg mt-3">Update Report</button>
            <a href="view_documents.php" class="btn btn-secondary mt-2">Cancel</a>

        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include 'footer.php'; ?>
</body>
</html>