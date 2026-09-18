<?php

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

/* -----------------------------
   CHECK IMAGE
----------------------------- */

if (!isset($_FILES["complaintImage"])) {
    echo json_encode([
        "success" => false,
        "message" => "No image received."
    ]);
    exit;
}

$image = $_FILES["complaintImage"];

if ($image["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "success" => false,
        "message" => "Image upload failed. Error code: " . $image["error"]
    ]);
    exit;
}

/* -----------------------------
   CHECK FILE SIZE
----------------------------- */

if ($image["size"] > 5 * 1024 * 1024) {
    echo json_encode([
        "success" => false,
        "message" => "Image is larger than 5 MB."
    ]);
    exit;
}

/* -----------------------------
   CHECK IMAGE TYPE
----------------------------- */

$allowedTypes = [
    "image/jpeg",
    "image/png",
    "image/webp"
];

$fileType = mime_content_type($image["tmp_name"]);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode([
        "success" => false,
        "message" => "Only JPG, PNG and WEBP images are allowed."
    ]);
    exit;
}

/* -----------------------------
   READ GEMINI API KEY
----------------------------- */

$envFile = __DIR__ . "/.env";

if (!file_exists($envFile)) {
    echo json_encode([
        "success" => false,
        "message" => ".env file not found."
    ]);
    exit;
}

$env = file_get_contents($envFile);

preg_match(
    '/^GEMINI_API_KEY\s*=\s*(.+)$/m',
    $env,
    $matches
);

if (!isset($matches[1])) {
    echo json_encode([
        "success" => false,
        "message" => "GEMINI_API_KEY not found in .env"
    ]);
    exit;
}

$apiKey = trim(trim($matches[1]), "\"'");

/* -----------------------------
   CONVERT IMAGE TO BASE64
----------------------------- */

$imageData = base64_encode(
    file_get_contents($image["tmp_name"])
);

/* -----------------------------
   GEMINI API
----------------------------- */

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent";

$prompt = "
You are an AI assistant for CIVICFIX AI, a smart civic complaint platform.

Analyze the uploaded image and identify the visible civic issue.

Return the answer EXACTLY in this format:

Issue: <short issue name>
Category: <Water / Drainage / Road / Garbage / Streetlight / Other>
Severity: <High / Medium / Low>
Department: <responsible department>
Recommendation: <short practical recommendation>

Focus only on visible evidence in the image.
Do not invent information that cannot be seen.
";

/* -----------------------------
   REQUEST DATA
----------------------------- */

$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "inlineData" => [
                        "mimeType" => $fileType,
                        "data" => $imageData
                    ]
                ],
                [
                    "text" => $prompt
                ]
            ]
        ]
    ]
];

$jsonData = json_encode($data);

/* -----------------------------
   SEND REQUEST
----------------------------- */

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-goog-api-key: " . $apiKey
    ],

    CURLOPT_POSTFIELDS => $jsonData,

    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);

$curlError = curl_error($ch);

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);

/* -----------------------------
   CURL ERROR
----------------------------- */

if ($curlError) {
    echo json_encode([
        "success" => false,
        "message" => "Gemini connection error.",
        "curl_error" => $curlError
    ]);
    exit;
}

/* -----------------------------
   GEMINI ERROR
----------------------------- */

$responseData = json_decode(
    $response,
    true
);

if ($httpCode < 200 || $httpCode >= 300) {

    echo json_encode([
        "success" => false,
        "message" => "Gemini API error.",
        "http_code" => $httpCode,
        "api_response" => $responseData ?: $response
    ]);

    exit;
}

/* -----------------------------
   GET AI TEXT
----------------------------- */

$aiText = "";

if (
    isset($responseData["candidates"][0]["content"]["parts"][0]["text"])
) {

    $aiText =
        $responseData["candidates"][0]["content"]["parts"][0]["text"];
}

if ($aiText === "") {

    echo json_encode([
        "success" => false,
        "message" => "Gemini returned no analysis.",
        "api_response" => $responseData
    ]);

    exit;
}

/* -----------------------------
   SUCCESS
----------------------------- */

echo json_encode([
    "success" => true,
    "message" => "Image analyzed successfully.",
    "analysis" => $aiText
], JSON_PRETTY_PRINT);

?>