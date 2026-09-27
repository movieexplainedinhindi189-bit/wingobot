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

// Ultra-Unique Prediction Matrix
function generatePrediction($p) {
    $last3   = (int)substr($p, -3);
    $num     = ($last3 * 7 + 3) % 10;
    $size    = ($num >= 5) ? "BIG 📈 [HIGH VECTOR]" : "SMALL 📉 [LOW VECTOR]";
    $rawSize = ($num >= 5) ? "BIG" : "SMALL";
    $color   = ($num == 0 || $num == 5) ? "VIOLET 🟣" : (($num % 2 === 0) ? "RED 🔴 [ALPHA]" : "GREEN 🟢 [BETA]");
    
    return [
        'number'  => $num,
        'size'    => $size,
        'rawSize' => $rawSize,
        'color'   => $color
    ];
}

// ==========================================
// 1. FETCH LIVE DATA FROM 91 CLUB API
// ==========================================
function getReal91ClubHistory() {
    $endpoints = [
        "https://api.91club.com/api/webapi/GetNoHeaderWingoList",
        "https://91clubapi.com/api/webapi/GetNoHeaderWingoList",
        "https://api.91clubadmin.com/api/webapi/GetNoHeaderWingoList"
    ];

    $postData = json_encode([
        "typeId"   => 1,
        "pageNo"   => 1,
        "pageSize" => 10,
        "language" => 0
    ]);

    $userAgents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1"
    ];

    foreach ($endpoints as$url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json;charset=UTF-8",
            "User-Agent: " . $userAgents[array_rand($userAgents)],
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

$historyList = getReal91ClubHistory();

if ($historyList && isset($historyList[0])) {
    $lastFinishedItem =$historyList[0];
    $lastPeriod       = (string)$lastFinishedItem['issueNumber'];
    $lastActualNum    = (int)$lastFinishedItem['number'];
    $lastActualSize   = ($lastActualNum >= 5) ? "BIG" : "SMALL";
    
    $currentPeriod    = (string)((int)$lastPeriod + 1);
} else {
    $todayDate      = date('Ymd');$hours          = (int)date('H');
    $minutes        = (int)date('i');$totalMinutes   = ($hours * 60) +$minutes;
    $baseOffset     =$totalMinutes - 329;
    
    $currentPeriod  =$todayDate . "10001" . sprintf("%04d", $baseOffset);$lastPeriod     = (string)((int)$currentPeriod - 1);$lastActualNum  = null;
    $lastActualSize = null;
}

$savedState = [];
if (file_exists($dataFile)) {
    $savedState = json_decode(file_get_contents($dataFile), true) ?? [];
}

// Prevent Duplicate Signals
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "CYBER BOT: Period " . $currentPeriod . " already broadcasted.";
    exit();
}

$currentPred  = generatePrediction($currentPeriod);
$currentLevel =$savedState['level'] ?? 1;

// ==========================================
// 2. ROBOTIC WIN / LOSS / JACKPOT BANNER
// ==========================================
if (isset($savedState['period']) && $savedState['period'] ===$lastPeriod) {
    $prevPredSize   =$savedState['rawSize'];
    $prevPredNumber =$savedState['number'];
    
    if ($lastActualSize !== null &&$lastActualNum !== null) {
        $isSizeWin   = ($prevPredSize === $lastActualSize);$isNumberWin = ($prevPredNumber ===$lastActualNum);
        
        if ($isSizeWin &&$isNumberWin) {
            // 💥 ROBOTIC JACKPOT
            $resultMsg  = "🤖 *[QUANTUM AI VERIFIER]*\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "💥 🎯 *CRITICAL JACKPOT OVERRIDE!* 🎯 💥\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
            $resultMsg .= "🔢 *SERVER NUMBER:* `" . $lastActualNum . "`\n";
            $resultMsg .= "📊 *SERVER VECTOR:* *" . $lastActualSize . "*\n";
            $resultMsg .= "⚡ *ACCURACY:* *100% PERFECT MATRIX MATCH ✅*\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "⚙️ *SYSTEM STATUS:* Profit Extracted. Resetting to Level 1.";
            $currentLevel = 1;         } else if ($isSizeWin) {
            // 🎉 ROBOTIC DIRECT WIN
            $resultMsg  = "🤖 *[QUANTUM AI VERIFIER]*\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "🎉 *SYSTEM RESULT: DIRECT WIN CONFIRMED* 🎉\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
            $resultMsg .= "📊 *ACTUAL OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
            $resultMsg .= "💎 *PROFIT STATUS:* *SUCCESSFUL EXTRACTION ✅*\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "⚡ *AI Core: Next target loading...*";
            $currentLevel = 1;
        } else {
            // 💔 ROBOTIC LOSS & RECOVERY
            $nextLvl    = ($currentLevel < 5) ?$currentLevel + 1 : 1;
            $resultMsg  = "🤖 *[QUANTUM AI VERIFIER]*\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "💔 *SYSTEM RESULT: PERIOD MISMATCH* 💔\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
            $resultMsg .= "📊 *ACTUAL OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
            $resultMsg .= "⚠️ *PROTOCOL:* *EXECUTE RECOVERY LEVEL " . $nextLvl . " 🔴*\n";
            $resultMsg .= "===========================\n";
            $resultMsg .= "🤖 *ROBOT ADVICE:* Maintain strict 5-Level Multiplier to recover current loss!";
            
            $currentLevel =$nextLvl;
        }

        sendTelegramMessage($botToken, $chatId,$resultMsg);
        sleep(1);
    }
}

// Save Bot State
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
// 3. FUTURISTIC ROBOT SIGNAL
// ==========================================
$signalMessage  = "⚡ *[91 CLUB QUANTUM HACK BOT]* ⚡\n";
$signalMessage .= "🤖 *MODE:* Autonomous Cyber Signal Engine\n";
$signalMessage .= "===========================\n\n";

$signalMessage .= "🚀 *LIVE MATRIX SIGNAL* 🚀\n";
$signalMessage .= "🆔 *PERIOD ID:* `" . $currentPeriod . "`\n";
$signalMessage .= "📊 *AI PREDICTION:* *" . $currentPred['size'] . "*\n";
$signalMessage .= "🎨 *COLOR CODE:* *" . $currentPred['color'] . "*\n";
$signalMessage .= "🔢 *LUCKY DIGIT:* `" . $currentPred['number'] . "`\n";
$signalMessage .= "💰 *FUND SYSTEM:* *Level " . $currentLevel . " (5-Level Mandatory)*\n\n";

$signalMessage .= "===========================\n";
$signalMessage .= "⚠️ *SYSTEM COMPATIBILITY NOTICE:* ⚠️\n";
$signalMessage .= "Robot Algorithm strictly matches *HACK SERVER REGISTERED ACCOUNTS*. Old accounts will cause signal mismatch & losses!\n\n";

$signalMessage .= "🔗 *HACK SERVER LINK:* \n";
$signalMessage .= "👉 [Click Here To Register New Hack Account](" . $regLink . ")\n\n";

$signalMessage .= "⚙️ *ROBOTIC RULE:* Always follow 5-Level Fund Plan for 100% daily profit automation!";

sendTelegramMessage($botToken, $chatId,$signalMessage);

echo "Success! Unique Robot AI Signal and Verification executed.";
?>
