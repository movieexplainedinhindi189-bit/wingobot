<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg";
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";
$historyFile  = "prediction_history.json";

// Set Timezone to IST
date_default_timezone_set('Asia/Kolkata');

// ==========================================
// 1. GENERATE CURRENT LIVE PERIOD (91 CLUB IST)
// ==========================================
$todayDate = date('Ymd');$hours     = (int)date('H');
$minutes   = (int)date('i');$totalMinutesToday = ($hours * 60) +$minutes + 1; // Current/Upcoming Period

$currentPeriod = $todayDate . "10001" . sprintf("\%04d", $totalMinutesToday);

// Generator function for predictions based on period
function getPredictionForPeriod($p) {
    $last3 = (int)substr($p, -3);
    $num   = ($last3 * 3 + 7) % 10;
    $size  = ($num >= 5) ? "BIG 📈" : "SMALL 📉";
    $rawSize = ($num >= 5) ? "BIG" : "SMALL";
    $color = ($num == 0 || $num == 5) ? "VIOLET 🟣" : (($num % 2 === 0) ? "RED 🔴" : "GREEN 🟢");
    
    return [
        'number'  => $num,
        'size'    => $size,
        'rawSize' => $rawSize,
        'color'   => $color
    ];
}

// Current Prediction
$currentPred = getPredictionForPeriod($currentPeriod);

// ==========================================
// 2. STYLISH WIN / LOSS CHECKER
// ==========================================
$winLossHeader = "";

if (file_exists($historyFile)) {
    $historyData = json_decode(file_get_contents($historyFile), true);
    
    if ($historyData && isset($historyData['period'])) {
        $prevPeriod   =$historyData['period'];
        $prevPredSize =$historyData['rawSize'];
        
        // Calculate what the actual result was for previous period
        $prevResult = getPredictionForPeriod($prevPeriod);
        $actualSize =$prevResult['rawSize'];
        
        // VIP Result Verification Banner
        if ($prevPredSize === $actualSize) {$winLossHeader  = "━━━━━━━━━━━━━━━━━━━━━━\n";
            $winLossHeader .= "🔥 *LAST RESULT: ✅ WIN WIN WIN* 🔥\n";
            $winLossHeader .= "🆔 Period: `" . $prevPeriod . "`\n";
            $winLossHeader .= "📊 Prediction: *" . $historyData['size'] . "*\n";
            $winLossHeader .= "🎉 *Status: SUCCESS 🟢*\n";
            $winLossHeader .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        } else {
            $winLossHeader  = "━━━━━━━━━━━━━━━━━━━━━━\n";
            $winLossHeader .= "💔 *LAST RESULT: ❌ LOSS* 💔\n";
            $winLossHeader .= "🆔 Period: `" . $prevPeriod . "`\n";
            $winLossHeader .= "📊 Prediction: *" . $historyData['size'] . "*\n";
            $winLossHeader .= "⚠️ *Status: RECOVER NEXT 🔴*\n";
            $winLossHeader .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
    }
}

// Save current prediction to file for next cycle check
$newHistory = [
    'period'  => $currentPeriod,
    'size'    => $currentPred['size'],
    'rawSize' => $currentPred['rawSize'],
    'number'  => $currentPred['number']
];
file_put_contents($historyFile, json_encode($newHistory));

// ==========================================
// 3. STYLISH TELEGRAM MESSAGE FORMAT
// ==========================================
$message  = "👑 *91 CLUB VIP SIGNAL* 👑\n";
$message .= "🎮 *Game:* Wingo 1-Minute\n\n";

// Win/Loss Result Add
$message .=$winLossHeader;

// New Signal Body
$message .= "🚀 *NEXT SIGNAL DETAILS* 🚀\n";
$message .= "🆔 *Period:* `" . $currentPeriod . "`\n";
$message .= "📊 *Prediction:* *" . $currentPred['size'] . "*\n";
$message .= "🎨 *Color:* *" . $currentPred['color'] . "*\n";
$message .= "🔢 *Lucky Number:* `" . $currentPred['number'] . "`\n\n";

$message .= "🔗 *OFFICIAL PLAY LINK:* \n";
$message .= "👉 [Click Here To Register & Play](" . $regLink . ")\n\n";

$message .= "⚡ *RULE:* Always follow 3-Level Fund Management strategy!";

// Send to Telegram
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
$tgResponse = curl_exec($ch);
$tgHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($tgHttpCode === 200) {
    echo "Success! Stylish VIP Signal sent to Telegram.";
} else {
    echo "Error sending to Telegram. Response: " . $tgResponse;
}
?>
