<?php

header("Content-Type: application/json; charset=UTF-8");

$envFile = __DIR__ . "/.env";

if (!file_exists($envFile)) {

    echo json_encode([
        "success" => false,
        "message" => ".env file not found"
    ]);

    exit;
}

$env = file_get_contents($envFile);

preg_match('/^GEMINI_API_KEY\s*=\s*(.+)$/m', $env, $matches);

if (!isset($matches[1])) {

    echo json_encode([
        "success" => false,
        "message" => "GEMINI_API_KEY not found in .env"
    ]);

    exit;
}

$apiKey = trim(trim($matches[1]), "\"'");

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent";

$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Reply with exactly: CIVICFIX AI TEST OK"
                ]
            ]
        ]
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-goog-api-key: " . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);

$curlError = curl_error($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo json_encode([
    "http_code" => $httpCode,
    "curl_error" => $curlError,
    "response" => json_decode($response, true) ?: $response
], JSON_PRETTY_PRINT);

?>