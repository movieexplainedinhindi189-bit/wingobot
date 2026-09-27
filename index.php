<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";

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

// Ultra-Unique Quantum Algorithm
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

// 1. Time & Period Calculation
$todayDate      = date('Ymd');$hours          = (int)date('H');
$minutes        = (int)date('i');$totalMinutes   = ($hours * 60) +$minutes;
$baseOffset     =$totalMinutes - 329;

$currentPeriod  = $todayDate . "10001" . sprintf("\%04d", $baseOffset);
$lastPeriod     = (string)((int)$currentPeriod - 1);

// Prevent duplicate execution per minute using a lightweight marker
$markerFile     = __DIR__ . '/last_run.txt';
$lastRunMinute  = file_exists($markerFile) ? trim(file_get_contents($markerFile)) : '';$currentMinute  = date('Y-m-d H:i');

if ($lastRunMinute ===$currentMinute) {
    echo "Cyber Bot: Already executed for this minute interval.";
    exit();
}
file_put_contents($markerFile,$currentMinute);

// 2. Evaluate Previous Period Outcome Dynamically
$prevPred       = generatePrediction($lastPeriod);
// Simulating actual server match pattern for dynamic uniqueness
$isJackpot      = ($prevPred['number'] % 3 === 0); // Unique dynamic trigger for jackpot
$isWin          = ($prevPred['number'] > 2);       // Dynamic win/loss simulation

if ($isJackpot) {
    // 💥 JACKPOT BANNER
    $resultMsg  = "╔═════════════════════════════╗\n";
    $resultMsg  = "  🤖 `[QUANTUM AI - HACK CORE v9.4]`  \n";
    $resultMsg  = "╚═════════════════════════════╝\n\n";
    $resultMsg .= "🔥 🎯 *CRITICAL JACKPOT HIT!* 🎯 🔥\n";
    $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $resultMsg .= "🆔 *PERIOD ID:* `" . $lastPeriod . "`\n";
    $resultMsg .= "🔢 *LUCKY NUMBER:* `" . $prevPred['number'] . "` (" . $prevPred['rawSize'] . ")\n";
    $resultMsg .= "💎 *STATUS:* *DIRECT NUMBER + SIZE MATCHED ✅*\n";
    $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $resultMsg .= "⚡ *Server Override Successful. Profit Extracted!*";
} else if ($isWin) {
    // 🎉 WIN BANNER
    $resultMsg  = "╔═════════════════════════════╗\n";
    $resultMsg  = "  🤖 `[QUANTUM AI - HACK CORE v9.4]`  \n";
    $resultMsg  = "╚═════════════════════════════╝\n\n";
    $resultMsg .= "🎉 *MATRIX RESULT: WIN CONFIRMED* 🎉\n";
    $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $resultMsg .= "🆔 *PERIOD ID:* `" . $lastPeriod . "`\n";
    $resultMsg .= "📊 *OUTCOME:* *" . $prevPred['rawSize'] . "* (Digit: `" . $prevPred['number'] . "`)\n";
    $resultMsg .= "💎 *STATUS:* *PROFIT SECURED ✅*\n";
    $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $resultMsg .= "⚡ *AI Engine: Initializing next matrix...*";
} else {
    // 💔 LOSS BANNER WITH RECOVERY
    $resultMsg  = "╔═════════════════════════════╗\n";
    $resultMsg  = "  🤖 `[QUANTUM AI - HACK CORE v9.4]`  \n";
    $resultMsg  = "╚═════════════════════════════╝\n\n";
    $resultMsg .= "💔 *MATRIX RESULT: PERIOD LOSS* 💔\n";
    $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $resultMsg .= "🆔 *PERIOD ID:* `" . $lastPeriod . "`\n";
    $resultMsg .= "📊 *OUTCOME:* *" . $prevPred['rawSize'] . "* (Digit: `" . $prevPred['number'] . "`)\n";
    $resultMsg .= "⚠️ *PROTOCOL:* *ACTIVATE RECOVERY LEVEL 2 🔴*\n";
    $resultMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $resultMsg .= "🤖 *ROBOT ADVICE:* Loss recover karne ke liye 5-Level fund plan use karein!";
}

sendTelegramMessage($botToken, $chatId,$resultMsg);
sleep(1); // 1-second delay for cyber separation

// 3. Generate New Prediction Signal with Hindi Warning
$currentPred = generatePrediction($currentPeriod);

$signalMessage  = "╔═════════════════════════════╗\n";
$signalMessage  = "  ⚡ `[91 CLUB CYBER HACK SERVER]`  \n";
$signalMessage  = "╚═════════════════════════════╝\n\n";
$signalMessage .= "🚀 *LIVE NEURAL NETWORK SIGNAL* 🚀\n";
$signalMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$signalMessage .= "🆔 *PERIOD ID:* `" . $currentPeriod . "`\n";
$signalMessage .= "📊 *PREDICTION:* *" . $currentPred['size'] . "*\n";
$signalMessage .= "🎨 *COLOR CODE:* *" . $currentPred['color'] . "*\n";
$signalMessage .= "🔢 *TARGET DIGIT:* `" . $currentPred['number'] . "`\n";
$signalMessage .= "💰 *FUND SYSTEM:* *Level 1 (5-Level Mandatory)*\n";
$signalMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$signalMessage .= "⚠️ *ZAROORI SOOCHNA (HINDI):* ⚠️\n";
$signalMessage .= "Agar aap purane account se kheloge toh error aayega aur loss ho sakta hai! **Sabhi log naya account banakar hi is hack signal par khele**, tabhi 100% accurate win aayega.\n\n";

$signalMessage .= "🔗 *NAYA ACCOUNT REGISTER LINK:* \n";
$signalMessage .= "👉 [Click Here To Register New Hack Account](" . $regLink . ")\n\n";

$signalMessage .= "🤖 *ROBOTIC RULE:* 5-Level balance maintain karke daily profit book karein!";

sendTelegramMessage($botToken, $chatId,$signalMessage);

echo "Success! Unique Cyber Banners, Dynamic Results, and Hindi warnings executed.";
?>
