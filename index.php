<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$dataFile     = '/tmp/real_91club_state_v2.json';$robotPhoto   = "https://images.unsplash.com/photo-1485827404703-89b55fcc595e";

date_default_timezone_set('Asia/Kolkata');

function sendTelegramMessage($botToken, $chatId,$message) {
    $url  = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    $data = [
        'chat_id'                  => $chatId,
        'text'                     => $message,
        'parse_mode'               => 'Markdown',
        'disable_web_page_preview' => true
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function sendTelegramPhoto($botToken,$chatId, $photoUrl,$caption) {
    $url  = "https://api.telegram.org/bot" . $botToken . "/sendPhoto";
    $data = [
        'chat_id'    => $chatId,
        'photo'      => $photoUrl,
        'caption'    => $caption,
        'parse_mode' => 'Markdown'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// Fetch real history directly from 91 Club API
function getReal91ClubHistory() {
    $endpoints = [
        "https://api.91club.com/api/webapi/GetNoHeaderWingoList",
        "https://api.91clubadmin.com/api/webapi/GetNoHeaderWingoList"
    ];

    $postData = json_encode([
        "typeId"   => 1,
        "pageNo"   => 1,
        "pageSize" => 5,
        "language" => 0
    ]);

    foreach ($endpoints as$url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json;charset=UTF-8",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Referer: https://kanpur91.com/"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 &&$response) {
            $resData = json_decode($response, true);
            if (isset($resData['data']['list']) && count($resData['data']['list']) > 0) {
                return $resData['data']['list'];
            }
        }
    }
    return null;
}

// Generate prediction based on period
function generatePrediction($p) {
    $last3   = (int)substr($p, -3);
    $num     = ($last3 * 3 + 5) % 10;
    $size    = ($num >= 5) ? "BIG 📈" : "SMALL 📉";
    $rawSize = ($num >= 5) ? "BIG" : "SMALL";
    $color   = ($num == 0 || $num == 5) ? "VIOLET 🟣" : (($num % 2 === 0) ? "RED 🔴" : "GREEN 🟢");
    
    return [
        'number'  => $num,
        'size'    => $size,
        'rawSize' => $rawSize,
        'color'   => $color
    ];
}

// 1. Get real history data
$historyList = getReal91ClubHistory();
if (!$historyList) {
    echo "Error: Could not fetch data from 91 Club API.";
    exit();
}

$latestItem     =$historyList[0];
$lastPeriod     = (string)$latestItem['issueNumber']; // Real finished period from API
$lastActualNum  = (int)$latestItem['number'];         // Real winning number from API
$lastActualSize = ($lastActualNum >= 5) ? "BIG" : "SMALL";

// Current active period is exactly the next number of the latest issue
$currentPeriod  = (string)((int)$lastPeriod + 1);

// Load state to prevent duplicate posting for the same period
$savedState = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Already processed for period: " . $currentPeriod;
    exit();
}

$currentPred  = generatePrediction($currentPeriod);
$currentLevel =$savedState['level'] ?? 1;

// 2. Verify previous prediction with real API result
if (isset($savedState['period']) &&$savedState['period'] === $lastPeriod) {$prevPredSize = $savedState['rawSize'];$isSizeWin    = ($prevPredSize ===$lastActualSize);
    
    if ($isSizeWin) {$resultMsg  = "🤖 *[ROBOT AI VERIFIER]*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🎉 *RESULT: DIRECT WIN CONFIRMED* 🎉\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
        $resultMsg .= "📊 *REAL OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
        $resultMsg .= "💎 *STATUS:* *PROFIT EXTRACTED ✅*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "⚡ *Robot AI: Target Achieved Successfully.*";
        $currentLevel = 1;     } else {$nextLvl    = ($currentLevel < 5) ?$currentLevel + 1 : 1;
        $resultMsg  = "🤖 *[ROBOT AI VERIFIER]*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "💔 *RESULT: PERIOD LOSS* 💔\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
        $resultMsg .= "📊 *REAL OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
        $resultMsg .= "⚠️ *PROTOCOL:* *EXECUTE RECOVERY LEVEL " . $nextLvl . " 🔴*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🤖 *ROBOT ADVICE:* Loss recover karne ke liye 5-Level fund plan use karein!";
        $currentLevel =$nextLvl;
    }

    sendTelegramMessage($botToken, $chatId,$resultMsg);
    sleep(1);
}

// Save current state
$newState = [
    'last_sent_period' => $currentPeriod,
    'period'           => $currentPeriod,
    'rawSize'          => $currentPred['rawSize'],
    'level'            => $currentLevel
];
file_put_contents($dataFile, json_encode($newState));

// 3. Send New Signal with Robot Photo & Hindi Notice
$signalCaption  = "⚡ *[ROBOT HACK SIGNAL ENGINE]* ⚡\n\n";
$signalCaption .= "🚀 *LIVE SIGNAL* 🚀\n";
$signalCaption .= "🆔 *PERIOD ID:* `" . $currentPeriod . "`\n";
$signalCaption .= "📊 *PREDICTION:* *" . $currentPred['size'] . "*\n";
$signalCaption .= "🎨 *COLOR CODE:* *" . $currentPred['color'] . "*\n";
$signalCaption .= "🔢 *LUCKY DIGIT:* `" . $currentPred['number'] . "`\n";
$signalCaption .= "💰 *FUND SYSTEM:* *Level " . $currentLevel . " (5-Level Mandatory)*\n\n";

$signalCaption .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$signalCaption .= "🔥 *ZAROORI SOOCHNA (IMPORTANT):*\n";
$signalCaption .= "**Agar daily ka 5-10 hazaar kamana hai toh naya ID banalo**, tabhi 100% win aayega purane account me error aa sakta hai!\n\n";
$signalCaption .= "🔗 *NEW ID REGISTER LINK:* \n";
$signalCaption .= "👉 [Click Here To Register New ID](" . $regLink . ")\n";
$signalCaption .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

sendTelegramPhoto($botToken,$chatId, $robotPhoto,$signalCaption);

echo "Success! Real API synced. Period: " . $currentPeriod;
?>
