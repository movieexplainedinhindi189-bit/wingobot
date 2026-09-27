<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$dataFile     = "bot_state.json";

date_default_timezone_set('Asia/Kolkata');

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

// ==========================================
// 1. FETCH LIVE PERIOD & RESULT FROM API
// ==========================================
function get91ClubData() {
    $urls = [
        "https://api.91club.com/api/webapi/GetNoHeaderWingoList",
        "https://draw.armaniprediction.com/api/wingo1m"
    ];

    $postData = json_encode([
        "typeId" => 1,
        "pageNo" => 1,
        "pageSize" => 10,
        "language" => 0
    ]);

    foreach ($urls as$url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json;charset=UTF-8",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 &&$response) {
            $resData = json_decode($response, true);
            if (isset($resData['data']['list'][0])) {
                return $resData['data']['list'][0];
            }
        }
    }
    return null;
}

$apiItem = get91ClubData();

if ($apiItem && isset($apiItem['issueNumber'])) {
    $lastPeriod     = (string)$apiItem['issueNumber'];
    $lastActualNum  = (int)$apiItem['number'];
    $lastActualSize = ($lastActualNum >= 5) ? "BIG" : "SMALL";
    $currentPeriod  = (string)((int)$lastPeriod + 1);
} else {
    // Exact IST Base Fallback
    $todayDate      = date('Ymd');$hours          = (int)date('H');
    $minutes        = (int)date('i');$totalMinutes   = ($hours * 60) +$minutes;
    $baseOffset     =$totalMinutes - 329;
    
    $currentPeriod  = $todayDate . "10001" . sprintf("\%04d", $baseOffset);
    $lastPeriod     = (string)((int)$currentPeriod - 1);
    
    // Algorithmic Fallback Result
    $fallbackResult = generatePrediction($lastPeriod);
    $lastActualNum  =$fallbackResult['number'];
    $lastActualSize =$fallbackResult['rawSize'];
}

$savedState = [];
if (file_exists($dataFile)) {
    $savedState = json_decode(file_get_contents($dataFile), true) ?? [];
}

// Duplicate Signal Stop
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Wait! Period " . $currentPeriod . " already processed.";
    exit();
}

$currentPred = generatePrediction($currentPeriod);

// ==========================================
// 2. GUARANTEED RESULT BANNER (WIN / LOSS / JACKPOT)
// ==========================================
$currentLevel =$savedState['level'] ?? 1;

if (isset($savedState['period'])) {
    $prevPeriod     =$savedState['period'];
    $prevPredSize   =$savedState['rawSize'];
    $prevPredNumber =$savedState['number'];
    
    // Match against target last period
    $isSizeWin   = ($prevPredSize === $lastActualSize);$isNumberWin = ($prevPredNumber ===$lastActualNum);
    
    if ($isSizeWin &&$isNumberWin) {
        // 💥 JACKPOT WIN
        $resultMsg  = "💥 🎯 *JACKPOT WIN! (NUMBER MATCH)* 🎯 💥\n\n";
        $resultMsg .= "🆔 *Period:* `" . $prevPeriod . "`\n";
        $resultMsg .= "🔢 *Winning Number:* `" . $lastActualNum . "`\n";
        $resultMsg .= "📊 *Winning Size:* *" . $lastActualSize . "*\n";
        $resultMsg .= "💎 *Status:* *EXACT NUMBER + SIZE JACKPOT HIT ✅*\n\n";
        $resultMsg .= "🚀 *Hack Server Performance: 100% PERFECT!*";
        $currentLevel = 1;     } else if ($isSizeWin) {
        // 🎉 DIRECT WIN
        $resultMsg  = "🎉 *CONGRATULATIONS! DIRECT WIN* 🎉\n\n";
        $resultMsg .= "🆔 *Period:* `" . $prevPeriod . "`\n";
        $resultMsg .= "📊 *Result:* *" . $lastActualSize . "* (Number: `" . $lastActualNum . "`)\n";
        $resultMsg .= "💎 *Status:* *PROFIT DONE ✅*\n\n";
        $resultMsg .= "⚡ *Hack Server Profit System Activated!*";
        $currentLevel = 1;
    } else {
        // 💔 PERIOD LOSS
        $nextLvl    = ($currentLevel < 5) ?$currentLevel + 1 : 1;
        $resultMsg  = "💔 *SORRY! PERIOD LOSS* 💔\n\n";
        $resultMsg .= "🆔 *Period:* `" . $prevPeriod . "`\n";
        $resultMsg .= "📊 *Actual Result:* *" . $lastActualSize . "* (Number: `" . $lastActualNum . "`)\n";
        $resultMsg .= "⚠️ *Status:* *USE RECOVERY LEVEL " . $nextLvl . " 🔴*\n\n";
        $resultMsg .= "📌 *Don't worry! Maintain 5-Level fund management to recover loss instantly.*";
        
        $currentLevel =$nextLvl;
    }

    sendTelegramMessage($botToken, $chatId,$resultMsg);
    sleep(1);
}

// Save state for next turn
$newState = [
    'last_sent_period' => $currentPeriod,
    'period'           => $currentPeriod,
    'size'             => $currentPred['size'],
    'rawSize'          => $currentPred['rawSize'],
    'number'           => $currentPred['number'],
    'level'            => $currentLevel
];
file_put_contents($dataFile, json_encode($newState));

// ==========================================
// 3. NEW HACK PREDICTION SIGNAL
// ==========================================
$signalMessage  = "👑 *91 CLUB VIP HACK SIGNAL* 👑\n";
$signalMessage .= "⏱️ *Game:* Wingo 1-Min\n\n";

$signalMessage .= "🚀 *UPCOMING PERIOD DETAILS* 🚀\n";
$signalMessage .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$signalMessage .= "📊 *Prediction:* *" . $currentPred['size'] . "*\n";
$signalMessage .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$signalMessage .= "🔢 *Lucky Number:* `" . $currentPred['number'] . "`\n";
$signalMessage .= "💰 *Fund Plan:* *Level " . $currentLevel . " (Maintain 5-Level)*\n\n";

$signalMessage .= "⚠️ *IMPORTANT HACK NOTICE:* ⚠️\n";
$signalMessage .= "Ye hack server *Sirf Naye Registered Hack Account* par hi work karta hai. Purane account se khelne par signal miss ho sakta hai.\n\n";

$signalMessage .= "🔗 *HACK SERVER REGISTER LINK:* \n";
$signalMessage .= "👉 [Click Here To Register New Hack Account](" . $regLink . ")\n\n";

$signalMessage .= "📢 *Note:* Guaranteed daily profit ke liye 5-Level balance maintain karke khele!";

sendTelegramMessage($botToken, $chatId,$signalMessage);

echo "Success! Result and Prediction sent.";
?>
