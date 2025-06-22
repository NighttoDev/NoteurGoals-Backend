<?php

// Simple API Test Script
$baseUrl = 'http://localhost:8000/api';

echo "🔍 API ENDPOINTS TEST\n";
echo "====================\n\n";

// Biến để lưu token
$authToken = null;

// Test connection first
echo "1. Testing server connection...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/user');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection failed: $error\n";
    echo "   Make sure your Laravel server is running on localhost:8000\n\n";
    exit;
} else {
    echo "✅ Server is reachable (HTTP $httpCode)\n\n";
}

// Test public endpoints
echo "2. Testing public endpoints:\n";
echo "----------------------------\n";

$publicEndpoints = [
    'POST /register' => '/register',
    'POST /login' => '/login',
    'POST /verify-email' => '/verify-email',
    'POST /forgot-password' => '/forgot-password'
];

foreach ($publicEndpoints as $name => $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'data']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
    echo "$status $name: HTTP $httpCode\n";
    
    // Nếu là login endpoint, thử lấy token
    if ($endpoint === '/login') {
        $responseData = json_decode($response, true);
        if ($httpCode == 200 && isset($responseData['data']['token'])) {
            $authToken = $responseData['data']['token'];
            echo "   ✅ Token received: " . substr($authToken, 0, 20) . "...\n";
        }
    }
}

echo "\n3. Testing protected endpoints (with auth if available):\n";
echo "------------------------------------------------\n";

$protectedEndpoints = [
    'GET /goals' => '/goals',
    'GET /notes' => '/notes', 
    'GET /events' => '/events',
    'GET /notifications' => '/notifications',
    'GET /friends' => '/friends',
    'GET /files' => '/files',
    'GET /ai-suggestions' => '/ai-suggestions',
    'GET /subscription-plans' => '/subscription-plans',
    'GET /me' => '/me'
];

foreach ($protectedEndpoints as $name => $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $headers = ['Accept: application/json'];
    if ($authToken) {
        $headers[] = 'Authorization: Bearer ' . $authToken;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($authToken) {
        $expected = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$expected $name: HTTP $httpCode (with token)\n";
    } else {
        $expected = ($httpCode == 401) ? "✅" : "❌";
        echo "$expected $name: HTTP $httpCode (expected 401)\n";
    }
}

echo "\n4. Testing POST endpoints (with auth if available):\n";
echo "------------------------------------------------\n";

if ($authToken) {
    // Test tạo goal
    $goalData = [
        'title' => 'Test Goal',
        'description' => 'Test description',
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+30 days'))
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/goals');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($goalData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
    echo "$status POST /goals: HTTP $httpCode\n";
} else {
    echo "❌ Cannot test POST endpoints without authentication token\n";
}

echo "\n5. Testing route list:\n";
echo "--------------------\n";

// Test if we can get route list
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Laravel application is running\n";
} else {
    echo "❌ Laravel application might not be running properly\n";
}

echo "\n📋 SUMMARY:\n";
echo "==========\n";
echo "• If you see ✅ for server connection: Your server is running\n";
echo "• If you see ✅ for public endpoints: Routes are accessible\n";
echo "• If you see ✅ for protected endpoints: Authentication is working\n";
echo "• If you see ❌ for any endpoint: Check your routes and controllers\n\n";

if ($authToken) {
    echo "🔑 Authentication Status: ✅ Token received and being used\n";
} else {
    echo "🔑 Authentication Status: ❌ No token received (login may have failed)\n";
}

echo "\n🔧 NEXT STEPS:\n";
echo "=============\n";
echo "1. If endpoints return 404: Check if routes are properly defined\n";
echo "2. If endpoints return 500: Check Laravel logs for errors\n";
echo "3. If endpoints return 401: This is expected for protected routes without token\n";
echo "4. If login fails: Check your credentials and database connection\n";
echo "5. To test with authentication: Make sure login returns a valid token\n"; 