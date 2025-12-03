<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config.php";

// Define variables and initialize with empty values
$doc_name = $owner_name = $status = $date_event = $location = $contact = $doc_image = "";
$message = ""; // To show success or error messages

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // 1. Collect and sanitize input data
    $doc_name = trim($_POST["doc_name"]);
    $owner_name = trim($_POST["owner_name"]);
    $status = trim($_POST["status"]);
    $date_event = trim($_POST["date_event"]);
    $location = trim($_POST["location"]);
    $contact = trim($_POST["contact"]);

    // --- Image Upload Handling ---
    $param_doc_image = NULL; // Default value if no file is uploaded
    if (isset($_FILES["doc_image"]) && $_FILES["doc_image"]["error"] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png");
        $filename = $_FILES["doc_image"]["name"];
        $filetype = $_FILES["doc_image"]["type"];
        $filesize = $_FILES["doc_image"]["size"];

        // Verify file extension and size
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $message = "Error: Please select a valid file format (JPG, JPEG, or PNG).";
            goto end_of_post; // Jumps to the connection close, bypassing insert
        }
        if ($filesize > 5 * 1024 * 1024) {
             $message = "Error: File size must be less than 5MB.";
             goto end_of_post;
        }

        // Hash the filename to make it unique and secure
        $new_filename = time() . "_" . uniqid() . "." . $ext;
        $target_file = "uploads/" . $new_filename;

        // Check if file moves successfully
        if (move_uploaded_file($_FILES["doc_image"]["tmp_name"], $target_file)) {
            $param_doc_image = $target_file; // Save the path to the database
        } else {
            $message = "Error: File upload failed, server permission issue.";
            goto end_of_post;
        }
    }
    // --- End Image Upload Handling ---

    // 2. Prepare an INSERT statement (MUST be AFTER file handling is complete)
    $sql = "INSERT INTO documents (doc_name, owner_name, status, date_event, location, contact, doc_image) VALUES (?, ?, ?, ?, ?, ?, ?)";

    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sssssss", $param_doc_name, $param_owner_name, $param_status, $param_date_event, $param_location, $param_contact, $param_doc_image);

        // Set parameters
        $param_doc_name = $doc_name;
        $param_owner_name = $owner_name;
        $param_status = $status;
        $param_date_event = $date_event;
        $param_location = $location;
        $param_contact = $contact;
        // $param_doc_image is already set in the file handling block

        // 3. Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $message = "Report submitted successfully! Thank you.";
            // Clear inputs after successful submission
            $doc_name = $owner_name = $status = $date_event = $location = $contact = "";
        } else{
            $message = "ERROR: Could not submit report. " . mysqli_error($conn);
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    end_of_post: // Label for GOTO statement

    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTrack - Report Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <h2 class="mb-4 text-center text-primary">📑 Report Lost or Found Document</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-success text-center"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
                    
                    <div class="mb-3">
                        <label for="doc_name" class="form-label fw-bold">Document Name (e.g., Citizenship, Passport)</label>
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
                                <input class="form-check-input" type="radio" name="status" id="statusLost" value="Lost" required>
                                <label class="form-check-label" for="statusLost">Lost 😔</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusFound" value="Found" required>
                                <label class="form-check-label" for="statusFound">Found 🎉</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="date_event" class="form-label fw-bold">Date Lost or Found</label>
                        <input type="date" name="date_event" class="form-control" id="date_event" required>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label fw-bold">Location (Where it was lost/found)</label>
                        <input type="text" name="location" class="form-control" id="location" placeholder="e.g., Koteshwor Chowk, TU Gate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact" class="form-label fw-bold">Reporter Contact Information (Phone/Email)</label>
                        <input type="text" name="contact" class="form-control" id="contact" required>
                    </div>
                    <div class="mb-3">
    <label for="doc_image" class="form-label fw-bold">Upload Document Image (Optional)</label>
    <input type="file" name="doc_image" class="form-control" id="doc_image" accept="image/*">
    <div class="form-text">Max 5MB. Accepts JPEG, PNG.</div>
</div>

                    <button type="submit" class="btn btn-primary btn-lg mt-3">Submit Report</button>
                </form>
                </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include 'footer.php'; ?>
</body>
</html>