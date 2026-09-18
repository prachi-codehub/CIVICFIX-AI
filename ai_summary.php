<?php

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit;
}

$issue = trim($_POST["issue"] ?? "");
$category = trim($_POST["category"] ?? "");
$location = trim($_POST["location"] ?? "");
$description = trim($_POST["description"] ?? "");
$department = trim($_POST["department"] ?? "");
$priority = trim($_POST["priority"] ?? "");
$status = trim($_POST["status"] ?? "");

if (
    $issue === "" ||
    $category === "" ||
    $location === "" ||
    $description === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Complaint information is incomplete."
    ]);
    exit;
}

/* Load Gemini API key */
$envFile = __DIR__ . "/.env";

if (!file_exists($envFile)) {
    echo json_encode([
        "success" => false,
        "message" => ".env file not found."
    ]);
    exit;
}

$env = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$apiKey = "";

foreach ($env as $line) {

    $line = trim($line);

    if (strpos($line, "GEMINI_API_KEY=") === 0) {
        $apiKey = trim(substr($line, strlen("GEMINI_API_KEY=")));
        break;
    }
}

if ($apiKey === "") {
    echo json_encode([
        "success" => false,
        "message" => "Gemini API key not found."
    ]);
    exit;
}

/* AI prompt */

$prompt = "
You are CIVICFIX AI, an AI assistant for a smart civic complaint platform.

Create a short professional summary of this civic complaint for a municipal authority.

Complaint Information:

Issue: $issue
Category: $category
Location: $location
Description: $description
Department: $department
Priority: $priority
Status: $status

Rules:
1. Write only 1 or 2 short sentences.
2. Mention the main civic problem.
3. Mention the location.
4. Mention urgency if priority is High.
5. Mention the responsible department when available.
6. Do not invent information.
7. Use simple professional English.

Return only the summary text.
";

/* Gemini API */

$url =
"https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent";

$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => $prompt
                ]
            ]
        ]
    ]
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-goog-api-key: " . $apiKey
]);

curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($data)
);

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($response === false) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to connect to Gemini."
    ]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode !== 200) {

    echo json_encode([
        "success" => false,
        "message" => "Gemini API error.",
        "details" => $result
    ]);

    exit;
}

$summary =
    $result["candidates"][0]["content"]["parts"][0]["text"]
    ?? "";

$summary = trim($summary);

if ($summary === "") {

    echo json_encode([
        "success" => false,
        "message" => "AI did not generate a summary."
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "summary" => $summary
]);

?>