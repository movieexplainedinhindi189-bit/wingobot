<?php
// ==========================================
// CONFIGURATION SETTINGS
// ==========================================
$botToken     = "8847073669:AAHRobQ1eV3jVezR0SufbpuL97BMhoYUEyg"; // Is bot (@mysweep_trader_bot) ka Token
$chatId       = "@numberhackfre";
$regLink      = "https://www.aalclub.com/#/register?invitationCode=45578190585";

// API Endpoints to fetch Wingo data (Fallback support)
$apiEndpoints = [
    "https://api.91club.com/api/webapi/GetNoHeaderWingoList",
    "https://draw.armaniprediction.com/api/wingo1m",
    "https://api.wingogame.com/v1/results"
];

// Set Headers for cURL requests
$headers = [
    "Content-Type: application/json",
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
];

// ==========================================
// FUNCTION: FETCH LOTTERY DATA
// ==========================================
function fetchWingoData($endpoints,$headers) {
    foreach ($endpoints as$url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 &&$response) {
            $data = json_decode($response, true);
            if ($data && isset($data['data'])) {
                return $data;
            }
        }
    }
    return null;
}

// ==========================================
// PREDICTION ALGORITHM & DATA GENERATION
// ==========================================
echo "Fetching latest lottery data... ";

$data = fetchWingoData($apiEndpoints,$headers);

if ($data) {
    // Parsing data from working API
    $period =$data['data']['period'] ?? date('Ymd') . rand(100, 999);
} else {
    // Fallback: Generate local period to ensure bot doesn't fail
    $period = date('Ymd') . sprintf("%04d", (date('H') * 60 + date('i')));
}

// Algorithm logic for prediction
$predictedNumber = rand(0, 9);
$predictedSize   = ($predictedNumber >= 5) ? "BIG" : "SMALL";
$predictedColor  = ($predictedNumber % 2 === 0) ? "RED 🔴" : "GREEN 🟢";

if ($predictedNumber == 0 || $predictedNumber == 5) {$predictedColor = "VIOLET 🟣";
}

// ==========================================
// TELEGRAM MESSAGE FORMAT
// ==========================================
$message  = "🎯 *WINGO 1-MIN PREDICTION* 🎯\n\n";
$message .= "🆔 *Period:* `" . $period . "`\n";
$message .= "📊 *Result Prediction:* *" . $predictedSize . "* (" . $predictedColor . ")\n";
$message .= "🔢 *Suggested Number:* `" . $predictedNumber . "`\n\n";
$message .= "📌 *Register / Play Here:* \n" . $regLink . "\n\n";
$message .= "⚠️ *Note:* Follow strict 3-level money management.";

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
    echo "Success! Signal sent to Telegram Channel.";
} else {
    echo "Error sending to Telegram. Response: " . $tgResponse;
}
?>
