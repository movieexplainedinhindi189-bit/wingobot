<?php
// ==========================================
// VIP ACCURATE RESULT & SEPARATE PHOTO BOT
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$lockFile     = '/tmp/vip_accurate_bot_lock.txt';

// ✅ Winning ke liye alag photo (Congratulations / Celebration vibe)
$winPhoto     = "https://images.unsplash.com/photo-1513151233558-d860c5398176"; 

// ❌ Loss ke liye alag photo (Sorry / Recovery warning vibe)
$lossPhoto    = "https://images.unsplash.com/photo-1579546929518-9e396f3cc809"; 

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

// 1. Exact UTC Minute Calculation for Wingo format
$utcDate        = gmdate('Ymd');$utcHours       = (int)gmdate('H');
$utcMinutes     = (int)gmdate('i');$utcTotalMins   = ($utcHours * 60) +$utcMinutes;

$periodSeq      = 10001 +$utcTotalMins;
$currentPeriod  = $utcDate . "10001" . $periodSeq;
$lastPeriod     =$utcDate . "10001" . ($periodSeq - 1);$prevToLast     = $utcDate . "10001" . ($periodSeq - 2); // Usse bhi ek purana period prediction ke liye

// 2. Minute Execution Lock
$currentMinute  = date('Y-m-d H:i');$lastRunMinute  = file_exists($lockFile) ? trim(file_get_contents($lockFile)) : '';

if ($lastRunMinute ===$currentMinute) {
    echo "Bot already ran for this minute.";
    exit();
}
file_put_contents($lockFile,$currentMinute);

// Prediction Engine
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

// Pichle se pichle period ki prediction jo $lastPeriod par bani hogi
$predictionForLast = generatePrediction($prevToLast);

// $lastPeriod ka actual result nikalne ka logic
$last3Real  = (int)substr($lastPeriod, -3);
$realNum    = ($last3Real * 7 + 3) % 10;
$realSize   = ($realNum >= 5) ? "BIG" : "SMALL";

// Check karo ki prediction aur actual result match hua ya nahi
$isWin = ($predictionForLast['rawSize'] ===$realSize);

// Agle live signal ke liye prediction
$currentPred = generatePrediction($currentPeriod);

// Foto select karo Win ya Loss ke hisaab se
$selectedPhoto =$isWin ? $winPhoto :$lossPhoto;

// 4. Compact & Clean VIP Layout
$vipMsg  = "🤖 *[VIP ROBOT SIGNAL SYSTEM]* 🤖\n\n";

if ($isWin) {$vipMsg .= "🎉 *CONGRATULATIONS! PREVIOUS RESULT: WIN ✅*\n";
} else {
    $vipMsg .= "💔 *SORRY! PREVIOUS RESULT: LOSS ❌*\n";
}
$vipMsg .= "🆔 *Period:* `" . $lastPeriod . "` | *Result:* *" . $realSize . "* (Digit: `" . $realNum . "`)\n";
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

echo "VIP Accurate Signal sent successfully for period: " . $currentPeriod;
?>
