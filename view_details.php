<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Include connection file
require_once "config.php"; 

$document = null; // Variable to hold the document details
// Check if the 'id' parameter exists in the URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    
    // Prepare a select statement using the ID
    $sql = "SELECT * FROM documents WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind the ID parameter
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameters
        $param_id = trim($_GET["id"]);
        
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            // Check if a document with that ID exists
            if (mysqli_num_rows($result) == 1) {
                // Fetch the result row as an associative array
                $document = mysqli_fetch_assoc($result);
            } else {
                // No record found with that ID
                header("location: view_documents.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
} else {
    // ID was not provided in the URL
    header("location: view_documents.php");
    exit();
}

// Close connection at the end of the script
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Details - 
    <?php echo isset($document['doc_name']) ? $document['doc_name'] : 'Loading...'; ?>
</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($document): 
                    $card_class = ($document['status'] == 'Lost') ? 'border-danger shadow-lg' : 'border-success shadow-lg';
                    $status_text = ($document['status'] == 'Lost') ? 'text-danger' : 'text-success';
                ?>
                    <div class="card <?php echo $card_class; ?>">
                        <div class="card-header bg-light text-center">
                            <h3 class="mb-0">Details for: <strong><?php echo $document['doc_name']; ?></strong></h3>
                        </div>
                        <div class="card-body">

                         <p class="card-text fs-4 text-center <?php echo $status_text; ?>">
    <strong>Status: <?php echo $document['status']; ?></strong>
</p>

<?php if (!empty($document['doc_image'])): ?>
    <div class="mb-4 text-center">
        <h5 class="text-muted">Document Image for Verification:</h5>
        <img src="<?php echo $document['doc_image']; ?>" 
             alt="Document Image" 
             class="img-fluid rounded shadow-sm" 
             style="max-height: 300px; width: auto;">
        <hr>
    </div>
<?php endif; ?>
<ul class="list-group list-group-flush mb-4"> 
    
</ul>
                            <p class="card-text fs-4 text-center <?php echo $status_text; ?>"> <strong>Status: <?php echo $document['status']; ?></strong> </p>
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Name on Document:</strong>
                                    <span><?php echo $document['owner_name']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    **Date <?php echo $document['status']; ?>/Found:**
                                    <span><?php echo $document['date_event']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    **Location:**
                                    <span><?php echo $document['location']; ?></span>
                                </li>
                            </ul>
                            
                            <div class="alert alert-info text-center mt-4 p-3">
                                <h4 class="alert-heading">📢 **Contact Information**</h4>
                                <p class="mb-0 fs-5">
                                    Please contact the reporter directly using: **<?php echo $document['contact']; ?>**
                                </p>
                            </div>

                        </div>
                        <div class="card-footer text-center">
                            <a href="view_documents.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Tracker</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include 'footer.php'; ?>
</body>
</html>