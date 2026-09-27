<?php
// ==========================================
// VIP COMPACT ROBOT PREDICTOR
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/vip_bot_lock.txt';$robotPhoto   = "https://images.unsplash.com/photo-1485827404703-89b55fcc595e";

date_default_timezone_set('Asia/Kolkata');

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

// 1. Precise UTC Minute Calculation for Wingo format
$utcDate        = gmdate('Ymd');$utcHours       = (int)gmdate('H');
$utcMinutes     = (int)gmdate('i');$utcTotalMins   = ($utcHours * 60) +$utcMinutes;

$periodSeq      = 10001 +$utcTotalMins;
$currentPeriod  = $utcDate . "10001" . $periodSeq;
$lastPeriod     = $utcDate . "10001" . ($periodSeq - 1);

// Minute Execution Lock
$currentMinute  = date('Y-m-d H:i');$lastRunMinute  = file_exists($lockFile) ? trim(file_get_contents($lockFile)) : '';

if ($lastRunMinute ===$currentMinute) {
    echo "VIP Bot already executed for this minute.";
    exit();
}
file_put_contents($lockFile,$currentMinute);

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

$lastPred    = generatePrediction($lastPeriod);
$currentPred = generatePrediction($currentPeriod);
$isWin       = ($lastPred['number'] >= 5);

// VIP Compact & Clean Message Layout
$vipMsg  = "🤖 *[VIP ROBOT HACK SYSTEM]* 🤖\n\n";
$vipMsg .= "📊 *PREVIOUS HISTORY CHECK:* \n";
$vipMsg .= "• Period: `" . $lastPeriod . "`\n";
$vipMsg .= "• Result: *" . $lastPred['rawSize'] . "* (" . ($isWin ? "WIN ✅" : "LOSS ❌") . ")\n\n";

$vipMsg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
$vipMsg .= "🚀 *NEXT LIVE SIGNAL* 🚀\n";
$vipMsg .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$vipMsg .= "🎯 *Prediction:* *" . $currentPred['size'] . "*\n";
$vipMsg .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$vipMsg .= "🔢 *Lucky Digit:* `" . $currentPred['number'] . "`\n";
$vipMsg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

$vipMsg .= "🔥 **Zaroori Soochna:** Daily ka 5-10 hazaar kamane ke liye naya ID banalo taaki error na aaye!\n";
$vipMsg .= "👉 [Click Here To Register New ID](" . $regLink . ")";

sendTelegramPhoto($botToken,$chatId, $robotPhoto,$vipMsg);

echo "VIP Compact Signal sent successfully for period: " . $currentPeriod;
?>
