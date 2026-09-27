<?php
// ==========================================
// PYAARA SA VIP COMPACT SIGNAL BOT
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/vip_pyaara_bot_lock.txt';$robotPhoto   = "https://images.unsplash.com/photo-1485827404703-89b55fcc595e";

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

// Exact UTC Minute Calculation for Wingo format
$utcDate        = gmdate('Ymd');$utcHours       = (int)gmdate('H');
$utcMinutes     = (int)gmdate('i');$utcTotalMins   = ($utcHours * 60) +$utcMinutes;

$periodSeq      = 10001 +$utcTotalMins;
$currentPeriod  = $utcDate . "10001" . $periodSeq;

// Minute Execution Lock
$currentMinute  = date('Y-m-d H:i');$lastRunMinute  = file_exists($lockFile) ? trim(file_get_contents($lockFile)) : '';

if ($lastRunMinute ===$currentMinute) {
    echo "Signal already sent for this minute.";
    exit();
}
file_put_contents($lockFile,$currentMinute);

// Prediction Algorithm
function generatePrediction($p) {
    $last3   = (int)substr($p, -3);
    $num     = ($last3 * 7 + 3) % 10;
    $size    = ($num >= 5) ? "BIG 📈" : "SMALL 📉";
    $color   = ($num == 0 || $num == 5) ? "VIOLET 🟣" : (($num % 2 === 0) ? "RED 🔴" : "GREEN 🟢");
    
    return [
        'number'  => $num,
        'size'    => $size,
        'color'   => $color
    ];
}

$currentPred = generatePrediction($currentPeriod);

// Super Clean & Compact VIP Layout with Instructions
$vipMsg  = "🤖 *[VIP SIGNAL]*\n\n";
$vipMsg .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$vipMsg .= "🎯 *Prediction:* *" . $currentPred['size'] . "*\n";
$vipMsg .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$vipMsg .= "🔢 *Digit:* `" . $currentPred['number'] . "`\n\n";

$vipMsg .= "⚠️ *Maintain 5 Level Fund*\n";
$vipMsg .= "🔥 *New Account se ID banakar hi game play karein!*\n\n";
$vipMsg .= "👉 [Click Here To Register New ID](" . $regLink . ")";

sendTelegramPhoto($botToken,$chatId, $robotPhoto,$vipMsg);

echo "Pyaara VIP Signal sent successfully for period: " . $currentPeriod;
?>
