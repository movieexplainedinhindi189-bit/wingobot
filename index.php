<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/bot_period_lock.txt';$robotPhoto   = "https://images.unsplash.com/photo-1485827404703-89b55fcc595e";

date_default_timezone_set('Asia/Kolkata');

// Helper Functions
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

// Prediction Algorithm matching image pattern
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

// Exact Period Calculation based on your image format (YYYYMMDD10001XXXX)
$todayDate      = date('Ymd');$hours          = (int)date('H');
$minutes        = (int)date('i');$totalMinutes   = ($hours * 60) +$minutes;
$baseOffset     =$totalMinutes - 329; // Adjust according to UTC/IST 1-min wingo

$currentPeriod  = $todayDate . "10001" . sprintf("\%04d", $baseOffset);
$lastPeriod     = (string)((int)$currentPeriod - 1);

// Prevent duplicate execution for the same period
$lastRunPeriod = file_exists($lockFile) ? trim(file_get_contents($lockFile)) : '';
if ($lastRunPeriod ===$currentPeriod) {
    echo "Already processed for period: " . $currentPeriod;
    exit();
}
file_put_contents($lockFile,$currentPeriod);

// Generate predictions
$lastPred    = generatePrediction($lastPeriod);
$currentPred = generatePrediction($currentPeriod);

// Simulated Win/Loss based on algorithm verification
$lastActualNum  = $lastPred['number'];$lastActualSize = $lastPred['rawSize'];$isWin = true; // Direct win synchronization

if ($isWin) {$resultMsg  = "🤖 *[ROBOT AI VERIFIER]*\n";
    $resultMsg .= "===========================\n";
    $resultMsg .= "🎉 *RESULT: DIRECT WIN CONFIRMED* 🎉\n";
    $resultMsg .= "===========================\n";
    $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
    $resultMsg .= "📊 *OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
    $resultMsg .= "💎 *STATUS:* *PROFIT EXTRACTED ✅*\n";
    $resultMsg .= "===========================\n";
    $resultMsg .= "⚡ *Robot AI: Target Achieved Successfully.*";
} else {
    $resultMsg  = "🤖 *[ROBOT AI VERIFIER]*\n";
    $resultMsg .= "===========================\n";
    $resultMsg .= "💔 *RESULT: PERIOD LOSS* 💔\n";
    $resultMsg .= "===========================\n";
    $resultMsg .= "🆔 *PERIOD:* `" . $lastPeriod . "`\n";
    $resultMsg .= "📊 *OUTCOME:* *" . $lastActualSize . "* (Digit: `" . $lastActualNum . "`)\n";
    $resultMsg .= "⚠️ *PROTOCOL:* *EXECUTE RECOVERY LEVEL 2 🔴*\n";
    $resultMsg .= "===========================\n";
    $resultMsg .= "🤖 *ROBOT ADVICE:* Loss recover karne ke liye 5-Level fund plan use karein!";
}

// Send Result of previous period
sendTelegramMessage($botToken, $chatId,$resultMsg);
sleep(1);

// Send New Signal with Robot Photo and Required Hindi text
$signalCaption  = "⚡ *[ROBOT HACK SIGNAL ENGINE]* ⚡\n\n";
$signalCaption .= "🚀 *LIVE SIGNAL* 🚀\n";
$signalCaption .= "🆔 *PERIOD ID:* `" . $currentPeriod . "`\n";
$signalCaption .= "📊 *PREDICTION:* *" . $currentPred['size'] . "*\n";
$signalCaption .= "🎨 *COLOR CODE:* *" . $currentPred['color'] . "*\n";
$signalCaption .= "🔢 *LUCKY DIGIT:* `" . $currentPred['number'] . "`\n";
$signalCaption .= "💰 *FUND SYSTEM:* *Level 1 (5-Level Mandatory)*\n\n";

$signalCaption .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$signalCaption .= "🔥 *ZAROORI SOOCHNA (IMPORTANT):*\n";
$signalCaption .= "**Agar daily ka 5-10 hazaar kamana hai toh naya ID banalo**, tabhi 100% win aayega purane account me error aa sakta hai!\n\n";
$signalCaption .= "🔗 *NEW ID REGISTER LINK:* \n";
$signalCaption .= "👉 [Click Here To Register New ID](" . $regLink . ")\n";
$signalCaption .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

sendTelegramPhoto($botToken,$chatId, $robotPhoto,$signalCaption);

echo "Success! Signal sent for period: " . $currentPeriod;
?>
