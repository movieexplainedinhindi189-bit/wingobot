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
    // Precise IST Minutes Calculation
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

// Check Duplicate (Stop sending multiple signals in same minute)
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Wait! Signal for period " . $currentPeriod . " already active.";
    exit();
}

$currentPred = generatePrediction($currentPeriod);

// ==========================================
// 2. STYLISH RESULT & RECOVERY LOGIC
// ==========================================
$winLossHeader = "";
$levelText     = "Level 1";
$isLoss        = false;

if (isset($savedState['period']) &&$savedState['period'] === $lastPeriod) {$prevPredSize = $savedState['rawSize'];$actualResultSize = $lastActualSize ?? generatePrediction($lastPeriod)['rawSize'];
    
    if ($prevPredSize === $actualResultSize) {$winLossHeader  = "━━━━━━━━━━━━━━━━━━━━━━\n";
        $winLossHeader .= "🎉 *LAST RESULT: ✅ WIN WIN WIN* 🎉\n";
        $winLossHeader .= "🆔 Period: `" . $lastPeriod . "`\n";
        $winLossHeader .= "📊 Result: *" . $actualResultSize . "*\n";
        $winLossHeader .= "💎 Status: *SUPER PROFIT 🟢*\n";
        $winLossHeader .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $levelText      = "Level 1 (Reset)";
    } else {
        $winLossHeader  = "━━━━━━━━━━━━━━━━━━━━━━\n";
        $winLossHeader .= "💔 *LAST RESULT: ❌ LOSS* 💔\n";
        $winLossHeader .= "🆔 Period: `" . $lastPeriod . "`\n";
        $winLossHeader .= "📊 Result: *" . $actualResultSize . "*\n";
        $winLossHeader .= "⚠️ Status: *USE RECOVERY FUND 🔴*\n";
        $winLossHeader .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $prevLevel =$savedState['level'] ?? 1;
        $nextLevel = ($prevLevel < 3) ?$prevLevel + 1 : 1;
        $levelText = "Level " . $nextLevel . " (2X / 3X)";
        $isLoss    = true;
    }
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
// 3. VIP TELEGRAM FORMATTING
// ==========================================
$message  = "👑 *91 CLUB VIP OFFICIAL SIGNALS* 👑\n";
$message .= "⏱️ *Game:* Wingo 1-Min\n\n";

$message .=$winLossHeader;

$message .= "🚀 *NEXT SIGNAL DETAILS* 🚀\n";
$message .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$message .= "📊 *Prediction:* *" . $currentPred['size'] . "*\n";
$message .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$message .= "🔢 *Lucky Number:* `" . $currentPred['number'] . "`\n";
$message .= "💰 *Fund Plan:* *" . $levelText . "*\n\n";

$message .= "🔗 *PLAY ON OFFICIAL SERVER:* \n";
$message .= "👉 [Click Here To Register & Play](" . $regLink . ")\n\n";

$message .= "📢 *RULE:* Maintained 3-Level Balance to get guaranteed daily profit!";

// Send to Telegram
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
$tgResponse = curl_exec($ch);
$tgHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($tgHttpCode === 200) {
    echo "Success! Signal sent for period: " . $currentPeriod;
} else {
    echo "Error sending to Telegram: " . $tgResponse;
}
?>
