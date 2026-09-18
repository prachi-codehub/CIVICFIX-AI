<?php

header("Content-Type: application/json; charset=UTF-8");

include "db.php";

if (!isset($_GET["complaint_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Complaint ID is required."
    ]);

    exit;
}

$complaint_id = trim($_GET["complaint_id"]);

$sql = "
    SELECT complaint_id, status, department, issue_title
    FROM complaints
    WHERE complaint_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Database query failed."
    ]);

    exit;
}

$stmt->bind_param("s", $complaint_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Complaint not found."
    ]);

    $stmt->close();
    $conn->close();

    exit;
}

$complaint = $result->fetch_assoc();

$status = trim($complaint["status"]);

$message = "";

if ($status === "Submitted") {

    $message =
        "Your complaint " .
        $complaint["complaint_id"] .
        " has been successfully registered and is waiting for processing.";

}

elseif ($status === "In Progress") {

    $message =
        "Your complaint " .
        $complaint["complaint_id"] .
        " is now being handled by the " .
        $complaint["department"] .
        ".";

}

elseif ($status === "Resolved") {

    $message =
        "Your complaint " .
        $complaint["complaint_id"] .
        " has been marked as resolved by the responsible department.";

}

else {

    $message =
        "Your complaint status is currently " . $status . ".";

}

echo json_encode([

    "success" => true,

    "complaint_id" =>
        $complaint["complaint_id"],

    "status" =>
        $status,

    "department" =>
        $complaint["department"],

    "issue" =>
        $complaint["issue_title"],

    "message" =>
        $message

], JSON_PRETTY_PRINT);

$stmt->close();

$conn->close();

?>