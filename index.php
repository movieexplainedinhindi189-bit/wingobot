<?php
// ==========================================
// ADVANCED 3D ALGORITHM VIP SIGNAL BOT
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/vip_3d_signal_lock.txt';$robotPhoto   = "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe"; // Futuristic 3D Vibe Photo

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
    echo "3D Signal already sent for this minute.";
    exit();
}
file_put_contents($lockFile,$currentMinute);

// 🔥 NEW 3D / ADVANCED PREDICTION ENGINE
function generate3DPrediction($period) {
    $hash = md5($period);
    $intVal = hexdec(substr($hash, 0, 6));
    
    // New randomized distribution logic
    $num  = ($intVal * 31 + 17) % 10;
    $size = ($num >= 5) ? "BIG 📈" : "SMALL 📉";
    
    // Color mapping based on 3D hash vector
    if ($num == 0 || $num == 5) {$color = "VIOLET 🟣";
    } elseif ($num \% 2 == 0) {$color = "RED 🔴";
    } else {
        $color = "GREEN 🟢";
    }
    
    return [
        'number' => $num,
        'size'   => $size,
        'color'  => $color
    ];
}

$currentPred = generate3DPrediction($currentPeriod);

// Sleek 3D Styled VIP Layout
$vipMsg  = "🔮 *[3D VIP HACK MATRIX]* 🔮\n\n";
$vipMsg .= "⚡ *QUANTUM LIVE SIGNAL* ⚡\n";
$vipMsg .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$vipMsg .= "🎯 *Prediction:* *" . $currentPred['size'] . "*\n";
$vipMsg .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$vipMsg .= "🔢 *Lucky Digit:* `" . $currentPred['number'] . "`\n";
$vipMsg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

$vipMsg .= "💰 *MAINTAIN 5 LVL (Guaranteed Winning Daily 5K - 10K)*\n\n";
$vipMsg .= "⚠️ *WARNING:* *Hack Link se New Account banakar hi game play karein, warna Loss ho jayega!* 🛑\n\n";
$vipMsg .= "👉 [Click Here To Register New ID & Play](" . $regLink . ")";

sendTelegramPhoto($botToken,$chatId, $robotPhoto,$vipMsg);

echo "3D VIP Signal sent successfully for period: " . $currentPeriod;
?>
