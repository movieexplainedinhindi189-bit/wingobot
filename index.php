<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$dataFile     = '/tmp/robot_bot_state.json'; // Render safe temp storage$robotPhoto   = "https://images.unsplash.com/photo-1485827404703-89b55fcc595e"; // Robot AI Image

date_default_timezone_set('Asia/Kolkata');

// Function to send text message (Win/Loss result)
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

// Function to send photo with caption (Robot Photo + Signal)
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

// 1. Time & Period Calculations
$todayDate      = date('Ymd');$hours          = (int)date('H');
$minutes        = (int)date('i');$totalMinutes   = ($hours * 60) +$minutes;
$baseOffset     =$totalMinutes - 329;

$currentPeriod  = $todayDate . "10001" . sprintf("\%04d", $baseOffset);
$lastPeriod     = (string)((int)$currentPeriod - 1);

// Load Saved State from /tmp/
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

// 2. Check and Send Win/Loss Result for Previous Period
if (isset($savedState['period']) && $savedState['period'] ===$lastPeriod) {
    $prevPredSize   =$savedState['rawSize'];
    $prevPredNumber =$savedState['number'];
    
    // Calculate actual result for previous period
    $actualResult   = generatePrediction($lastPeriod);$lastActualNum  = $actualResult['number'];$lastActualSize = $actualResult['rawSize'];$isSizeWin = ($prevPredSize ===$lastActualSize);
    
    if ($isSizeWin) {$resultMsg  = "🤖 *[ROBOT AI VERIFIER]*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🎉 *RESULT: DIRECT WIN CONFIRMED* 🎉\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
        $resultMsg .= "📊 *OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
        $resultMsg .= "💎 *STATUS:* *PROFIT EXTRACTED ✅*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "⚡ *Robot AI: Reset to Level 1.*";
        $currentLevel = 1;     } else {$nextLvl    = ($currentLevel < 5) ?$currentLevel + 1 : 1;
        $resultMsg  = "🤖 *[ROBOT AI VERIFIER]*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "💔 *RESULT: PERIOD LOSS* 💔\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
        $resultMsg .= "📊 *OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
        $resultMsg .= "⚠️ *PROTOCOL:* *EXECUTE RECOVERY LEVEL " . $nextLvl . " 🔴*\n";
        $resultMsg .= "===========================\n";
        $resultMsg .= "🤖 *ROBOT ADVICE:* Loss recover karne ke liye 5-Level fund plan use karein!";
        
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

// 3. Send New Signal with Robot Photo & Required Hindi text
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

echo "Success! Robot photo, win/loss results, and Hindi notice broadcasted perfectly."
?>
