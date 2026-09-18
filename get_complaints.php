<?php

include __DIR__ . "/db.php";

$sql = "SELECT * FROM complaints ORDER BY created_at DESC";

$result = $conn->query($sql);

$complaints = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }

}

header("Content-Type: application/json");

echo json_encode($complaints);

$conn->close();

?>