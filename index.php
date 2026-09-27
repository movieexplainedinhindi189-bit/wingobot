<?php

// ==========================================
// CONFIGURATION
// ==========================================
define('BOT_TOKEN', '8841538456:AAH-dzwmSEUenzURnNaZJ67mBbhGKiRC5o8');
define('CHAT_ID', '5320999744');
define('REGISTRATION_LINK', 'https://www.jaipur91.com/#/register?invitationCode=45578190585');
define('WIN_10_IMAGE', 'https://host.hemnthapp.shop/uploads/kxQ3ZiGbPy.png');

define('DATA_FILE', __DIR__ . '/data.json');

// 91Club / Wingo 1Min API Endpoints
define('API_HISTORY', 'https://api.91club.com/api/webapi/GetNoHeaderList?typeid=1&pageno=1&pagesize=10');
define('API_CURRENT', 'https://api.91club.com/api/webapi/GetGameIssue?typeid=1');

/**
 * Sends a message to Telegram Chat ID
 */
function sendTelegramMessage($message) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $postData = [
        'chat_id' => CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

/**
 * Sends a photo to Telegram Chat ID
 */
function sendTelegramPhoto($photoUrl, $caption = "") {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendPhoto";
    $postData = [
        'chat_id' => CHAT_ID,
        'photo' => $photoUrl,
        'caption' => $caption,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

/**
 * Fetches JSON data using cURL with Browser Headers
 */
function fetchJson($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response) {
        return json_decode($response, true);
    }
    return null;
}

// Initialize default data state
$state = [
    'last_predicted_issue' => null,
    'last_prediction' => null,
    'current_level' => 1,
    'win_count' => 0
];

if (file_exists(DATA_FILE)) {
    $json = file_get_contents(DATA_FILE);
    $saved = json_decode($json, true);
    if (is_array($saved)) {
        $state = array_merge($state, $saved);
    }
}

echo "Fetching latest lottery data...\n";
$historyData = fetchJson(API_HISTORY);
$currentData = fetchJson(API_CURRENT);

if (!$historyData || !isset($historyData['data']['list']) || !$currentData || !isset($currentData['data']['issueNumber'])) {
    die("Error: Could not fetch or parse data from the API endpoints.\n");
}

$historyList = $historyData['data']['list'];
$currentIssue = $currentData['data']['issueNumber'];

// Check previous prediction result
if (!empty($state['last_predicted_issue']) && $state['last_predicted_issue'] !== $currentIssue) {
    echo "Checking result for issue: {$state['last_predicted_issue']}...\n";
    
    $resolvedIssue = null;
    foreach ($historyList as $item) {
        if ($item['issueNumber'] === $state['last_predicted_issue']) {
            $resolvedIssue = $item;
            break;
        }
    }

    if ($resolvedIssue) {
        $number = intval($resolvedIssue['number']);
        $actualResult = ($number <= 4) ? 'Small' : 'Big';

        if ($actualResult === $state['last_prediction']) {
            $state['win_count']++;
            $state['current_level'] = 1;
            
            sendTelegramMessage("🏆 WIN!");
            echo "Result: WIN!\n";

            if ($state['win_count'] >= 10) {
                sendTelegramPhoto(WIN_10_IMAGE, "🎉 10 WINS COMPLETED! 🎉\n\n📌 Create new account:\n🔗 " . REGISTRATION_LINK);
                $state['win_count'] = 0;
            }
        } else {
            $state['current_level'] *= 2;
            $state['win_count'] = 0;
            
            sendTelegramMessage("😢 LOSS!");
            echo "Result: LOSS! Level increased to {$state['current_level']}x\n";
        }

        $state['last_predicted_issue'] = null;
        $state['last_prediction'] = null;
    }
}

// Make new prediction
if ($currentIssue !== $state['last_predicted_issue']) {
    $lastResultNumber = intval($historyList[0]['number']);
    $lastResultSize = ($lastResultNumber <= 4) ? 'Small' : 'Big';
    $guess = ($lastResultSize === 'Small') ? 'Big' : 'Small';

    $shortCurrentIssue = substr($currentIssue, -4);
    
    // Message Format including Registration Link & New Account prompt
    $predictMsg = "Wingo 1Min {$shortCurrentIssue} {$state['current_level']}x {$guess} 🚀\n\n" .
                  "📌 <b>Create new account:</b>\n" .
                  "🔗 <a href='" . REGISTRATION_LINK . "'>" . REGISTRATION_LINK . "</a>";

    echo "Sending prediction for issue {$currentIssue}...\n";
    sendTelegramMessage($predictMsg);

    $state['last_predicted_issue'] = $currentIssue;
    $state['last_prediction'] = $guess;
}

file_put_contents(DATA_FILE, json_encode($state, JSON_PRETTY_PRINT));
echo "Done.\n";
?>
