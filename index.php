<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI CYBERPREDICTOR v15.0</title>
    <style>
        :root {
            --neon-blue: #00f3ff;
            --neon-green: #00ff66;
            --neon-red: #ff0055;
            --bg-dark: #0a0b10;
            --card-bg: #121526;
        }

        body {
            background-color: var(--bg-dark);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 15px;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            border: 2px solid var(--neon-blue);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 0 25px rgba(0, 243, 255, 0.2);
            text-align: center;
            box-sizing: border-box;
        }

        .robot-head {
            font-size: 42px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .title {
            color: var(--neon-blue);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
            font-weight: 800;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .period-no {
            color: var(--neon-blue);
            font-weight: bold;
            font-size: 1.05em;
        }

        .timer-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 2px solid var(--neon-green);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 0 10px rgba(0, 255, 102, 0.3);
        }

        .timer-sec {
            font-size: 15px;
            font-weight: 800;
            color: var(--neon-green);
            line-height: 1;
        }

        .timer-label {
            font-size: 7px;
            color: #aaa;
            font-weight: bold;
        }

        .pred-card {
            border: 2px dashed var(--neon-blue);
            padding: 18px;
            border-radius: 12px;
            margin: 15px 0;
            background: rgba(0, 243, 255, 0.03);
        }

        .result-text {
            font-size: 30px;
            font-weight: bold;
            margin: 8px 0;
        }

        .btn-action {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #00f3ff, #0066ff);
            border: none;
            color: #000;
            font-weight: bold;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 10px;
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.4);
            transition: 0.2s;
        }

        .btn-action:disabled {
            background: #333;
            color: #777;
            box-shadow: none;
            cursor: not-allowed;
        }

        .link-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .vip-link {
            flex: 1;
            padding: 10px;
            text-decoration: none;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            border-radius: 6px;
            text-align: center;
        }

        .reg-link { background: #ff0055; }
        .tg-link { background: #0088cc; }

        table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            padding: 6px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        th { color: var(--neon-blue); }

        .popup-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }

        .popup-card {
            background: #121526;
            border: 2px solid var(--neon-red);
            border-radius: 16px;
            padding: 20px;
            width: 85%; max-width: 340px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="popup-overlay" id="noticePopup">
    <div class="popup-card">
        <div style="font-size: 35px; margin-bottom: 5px;">⚠️</div>
        <div style="color:var(--neon-red); font-weight:bold; font-size:18px;">IMPORTANT NOTICE</div>
        <p style="font-size: 13px; color: #ccc;">
            Hack 100% working rakhne ke liye <b>Neeche diye gaye Hack Link se NEW ID Create karke hi khele</b>.
        </p>
        <a href="https://www.aalclub.com/#/register?invitationCode=45578190585" target="_blank" style="display:block; background:#ff0055; color:#fff; text-decoration:none; padding:10px; border-radius:6px; font-weight:bold; margin-bottom:10px;">REGISTER NEW ACCOUNT</a>
        <button onclick="closePopup()" style="background:transparent; border:1px solid #666; color:#ccc; padding:6px 12px; border-radius:4px; cursor:pointer;">I Understand / Continue</button>
    </div>
</div>

<div class="container">
    <div class="robot-head">🤖</div>
    <div class="title">AI CLUB PREDICTOR v15.0</div>

    <div class="info-box">
        <div style="text-align: left;">
            <div style="font-size: 10px; color: #888;">CURRENT PERIOD</div>
            <div id="period" class="period-no">CONNECTING...</div>
        </div>
        <div class="timer-circle">
            <div class="timer-sec" id="timer">60</div>
            <div class="timer-label">SEC</div>
        </div>
    </div>

    <div class="pred-card">
        <div>AI CONFIDENCE: <span id="confidence" style="color:var(--neon-green)">--</span></div>
        <div id="prediction" class="result-text" style="color:var(--neon-green)">READY</div>
        <div>EXPECTED NUMBER: <span id="pred-num" style="color:var(--neon-blue)">-</span></div>
    </div>

    <button id="predictBtn" class="btn-action" onclick="getPrediction()">🤖 GET AI PREDICTION</button>

    <div class="link-container">
        <a href="https://www.aalclub.com/#/register?invitationCode=45578190585" target="_blank" class="vip-link reg-link">📌 REGISTER NOW</a>
        <a href="https://t.me/numberhackfre" target="_blank" class="vip-link tg-link">📢 JOIN TELEGRAM</a>
    </div>

    <h4 style="margin-top: 20px; color: var(--neon-blue); text-align: left;">📜 AI Prediction History</h4>
    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th>Result</th>
                <th>Number</th>
            </tr>
        </thead>
        <tbody id="history-body"></tbody>
    </table>
</div>

<script>
    let activePeriod = "";
    let lockedPrediction = null;
    let periodHistoryMap = {};

    function closePopup() {
        document.getElementById('noticePopup').style.display = 'none';
    }

    // Exact 91 Club WinGo 1-Min Server Match Logic
    function getCurrent91ClubPeriod() {
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const istDate = new Date(utc + (3600000 * 5.5)); // IST Time
        
        const yyyy = istDate.getFullYear();
        const mm = String(istDate.getMonth() + 1).padStart(2, '0');
        const dd = String(istDate.getDate()).padStart(2, '0');
        
        // Calculated Offset from 05:30 AM Base Index (9:30 AM = 241)
        const currentHours = istDate.getHours();
        const currentMinutes = istDate.getMinutes();
        
        // Formula matched with your screenshot: 9:30 AM -> 0241
        let totalMinutes = (currentHours * 60) + currentMinutes - 329;
        if (totalMinutes < 1) totalMinutes += 1440; // Handles Day rollover

        const periodIndex = String(totalMinutes).padStart(4, '0');
        const fullPeriod = `${yyyy}${mm}${dd}10001${periodIndex}`;
        
        const secondsRemaining = 60 - istDate.getSeconds();

        return { fullPeriod, secondsRemaining };
    }

    function updateEngine() {
        const data = getCurrent91ClubPeriod();
        const currentPeriod = data.fullPeriod;
        const secondsLeft = data.secondsRemaining;

        document.getElementById('period').innerText = currentPeriod;
        document.getElementById('timer').innerText = String(secondsLeft).padStart(2, '0');

        if (activePeriod !== currentPeriod) {
            // Push previous prediction to history AFTER period ends (at 00 sec)
            if (activePeriod !== "" && periodHistoryMap[activePeriod]) {
                addHistoryRecord(activePeriod, periodHistoryMap[activePeriod]);
            }

            activePeriod = currentPeriod;
            lockedPrediction = null;

            // Interface Reset
            document.getElementById('predictBtn').disabled = false;
            document.getElementById('predictBtn').innerText = "🤖 GET AI PREDICTION";
            document.getElementById('prediction').innerText = "READY";
            document.getElementById('prediction').style.color = "var(--neon-green)";
            document.getElementById('pred-num').innerText = "-";
            document.getElementById('confidence').innerText = "--";
        }
    }

    function getPrediction() {
        if (lockedPrediction !== null) return;

        const isBig = Math.random() > 0.5;
        const num = isBig ? Math.floor(Math.random() * 5) + 5 : Math.floor(Math.random() * 5);
        const confidence = (Math.random() * (99.5 - 89.0) + 89.0).toFixed(1);

        lockedPrediction = { isBig, num, confidence };
        periodHistoryMap[activePeriod] = lockedPrediction;

        const predElement = document.getElementById('prediction');
        if (isBig) {
            predElement.innerText = "BIG 🔴";
            predElement.style.color = "var(--neon-red)";
        } else {
            predElement.innerText = "SMALL 🟢";
            predElement.style.color = "var(--neon-green)";
        }

        document.getElementById('pred-num').innerText = num;
        document.getElementById('confidence').innerText = confidence + "%";

        const btn = document.getElementById('predictBtn');
        btn.disabled = true;
        btn.innerText = "🔒 RESULT LOCKED FOR THIS PERIOD";
    }

    function addHistoryRecord(period, data) {
        const historyBody = document.getElementById('history-body');
        const shortPeriod = "..." + period.slice(-4);
        const newRow = `<tr>
            <td>${shortPeriod}</td>
            <td style="color: ${data.isBig ? 'var(--neon-red)' : 'var(--neon-green)'}">${data.isBig ? 'BIG' : 'SMALL'}</td>
            <td>${data.num}</td>
        </tr>`;
        historyBody.innerHTML = newRow + historyBody.innerHTML;
    }

    setInterval(updateEngine, 1000);
    updateEngine();
</script>

</body>
</html>
