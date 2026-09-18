<?php

include __DIR__ . "/db.php";

header("Content-Type: application/json");

$sql = "
    SELECT
        department,
        COUNT(*) AS total_complaints,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved_complaints,
        SUM(CASE WHEN status != 'Resolved' THEN 1 ELSE 0 END) AS pending_complaints,
        SUM(CASE WHEN priority = 'High' THEN 1 ELSE 0 END) AS high_priority_complaints
    FROM complaints
    GROUP BY department
    ORDER BY total_complaints DESC
";

$result = $conn->query($sql);

$departments = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $departments[] = [
            "department" => $row["department"],
            "total" => (int)$row["total_complaints"],
            "resolved" => (int)$row["resolved_complaints"],
            "pending" => (int)$row["pending_complaints"],
            "high_priority" => (int)$row["high_priority_complaints"]
        ];

    }

}

echo json_encode([
    "success" => true,
    "departments" => $departments
]);

$conn->close();

?>