<?php

include __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $complaint_id = $_POST["complaint_id"];
    $status = $_POST["status"];

    $sql = "UPDATE complaints 
            SET status = ? 
            WHERE complaint_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Prepare Error: " . $conn->error;
        exit;
    }

    $stmt->bind_param(
        "ss",
        $status,
        $complaint_id
    );

    if ($stmt->execute()) {

        echo "Status updated successfully";

    } else {

        echo "Database Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {

    echo "Invalid request";
}

?>