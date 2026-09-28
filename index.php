<?php
// ==========================================
// 3D VIP SIGNAL BOT (FIXED & ERROR FREE)
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/vip_3d_bot_lock.txt';$robotPhoto   = "https://images.unsplash.com/photo-1639762681485-074b7f938ba0";

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo "Curl Error: " . curl_error($ch);
    }
    curl_close($ch);
    return $res;
}

// 1. UTC Minute Calculation for Wingo format
$utcDate        = gmdate('Ymd');$utcHours       = (int)gmdate('H');
$utcMinutes     = (int)gmdate('i');$utcTotalMins   = ($utcHours * 60) +$utcMinutes;

$periodSeq      = 10001 +$utcTotalMins;
$currentPeriod  = $utcDate . "10001" . $periodSeq;

// 2. Simple Execution Lock
$currentMinute  = date('Y-m-d H:i');$lastRunMinute  = file_exists($lockFile) ? trim(file_get_contents($lockFile)) : '';

if ($lastRunMinute ===$currentMinute) {
    echo "⚠️ Signal already sent for this minute (" . $currentMinute . ").";
    exit();
}
file_put_contents($lockFile,$currentMinute);

// 3. 3D Compact Prediction Engine
function generate3DBlock($period) {
    $lastDigit = (int)substr($period, -1);
    $num       = ($lastDigit * 4 + 5) % 10;
    $size      = ($num >= 5) ? "BIG 📈" : "SMALL 📉";
    
    if ($num == 0 || $num == 5) {$color = "VIOLET 🟣";
    } elseif ($num \% 2 == 0) {$color = "RED 🔴";
    } else {
        $color = "GREEN 🟢";
    }
    
    return ['num' => $num, 'size' => $size, 'color' =>$color];
}

$pred = generate3DBlock($currentPeriod);

// 4. 3D Box Layout
$vipMsg  = "╔══════════════════════╗\n";
$vipMsg .= "   🔮 *3D VIP HACK MATRIX* 🔮\n";
$vipMsg .= "╚══════════════════════╝\n\n";

$vipMsg .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$vipMsg .= "🎯 *Prediction:* *" . $pred['size'] . "*\n";
$vipMsg .= "🎨 *Color:* *" . $pred['color'] . "*\n";
$vipMsg .= "🔢 *Digit:* `" . $pred['num'] . "`\n\n";

$vipMsg .= "⚡ *MAINTAIN 5 LVL FUND* ⚡\n";
$vipMsg .= "⚠️ *WARNING:* *Hack Link se New Account banakar hi play karein, warna Loss hoga!* 🛑\n\n";
$vipMsg .= "👉 [Register New Hack ID](" . $regLink . ")";

$response = sendTelegramPhoto($botToken,$chatId, $robotPhoto,$vipMsg);

if ($response) {
    echo "✅ 3D Signal successfully sent to Telegram for period: " . $currentPeriod;
} else {
    echo "❌ Failed to send signal.";
}
?>
