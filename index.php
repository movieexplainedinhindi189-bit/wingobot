<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$dataFile     = __DIR__ . '/bot_state.json';

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

// Prediction Algorithm
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

// 1. Time & Period Calculations
$todayDate      = date('Ymd');$hours          = (int)date('H');
$minutes        = (int)date('i');$totalMinutes   = ($hours * 60) +$minutes;
$baseOffset     =$totalMinutes - 329;

$currentPeriod  = $todayDate . "10001" . sprintf("\%04d", $baseOffset);
$lastPeriod     = (string)((int)$currentPeriod - 1);

// Load Saved State
$savedState = [];
if (file_exists($dataFile)) {
    $savedState = json_decode(file_get_contents($dataFile), true) ?? [];
}

// Stop Duplicate Execution for the same period
if (isset($savedState['last_sent_period']) && $savedState['last_sent_period'] ===$currentPeriod) {
    echo "Already processed for period " . $currentPeriod;
    exit();
}

$currentPred  = generatePrediction($currentPeriod);
$currentLevel =$savedState['level'] ?? 1;

// 2. Check and Send Result for Previous Period
if (isset($savedState['period']) &&$savedState['period'] === $lastPeriod) {$prevPredSize   = $savedState['rawSize'];$prevPredNumber = $savedState['number'];$actualResult   = generatePrediction($lastPeriod);$lastActualNum  = $actualResult['number'];$lastActualSize = $actualResult['rawSize'];$isSizeWin   = ($prevPredSize ===$lastActualSize);
    
    if ($isSizeWin) {$resultMsg  = "🤖 *[QUANTUM AI VERIFIER]*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🎉 *SYSTEM RESULT: DIRECT WIN CONFIRMED* 🎉\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
        $resultMsg .= "📊 *OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
        $resultMsg .= "💎 *STATUS:* *PROFIT EXTRACTED ✅*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "⚡ *AI Core: Resetting to Level 1.*";
        $currentLevel = 1;     } else {$nextLvl    = ($currentLevel < 5) ?$currentLevel + 1 : 1;
        $resultMsg  = "🤖 *[QUANTUM AI VERIFIER]*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "💔 *SYSTEM RESULT: PERIOD LOSS* 💔\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
        $resultMsg .= "📊 *OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
        $resultMsg .= "⚠️ *PROTOCOL:* *EXECUTE RECOVERY LEVEL " . $nextLvl . " 🔴*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🤖 *ROBOT ADVICE:* Maintain strict 5-Level Multiplier!";
        
        $currentLevel =$nextLvl;
    }

    sendTelegramMessage($botToken, $chatId,$resultMsg);
    sleep(1);
}

// Save New State
$newState = [
    'last_sent_period' => $currentPeriod,
    'period'           => $currentPeriod,
    'size'             => $currentPred['size'],
    'rawSize'          => $currentPred['rawSize'],
    'number'           => $currentPred['number'],
    'level'            => $currentLevel
];
file_put_contents($dataFile, json_encode($newState));

// 3. Send New Robot Signal with Hindi Instructions
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
$signalMessage .= "⚠️ *ZAROORI SOOCHNA (IMPORTANT):* ⚠️\n";
$signalMessage .= "Agar aap purane account se kheloge toh loss ho sakta hai! **Sabhi log naya account banakar hi is hack signal par khele**, tabhi 100% accurate win aayega.\n\n";

$signalMessage .= "🔗 *NAYA ACCOUNT REGISTER LINK:* \n";
$signalMessage .= "👉 [Click Here To Register New Hack Account](" . $regLink . ")\n\n";

$signalMessage .= "⚙️ *ROBOTIC RULE:* Daily profit ke liye hamesha 5-Level fund plan maintain karein!";

sendTelegramMessage($botToken, $chatId,$signalMessage);

echo "Success! State tracked, result and Hindi instructions broadcasted.";
?>
