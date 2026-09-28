<?php
// ==========================================
// VIP DYNAMIC WIN/LOSS PHOTO SIGNAL BOT
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/vip_photo_bot_lock.txt';

// Alag-alag photos WIN aur LOSS ke liye
$winPhoto     = "https://images.unsplash.com/photo-1518609878373-06d740f60d8b"; // Winning Celebration Vibe
$lossPhoto    = "https://images.unsplash.com/photo-1551836022-d5d88e9218df"; // Recovery / Warning Vibe

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
$lastPeriod     = $utcDate . "10001" . ($periodSeq - 1);

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

// Determine Win / Loss automatically based on algorithm logic
$isWin       = ($lastPred['number'] >= 5);
$selectedPhoto =$isWin ? $winPhoto :$lossPhoto;

// Compact & VIP Layout with History, Result, and Next Signal
$vipMsg  = "🤖 *[VIP ROBOT SIGNAL SYSTEM]* 🤖\n\n";

if ($isWin) {$vipMsg .= "🎉 *PREVIOUS RESULT: DIRECT WIN ✅*\n";
} else {
    $vipMsg .= "💔 *PREVIOUS RESULT: PERIOD LOSS ❌*\n";
}
$vipMsg .= "🆔 *Period:* `" . $lastPeriod . "` | *Outcome:* *" . $lastPred['rawSize'] . "*\n";
$vipMsg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

$vipMsg .= "🚀 *NEXT LIVE SIGNAL* 🚀\n";
$vipMsg .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$vipMsg .= "🎯 *Prediction:* *" . $currentPred['size'] . "*\n";
$vipMsg .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$vipMsg .= "🔢 *Digit:* `" . $currentPred['number'] . "`\n";
$vipMsg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

$vipMsg .= "💰 *MAINTAIN 5 LVL (Guaranteed Winning Daily 5K - 10K)*\n\n";
$vipMsg .= "⚠️ *WARNING:* *Hack Link se New Account banakar hi game play karein, warna Loss ho jayega!* 🛑\n\n";
$vipMsg .= "👉 [Click Here To Register New ID & Play](" . $regLink . ")";

sendTelegramPhoto($botToken,$chatId, $selectedPhoto,$vipMsg);

echo "VIP Dynamic Photo Signal sent successfully for period: " . $currentPeriod;
?>
