<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$dataFile     = "bot_state.json";

// Set Timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Telegram Send Message Function
function sendTelegramMessage($botToken, $chatId,$message) {
    $telegramUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    $postParams  = [
        'chat_id'                  => $chatId,
        'text'                     => $message,
        'parse_mode'               => 'Markdown',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init($telegramUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postParams);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// ==========================================
// 1. FETCH LIVE 91 CLUB DATA
// ==========================================
function get91ClubData() {
    $url = "https://api.91club.com/api/webapi/GetNoHeaderWingoList";
    $postData = json_encode([
        "typeId" => 1,
        "pageNo" => 1,
        "pageSize" => 10,
        "language" => 0
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json;charset=UTF-8",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 &&$response) {
        $resData = json_decode($response, true);
        if (isset($resData['data']['list'][0])) {
            return $resData['data']['list'];
        }
    }
    return null;
}

$listData = get91ClubData();

if ($listData) {
    $lastFinishedItem =$listData[0];
    $lastPeriod       = (string)$lastFinishedItem['issueNumber'];
    $lastNumber       = (int)$lastFinishedItem['number'];
    $lastActualSize   = ($lastNumber >= 5) ? "BIG" : "SMALL";
    
    $currentPeriod    = (string)((int)$lastPeriod + 1);
} else {
    $todayDate    = date('Ymd');$hours        = (int)date('H');
    $minutes      = (int)date('i');$totalMinutes = ($hours * 60) +$minutes;
    $baseOffset   =$totalMinutes - 329;
    
    $currentPeriod  =$todayDate . "10001" . sprintf("%04d", $baseOffset);$lastPeriod     = (string)((int)$currentPeriod - 1);$lastActualSize = null;
}

// Prediction Algorithm
function generatePrediction($p) {
    $last3   = (int)substr($p, -3);
    $num     = ($last3 * 7 + 3) % 10;
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

// Load Previous Bot State
$savedState = [];
if (file_exists($dataFile)) {
    $savedState = json_decode(file_get_contents($dataFile), true) ?? [];
}

// Stop sending multiple signals in same minute
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Wait! Signal for period " . $currentPeriod . " already sent.";
    exit();
}

$currentPred = generatePrediction($currentPeriod);

// ==========================================
// 2. SEPARATE RESULT MESSAGE (WIN / LOSS)
// ==========================================
$levelText = "Level 1";
$isLoss    = false;

if (isset($savedState['period']) &&$savedState['period'] === $lastPeriod) {$prevPredSize     = $savedState['rawSize'];$actualResultSize = $lastActualSize ?? generatePrediction($lastPeriod)['rawSize'];
    
    if ($prevPredSize === $actualResultSize) {$resultMessage  = "🎉 *CONGRATULATIONS! DIRECT WIN* 🎉\n\n";
        $resultMessage .= "🆔 *Period:* `" . $lastPeriod . "`\n";
        $resultMessage .= "📊 *Result:* *" . $actualResultSize . "*\n";
        $resultMessage .= "💎 *Status:* *PROFIT DONE ✅*\n\n";
        $resultMessage .= "⚡ *Hack Server Working 100% Accurately!*";
        $levelText       = "Level 1 (Reset)";
    } else {
        $resultMessage  = "💔 *SORRY! PERIOD LOSS* 💔\n\n";
        $resultMessage .= "🆔 *Period:* `" . $lastPeriod . "`\n";
        $resultMessage .= "📊 *Result:* *" . $actualResultSize . "*\n";
        $resultMessage .= "⚠️ *Status:* *PREPARE FOR RECOVERY 🔴*\n\n";
        $resultMessage .= "📌 *Don't worry, 3-Level fund strategy will recover full loss!*";
        
        $prevLevel =$savedState['level'] ?? 1;
        $nextLevel = ($prevLevel < 3) ?$prevLevel + 1 : 1;
        $levelText = "Level " . $nextLevel . " (2X / 3X)";
        $isLoss    = true;
    }

    // Pehle Result Message alag se bhejenge
    sendTelegramMessage($botToken, $chatId,$resultMessage);
    sleep(1); // 1 second gap between messages
}

// Save state for next turn
$newState = [
    'last_sent_period' => $currentPeriod,
    'period'           => $currentPeriod,
    'size'             => $currentPred['size'],
    'rawSize'          => $currentPred['rawSize'],
    'number'           => $currentPred['number'],
    'level'            => ($isLoss ? ($savedState['level'] ?? 1) + 1 : 1)
];
file_put_contents($dataFile, json_encode($newState));

// ==========================================
// 3. NEW PREDICTION SIGNAL WITH HACK CONTEXT
// ==========================================
$signalMessage  = "👑 *91 CLUB VIP HACK SIGNAL* 👑\n";
$signalMessage .= "⏱️ *Game:* Wingo 1-Min\n\n";

$signalMessage .= "🚀 *UPCOMING PERIOD DETAILS* 🚀\n";
$signalMessage .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$signalMessage .= "📊 *Prediction:* *" . $currentPred['size'] . "*\n";
$signalMessage .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$signalMessage .= "🔢 *Lucky Number:* `" . $currentPred['number'] . "`\n";
$signalMessage .= "💰 *Fund Plan:* *" . $levelText . "*\n\n";

$signalMessage .= "⚠️ *IMPORTANT HACK NOTICE:* ⚠️\n";
$signalMessage .= "Ye hack algorithm *Sirf Naye Server Hack Account* par hi kaam karega. Agar aap puraane account me kheloge toh signal mismatch hoga aur loss ho sakta hai.\n\n";

$signalMessage .= "🔗 *HACK SERVER REGISTER LINK:* \n";
$signalMessage .= "👉 [Click Here To Register New Hack Account](" . $regLink . ")\n\n";

$signalMessage .= "📢 *Note:* Naya account bana kar hi 3-Level fund se khelein!";

// Signal message bhejenge
sendTelegramMessage($botToken, $chatId,$signalMessage);

echo "Success! Separate Result & New Hack Signal sent to Telegram.";
?>
