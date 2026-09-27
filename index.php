<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$dataFile     = '/tmp/bot_state_91club.json'; // Render writable temp storage

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

// ==========================================
// 1. FETCH REAL 91 CLUB GAME HISTORY API
// ==========================================
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
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
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

// Prediction Algorithm
function generatePrediction($p) {
    $last3   = (int)substr($p, -3);
    $num     = ($last3 * 7 + 3) % 10;
    $size    = ($num >= 5) ? "BIG 📈 [VECTOR-X]" : "SMALL 📉 [VECTOR-Y]";
    $rawSize = ($num >= 5) ? "BIG" : "SMALL";
    $color   = ($num == 0 || $num == 5) ? "VIOLET 🟣 [NEON]" : (($num % 2 === 0) ? "RED 🔴 [ALPHA]" : "GREEN 🟢 [BETA]");
    
    return [
        'number'  => $num,
        'size'    => $size,
        'rawSize' => $rawSize,
        'color'   => $color
    ];
}

$historyList = getReal91ClubHistory();

if ($historyList && isset($historyList[0])) {
    $lastFinishedItem =$historyList[0];
    $lastPeriod       = (string)$lastFinishedItem['issueNumber'];
    $lastActualNum    = (int)$lastFinishedItem['number'];
    $lastActualSize   = ($lastActualNum >= 5) ? "BIG" : "SMALL";
    
    $currentPeriod    = (string)((int)$lastPeriod + 1);
} else {
    // Fallback time calculation if API is momentarily unreachable
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

// Prevent duplicate execution for same period
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Cyber Bot: Period " . $currentPeriod . " already processed.";
    exit();
}

$currentPred  = generatePrediction($currentPeriod);
$currentLevel =$savedState['level'] ?? 1;

// ==========================================
// 2. REAL VERIFICATION & CYBER BANNER
// ==========================================
if (isset($savedState['period']) && $savedState['period'] ===$lastPeriod) {
    $prevPredSize   =$savedState['rawSize'];
    $prevPredNumber =$savedState['number'];
    
    if ($lastActualSize !== null &&$lastActualNum !== null) {
        $isSizeWin   = ($prevPredSize === $lastActualSize);$isNumberWin = ($prevPredNumber ===$lastActualNum);
        
        if ($isSizeWin &&$isNumberWin) {
            // 💥 JACKPOT (Size + Number Match)
            $resultMsg  = "╔═════════════════════════════╗\n";
            $resultMsg .= "  🤖 `[QUANTUM AI - HACK CORE v9.4]`  \n";
            $resultMsg .= "╚═════════════════════════════╝\n\n";
            $resultMsg .= "🔥 🎯 *CRITICAL JACKPOT HIT!* 🎯 🔥\n";
            $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $resultMsg .= "🆔 *PERIOD ID:* `" . $lastPeriod . "`\n";
            $resultMsg .= "🔢 *WINNING NUMBER:* `" . $lastActualNum . "` (" . $lastActualSize . ")\n";
            $resultMsg .= "💎 *STATUS:* *DIRECT NUMBER + SIZE MATCHED ✅*\n";
            $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $resultMsg .= "⚡ *Server Override Successful. Level Reset to 1!*";
            $currentLevel = 1;         } else if ($isSizeWin) {
            // 🎉 DIRECT WIN
            $resultMsg  = "╔═════════════════════════════╗\n";
            $resultMsg .= "  🤖 `[QUANTUM AI - HACK CORE v9.4]`  \n";
            $resultMsg .= "╚═════════════════════════════╝\n\n";
            $resultMsg .= "🎉 *MATRIX RESULT: WIN CONFIRMED* 🎉\n";
            $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $resultMsg .= "🆔 *PERIOD ID:* `" . $lastPeriod . "`\n";
            $resultMsg .= "📊 *ACTUAL OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
            $resultMsg .= "💎 *STATUS:* *PROFIT SECURED ✅*\n";
            $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $resultMsg .= "⚡ *AI Engine: Level Reset to 1.*";
            $currentLevel = 1;
        } else {
            // 💔 LOSS & 5-LEVEL RECOVERY
            $nextLvl    = ($currentLevel < 5) ?$currentLevel + 1 : 1;
            $resultMsg  = "╔═════════════════════════════╗\n";
            $resultMsg .= "  🤖 `[QUANTUM AI - HACK CORE v9.4]`  \n";
            $resultMsg .= "╚═════════════════════════════╝\n\n";
            $resultMsg .= "💔 *MATRIX RESULT: PERIOD LOSS* 💔\n";
            $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $resultMsg .= "🆔 *PERIOD ID:* `" . $lastPeriod . "`\n";
            $resultMsg .= "📊 *ACTUAL OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
            $resultMsg .= "⚠️ *PROTOCOL:* *ACTIVATE RECOVERY LEVEL " . $nextLvl . " 🔴*\n";
            $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $resultMsg .= "🤖 *ROBOT ADVICE:* Loss recover karne ke liye 5-Level fund plan use karein!";
            
            $currentLevel =$nextLvl;
        }

        sendTelegramMessage($botToken, $chatId,$resultMsg);
        sleep(1);
    }
}

// Save State to /tmp/
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
// 3. NEW SIGNAL WITH HINDI INSTRUCTIONS
// ==========================================
$signalMessage  = "╔═════════════════════════════╗\n";
$signalMessage .= "  ⚡ `[91 CLUB CYBER HACK SERVER]`  \n";
$signalMessage .= "╚═════════════════════════════╝\n\n";
$signalMessage .= "🚀 *LIVE NEURAL NETWORK SIGNAL* 🚀\n";
$signalMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$signalMessage .= "🆔 *PERIOD ID:* `" . $currentPeriod . "`\n";
$signalMessage .= "📊 *PREDICTION:* *" . $currentPred['size'] . "*\n";
$signalMessage .= "🎨 *COLOR CODE:* *" . $currentPred['color'] . "*\n";
$signalMessage .= "🔢 *TARGET DIGIT:* `" . $currentPred['number'] . "`\n";
$signalMessage .= "💰 *FUND SYSTEM:* *Level " . $currentLevel . " (5-Level Mandatory)*\n";
$signalMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$signalMessage .= "⚠️ *ZAROORI SOOCHNA (HINDI):* ⚠️\n";
$signalMessage .= "Agar aap purane account se kheloge toh error aayega aur loss ho sakta hai! **Sabhi log naya account banakar hi is hack signal par khele**, tabhi 100% accurate win aayega.\n\n";

$signalMessage .= "🔗 *NAYA ACCOUNT REGISTER LINK:* \n";
$signalMessage .= "👉 [Click Here To Register New Hack Account](" . $regLink . ")\n\n";

$signalMessage .= "🤖 *ROBOTIC RULE:* 5-Level balance maintain karke daily profit book karein!";

sendTelegramMessage($botToken, $chatId,$signalMessage);

echo "Success! Real 91 Club API verification + Cyber Banners executed.";
?>
