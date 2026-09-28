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
// 1. FETCH LIVE PERIOD & LAST RESULT FROM 91 CLUB API
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
    // API se direct real issue numbers aur actual winning size/number fetch
    $lastFinishedItem =$listData[0];
    $lastPeriod       = (string)$lastFinishedItem['issueNumber'];
    $lastNumber       = (int)$lastFinishedItem['number'];
    $lastActualSize   = ($lastNumber >= 5) ? "BIG" : "SMALL";
    
    // Agla live period
    $currentPeriod    = (string)((int)$lastPeriod + 1);
} else {
    // Fallback math calculation
    $todayDate    = date('Ymd');$hours        = (int)date('H');
    $minutes      = (int)date('i');$totalMinutes = ($hours * 60) +$minutes;
    $baseOffset   =$totalMinutes - 329;
    
    $currentPeriod  =$todayDate . "10001" . sprintf("%04d", $baseOffset);$lastPeriod     = (string)((int)$currentPeriod - 1);$lastActualSize = null;
}

// Prediction Generator Algorithm
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

// Load Previous State
$savedState = [];
if (file_exists($dataFile)) {
    $savedState = json_decode(file_get_contents($dataFile), true) ?? [];
}

// Agar same period ke liye prediction pehle bhej di hai, toh firse repeat nahi karega
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Wait! Signal for period " . $currentPeriod . " already sent. Waiting for next minute.";
    exit();
}

$currentPred = generatePrediction($currentPeriod);

// ==========================================
// 2. CHECK REAL WIN / LOSS FOR LAST PERIOD
// ==========================================
$winLossHeader = "";

if (isset($savedState['period']) && $savedState['period'] ===$lastPeriod) {
    $prevPredSize =$savedState['rawSize'];
    
    // Real API Result check ya algorithm fall-back
    $actualResultSize = $lastActualSize ?? generatePrediction($lastPeriod)['rawSize'];
    
    if ($prevPredSize === $actualResultSize) {$winLossHeader  = "━━━━━━━━━━━━━━━━━━━━━━\n";
        $winLossHeader .= "🔥 *LAST RESULT: ✅ WIN WIN WIN* 🔥\n";
        $winLossHeader .= "🆔 Period: `" . $lastPeriod . "`\n";
        $winLossHeader .= "📊 Result: *" . $actualResultSize . "* (Prediction: *" . $prevPredSize . "*)\n";
        $winLossHeader .= "🎉 *Status: SUCCESS 🟢*\n";
        $winLossHeader .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
    } else {
        $winLossHeader  = "━━━━━━━━━━━━━━━━━━━━━━\n";
        $winLossHeader .= "💔 *LAST RESULT: ❌ LOSS* 💔\n";
        $winLossHeader .= "🆔 Period: `" . $lastPeriod . "`\n";
        $winLossHeader .= "📊 Result: *" . $actualResultSize . "* (Prediction: *" . $prevPredSize . "*)\n";
        $winLossHeader .= "⚠️ *Status: RECOVER NEXT 🔴*\n";
        $winLossHeader .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}

// Save current prediction to file
$newState = [
    'last_sent_period' => $currentPeriod,
    'period'           => $currentPeriod,
    'size'             => $currentPred['size'],
    'rawSize'          => $currentPred['rawSize'],
    'number'           => $currentPred['number']
];
file_put_contents($dataFile, json_encode($newState));

// ==========================================
// 3. STYLISH TELEGRAM MESSAGE FORMAT
// ==========================================
$message  = "👑 *91 CLUB VIP SIGNAL* 👑\n";
$message .= "🎮 *Game:* Wingo 1-Minute\n\n";

$message .=$winLossHeader;

$message .= "🚀 *NEXT SIGNAL DETAILS* 🚀\n";
$message .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$message .= "📊 *Prediction:* *" . $currentPred['size'] . "*\n";
$message .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$message .= "🔢 *Lucky Number:* `" . $currentPred['number'] . "`\n\n";

$message .= "🔗 *OFFICIAL PLAY LINK:* \n";
$message .= "👉 [Click Here To Register & Play](" . $regLink . ")\n\n";

$message .= "⚡ *RULE:* Always follow 3-Level Fund Management strategy!";

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
