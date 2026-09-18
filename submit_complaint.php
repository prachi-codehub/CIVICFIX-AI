<?php

include "db.php";

// ==========================================
// CHECK REQUEST
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo "Invalid request.";
    exit;

}


// ==========================================
// GET FORM DATA
// ==========================================

$complaint_id = trim($_POST["complaint_id"] ?? "");
$issue_title = trim($_POST["issue_title"] ?? "");
$category = trim($_POST["category"] ?? "");
$location = trim($_POST["location"] ?? "");
$description = trim($_POST["description"] ?? "");
$department = trim($_POST["department"] ?? "");
$priority = trim($_POST["priority"] ?? "");


// ==========================================
// BASIC VALIDATION
// ==========================================

if (
    $complaint_id === "" ||
    $issue_title === "" ||
    $category === "" ||
    $location === "" ||
    $description === "" ||
    $department === "" ||
    $priority === ""
) {

    echo "Please fill all required complaint details.";
    exit;

}


// ==========================================
// IMAGE VARIABLES
// ==========================================

$image_status = "No image uploaded";
$image_path = null;


// ==========================================
// IMAGE UPLOAD
// ==========================================

if (
    isset($_FILES["complaintImage"]) &&
    $_FILES["complaintImage"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    $image = $_FILES["complaintImage"];


    // Check upload error

    if ($image["error"] !== UPLOAD_ERR_OK) {

        echo "Image upload error. Please try again.";
        exit;

    }


    // Maximum 5 MB

    if ($image["size"] > 5 * 1024 * 1024) {

        echo "Image is larger than 5 MB.";
        exit;

    }


    // Detect actual MIME type

    $fileType = mime_content_type(
        $image["tmp_name"]
    );


    $allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];


    if (!in_array($fileType, $allowedTypes, true)) {

        echo "Only JPG, PNG and WEBP images are allowed.";
        exit;

    }


    // ==========================================
    // FILE EXTENSION
    // ==========================================

    if ($fileType === "image/jpeg") {

        $extension = "jpg";

    }

    elseif ($fileType === "image/png") {

        $extension = "png";

    }

    else {

        $extension = "webp";

    }


    // ==========================================
    // CREATE SAFE FILE NAME
    // ==========================================

    $safeComplaintId =
        preg_replace(
            "/[^A-Za-z0-9_-]/",
            "",
            $complaint_id
        );


    $fileName =
        $safeComplaintId .
        "_" .
        time() .
        "_" .
        uniqid() .
        "." .
        $extension;


    // ==========================================
    // UPLOAD DIRECTORY
    // ==========================================

    $uploadDirectory =
        __DIR__ . DIRECTORY_SEPARATOR . "uploads";


    // Create folder if it doesn't exist

    if (!is_dir($uploadDirectory)) {

        if (!mkdir(
            $uploadDirectory,
            0755,
            true
        )) {

            echo "Unable to create uploads folder.";
            exit;

        }

    }


    // ==========================================
    // TARGET FILE
    // ==========================================

    $targetFile =
        $uploadDirectory .
        DIRECTORY_SEPARATOR .
        $fileName;


    // ==========================================
    // MOVE IMAGE
    // ==========================================

    if (
        !move_uploaded_file(
            $image["tmp_name"],
            $targetFile
        )
    ) {

        echo "Failed to save uploaded image.";
        exit;

    }


    // ==========================================
    // DATABASE IMAGE PATH
    // ==========================================

    $image_path =
        "uploads/" .
        $fileName;


    $image_status =
        "Image uploaded successfully";

}


// ==========================================
// DUPLICATE CHECK
// ==========================================
//
// A complaint is considered a possible duplicate
// only when:
//
// 1. Category is the same
// 2. Location is the same
// 3. Issue title OR description is similar
// 4. Existing complaint is not resolved
//
// ==========================================

$duplicate_sql = "

    SELECT
        complaint_id,
        issue_title,
        location,
        description

    FROM complaints

    WHERE category = ?

    AND status != 'Resolved'

    AND LOWER(TRIM(location))
        = LOWER(TRIM(?))

    AND (

        LOWER(issue_title)
        LIKE LOWER(?)

        OR

        LOWER(description)
        LIKE LOWER(?)

    )

    LIMIT 1

";


$duplicate_stmt =
    $conn->prepare(
        $duplicate_sql
    );


if (!$duplicate_stmt) {

    // Remove uploaded image if database
    // duplicate-check preparation fails

    if (
        $image_path !== null &&
        file_exists(
            __DIR__ . "/" . $image_path
        )
    ) {

        unlink(
            __DIR__ . "/" . $image_path
        );

    }


    echo "Duplicate Check Error: " .
         $conn->error;

    $conn->close();

    exit;

}


// ==========================================
// SEARCH VALUES
// ==========================================

$issue_search =
    "%" .
    $issue_title .
    "%";


$description_search =
    "%" .
    $description .
    "%";


// ==========================================
// BIND PARAMETERS
// ==========================================

$duplicate_stmt->bind_param(
    "ssss",
    $category,
    $location,
    $issue_search,
    $description_search
);


// ==========================================
// EXECUTE DUPLICATE CHECK
// ==========================================

$duplicate_stmt->execute();


$duplicate_result =
    $duplicate_stmt->get_result();


// ==========================================
// DUPLICATE FOUND
// ==========================================

if ($duplicate_result->num_rows > 0) {

    $duplicate =
        $duplicate_result->fetch_assoc();


    // Delete uploaded image because
    // complaint will not be created

    if (
        $image_path !== null &&
        file_exists(
            __DIR__ . "/" . $image_path
        )
    ) {

        unlink(
            __DIR__ . "/" . $image_path
        );

    }


    echo
        "Possible duplicate complaint found. " .
        "Existing Complaint ID: " .
        $duplicate["complaint_id"];


    $duplicate_stmt->close();

    $conn->close();

    exit;

}


$duplicate_stmt->close();


// ==========================================
// INSERT COMPLAINT
// ==========================================

$sql = "

    INSERT INTO complaints

    (
        complaint_id,
        issue_title,
        category,
        location,
        description,
        department,
        priority,
        image_status,
        image_path,
        status
    )

    VALUES

    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        'Submitted'
    )

";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    // Remove image if SQL preparation fails

    if (
        $image_path !== null &&
        file_exists(
            __DIR__ . "/" . $image_path
        )
    ) {

        unlink(
            __DIR__ . "/" . $image_path
        );

    }


    echo
        "Prepare Error: " .
        $conn->error;

    $conn->close();

    exit;

}


// ==========================================
// BIND VALUES
// ==========================================

$stmt->bind_param(

    "sssssssss",

    $complaint_id,
    $issue_title,
    $category,
    $location,
    $description,
    $department,
    $priority,
    $image_status,
    $image_path

);


// ==========================================
// INSERT
// ==========================================

if ($stmt->execute()) {

    echo
        "Complaint submitted successfully!";

}

else {

    // Remove image if database insertion fails

    if (
        $image_path !== null &&
        file_exists(
            __DIR__ . "/" . $image_path
        )
    ) {

        unlink(
            __DIR__ . "/" . $image_path
        );

    }


    echo
        "Database Error: " .
        $stmt->error;

}


// ==========================================
// CLOSE CONNECTION
// ==========================================

$stmt->close();

$conn->close();

?>