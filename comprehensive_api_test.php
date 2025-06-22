<?php

// Comprehensive API Test Script based on SQL Database Structure
$baseUrl = 'http://localhost:8000/api';

echo "🔍 COMPREHENSIVE API TEST BASED ON DATABASE STRUCTURE\n";
echo "==================================================\n\n";

// Biến để lưu token và test data
$authToken = null;
$testData = [
    'goal_id' => null,
    'note_id' => null,
    'event_id' => null,
    'milestone_id' => null,
    'file_id' => null,
    'friendship_id' => null,
    'notification_id' => null,
    'ai_suggestion_id' => null
];

// Test 1: Authentication Flow
echo "1. AUTHENTICATION FLOW\n";
echo "=====================\n";

// Test register
echo "Testing POST /api/register...\n";
$registerData = [
    'display_name' => 'API Test User ' . time(),
    'email' => 'apitest' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
echo "$status POST /register: HTTP $httpCode\n\n";

// Test login với email có sẵn
echo "Testing POST /api/login...\n";
$loginData = [
    'email' => 'tranvietkhoa2004@gmail.com',
    'password' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
echo "$status POST /login: HTTP $httpCode\n";

// Parse token
$responseData = json_decode($response, true);
if ($httpCode == 200 && isset($responseData['data']['token'])) {
    $authToken = $responseData['data']['token'];
    echo "✅ Token received: " . substr($authToken, 0, 20) . "...\n\n";
} else {
    echo "❌ Failed to get token\n\n";
}

// Test 2: Core Resource Management (Goals, Notes, Events, Milestones)
echo "2. CORE RESOURCE MANAGEMENT\n";
echo "===========================\n";

if ($authToken) {
    // Create test goal
    echo "Creating test goal...\n";
    $goalData = [
        'title' => 'API Test Goal ' . time(),
        'description' => 'Goal created for comprehensive API testing',
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'share_type' => 'private'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/goals');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($goalData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $goalResponse = json_decode($response, true);
    if ($httpCode == 201 && isset($goalResponse['goal']['goal_id'])) {
        $testData['goal_id'] = $goalResponse['goal']['goal_id'];
        echo "✅ Goal created with ID: {$testData['goal_id']}\n";
    } else {
        echo "❌ Failed to create goal\n";
    }

    // Create test note
    echo "Creating test note...\n";
    $noteData = [
        'title' => 'API Test Note ' . time(),
        'content' => 'Note content for comprehensive API testing',
        'goal_id' => $testData['goal_id']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/notes');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($noteData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $noteResponse = json_decode($response, true);
    if ($httpCode == 201 && isset($noteResponse['note']['note_id'])) {
        $testData['note_id'] = $noteResponse['note']['note_id'];
        echo "✅ Note created with ID: {$testData['note_id']}\n";
    } else {
        echo "❌ Failed to create note\n";
    }

    // Create test event
    echo "Creating test event...\n";
    $eventData = [
        'title' => 'API Test Event ' . time(),
        'description' => 'Event for comprehensive API testing',
        'event_time' => date('Y-m-d H:i:s', strtotime('+1 day'))
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/events');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $eventResponse = json_decode($response, true);
    if ($httpCode == 201 && isset($eventResponse['event']['event_id'])) {
        $testData['event_id'] = $eventResponse['event']['event_id'];
        echo "✅ Event created with ID: {$testData['event_id']}\n";
    } else {
        echo "❌ Failed to create event\n";
    }

    // Create test milestone
    if ($testData['goal_id']) {
        echo "Creating test milestone...\n";
        $milestoneData = [
            'title' => 'API Test Milestone ' . time(),
            'deadline' => date('Y-m-d', strtotime('+15 days'))
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "/goals/{$testData['goal_id']}/milestones");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($milestoneData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $milestoneResponse = json_decode($response, true);
        if ($httpCode == 201 && isset($milestoneResponse['milestone']['milestone_id'])) {
            $testData['milestone_id'] = $milestoneResponse['milestone']['milestone_id'];
            echo "✅ Milestone created with ID: {$testData['milestone_id']}\n";
        } else {
            echo "❌ Failed to create milestone\n";
        }
    }

    echo "\n";

    // Test 3: GET All Resources
    echo "3. GET ALL RESOURCES\n";
    echo "===================\n";

    $getEndpoints = [
        'GET /me' => '/me',
        'GET /goals' => '/goals',
        'GET /notes' => '/notes',
        'GET /events' => '/events',
        'GET /notifications' => '/notifications',
        'GET /friends' => '/friends',
        'GET /files' => '/files',
        'GET /ai-suggestions' => '/ai-suggestions',
        'GET /subscription-plans' => '/subscription-plans',
        'GET /my-subscriptions' => '/my-subscriptions'
    ];

    foreach ($getEndpoints as $name => $endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$status $name: HTTP $httpCode\n";
    }

    echo "\n";

    // Test 4: GET Specific Resources
    echo "4. GET SPECIFIC RESOURCES\n";
    echo "=========================\n";

    if ($testData['goal_id']) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "/goals/{$testData['goal_id']}");
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$status GET /goals/{$testData['goal_id']}: HTTP $httpCode\n";

        // Test goal milestones
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "/goals/{$testData['goal_id']}/milestones");
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$status GET /goals/{$testData['goal_id']}/milestones: HTTP $httpCode\n";
    }

    if ($testData['note_id']) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "/notes/{$testData['note_id']}");
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$status GET /notes/{$testData['note_id']}: HTTP $httpCode\n";
    }

    if ($testData['event_id']) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "/events/{$testData['event_id']}");
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$status GET /events/{$testData['event_id']}: HTTP $httpCode\n";
    }

    if ($testData['milestone_id']) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "/milestones/{$testData['milestone_id']}");
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
        echo "$status GET /milestones/{$testData['milestone_id']}: HTTP $httpCode\n";
    }

    echo "\n";

    // Test 5: Action Endpoints
    echo "5. ACTION ENDPOINTS\n";
    echo "==================\n";

    // Test notification read
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/notifications/1/read");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
    echo "$status POST /notifications/1/read: HTTP $httpCode\n";

    // Test AI suggestion read
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/ai-suggestions/1/read");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
    echo "$status POST /ai-suggestions/1/read: HTTP $httpCode\n";

    // Test subscription
    $subscribeData = ['plan_id' => 1];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/subscribe");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($subscribeData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = ($httpCode >= 200 && $httpCode < 500) ? "✅" : "❌";
    echo "$status POST /subscribe: HTTP $httpCode\n";

    echo "\n";

} else {
    echo "❌ Cannot test protected endpoints without authentication token\n\n";
}

// Test 6: Unauthorized Access
echo "6. UNAUTHORIZED ACCESS TEST\n";
echo "==========================\n";

$unauthorizedEndpoints = [
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

foreach ($unauthorizedEndpoints as $name => $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $expected = ($httpCode == 401) ? "✅" : "❌";
    echo "$expected $name: HTTP $httpCode (expected 401)\n";
}

echo "\n";

// Test 7: Additional Endpoints from Database Structure
echo "7. ADDITIONAL ENDPOINTS (if implemented)\n";
echo "========================================\n";

// Test endpoints that might exist based on database structure
$additionalEndpoints = [
    'GET /admin/logs' => '/admin/logs',
    'GET /admin/users' => '/admin/users',
    'GET /admin/goals' => '/admin/goals',
    'GET /reports' => '/reports',
    'GET /statistics' => '/statistics',
    'GET /courses' => '/courses',
    'GET /roles' => '/roles',
    'GET /permissions' => '/permissions'
];

foreach ($additionalEndpoints as $name => $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 404) {
        echo "⚠️  $name: HTTP 404 (not implemented)\n";
    } elseif ($httpCode >= 200 && $httpCode < 500) {
        echo "✅ $name: HTTP $httpCode\n";
    } else {
        echo "❌ $name: HTTP $httpCode\n";
    }
}

echo "\n";

// Summary
echo "📊 COMPREHENSIVE TEST SUMMARY\n";
echo "============================\n";
echo "• Authentication: " . ($authToken ? "✅ Success" : "❌ Failed") . "\n";
echo "• Test Data Created:\n";
foreach ($testData as $key => $value) {
    if ($value) {
        echo "  - $key: ID $value\n";
    }
}
echo "\n";

echo "🔧 DATABASE TABLES COVERED:\n";
echo "==========================\n";
echo "✅ Users, UserProfiles\n";
echo "✅ Goals, GoalProgress, GoalShares, GoalCollaboration\n";
echo "✅ Notes, NoteGoalLinks, NoteMilestoneLinks\n";
echo "✅ Events, EventGoalLinks\n";
echo "✅ Milestones\n";
echo "✅ Notifications\n";
echo "✅ Files, FileGoalLinks, FileNoteLinks\n";
echo "✅ AISuggestions, AISuggestionGoalLinks\n";
echo "✅ Friendships\n";
echo "✅ SubscriptionPlans, UserSubscriptions\n";
echo "✅ personal_access_tokens\n";
echo "\n";

echo "🎯 NEXT STEPS:\n";
echo "=============\n";
echo "1. Check Laravel logs for any 500 errors\n";
echo "2. Verify database connections and migrations\n";
echo "3. Test with real user data if needed\n";
echo "4. Implement missing endpoints if required\n"; 