<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Koneksi Terputus — Klinik247</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 40px 30px;
            max-width: 520px;
            z-index: 2;
            position: relative;
        }

        /* ===== Animated Cloud-Server-Cable Disconnect Icon ===== */
        .icon-wrap {
            position: relative;
            width: 220px;
            height: 200px;
            margin: 0 auto 30px;
        }

        .server {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 80px;
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }

        .server-bar {
            width: 80px;
            height: 6px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 3px;
            animation: blink 1.5s infinite;
        }
        .server-bar:nth-child(2) { animation-delay: .3s; }
        .server-bar:nth-child(3) { animation-delay: .6s; }

        @keyframes blink {
            0%, 100% { opacity: 0.3; background: #ef4444; }
            50% { opacity: 1; background: #ef4444; }
        }

        /* Broken connection line with electric flicker */
        .cable {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            z-index: 1;
        }
        .cable-left {
            flex: 1;
            height: 100%;
            background: linear-gradient(to right, transparent, #fbbf24);
            border-radius: 2px;
            animation: sparkLeft 1.2s infinite;
        }
        .cable-right {
            flex: 1;
            height: 100%;
            background: linear-gradient(to left, transparent, #fbbf24);
            border-radius: 2px;
            animation: sparkRight 1.2s infinite;
        }
        .cable-gap {
            width: 60px;
            position: relative;
        }

        @keyframes sparkLeft {
            0%, 100% { opacity: .6; }
            50% { opacity: 1; box-shadow: 0 0 12px #fbbf24; }
        }
        @keyframes sparkRight {
            0%, 100% { opacity: .6; }
            50% { opacity: 1; box-shadow: 0 0 12px #fbbf24; }
        }

        /* Pulse rings around server (radar effect) */
        .pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 80px;
            border-radius: 12px;
            border: 2px solid rgba(239, 68, 68, 0.4);
            animation: pulseExpand 2s infinite;
        }
        .pulse:nth-child(2) { animation-delay: .7s; }

        @keyframes pulseExpand {
            0%   { transform: translate(-50%, -50%) scale(1);   opacity: .8; }
            100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; }
        }

        /* Disconnect bolt animation */
        .bolt {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 40px;
            animation: shake 1s infinite;
        }
        @keyframes shake {
            0%, 100% { transform: translate(-50%, -50%) rotate(-15deg); }
            50%      { transform: translate(-50%, -50%) rotate(15deg); }
        }

        /* ===== Text + Buttons ===== */
        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        p {
            font-size: 15px;
            opacity: 0.85;
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse-dot 1s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50%      { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }

        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        button, a.btn {
            padding: 12px 24px;
            border: 0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }
        .btn-primary {
            background: #fff;
            color: #1e40af;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.2); }

        .retry-info {
            margin-top: 16px;
            font-size: 12px;
            opacity: 0.6;
        }
        #retryCountdown { font-weight: 700; color: #fbbf24; }

        /* Floating bg particles */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            33%      { transform: translateY(-30px) translateX(20px); }
            66%      { transform: translateY(20px) translateX(-15px); }
        }

        @media (max-width: 480px) {
            h1 { font-size: 22px; }
            .icon-wrap { width: 180px; height: 160px; }
            .server { width: 100px; height: 70px; }
        }
    </style>
</head>
<body>
    {{-- Floating background particles --}}
    <div class="particle" style="top:10%; left:15%; width:60px; height:60px; animation-delay:0s;"></div>
    <div class="particle" style="top:70%; left:80%; width:80px; height:80px; animation-delay:2s;"></div>
    <div class="particle" style="top:80%; left:10%; width:40px; height:40px; animation-delay:4s;"></div>
    <div class="particle" style="top:20%; left:80%; width:50px; height:50px; animation-delay:6s;"></div>

    <div class="container">
        {{-- Animated icon --}}
        <div class="icon-wrap">
            <div class="pulse"></div>
            <div class="pulse"></div>

            <div class="cable">
                <div class="cable-left"></div>
                <div class="cable-gap">
                    <div class="bolt">⚡</div>
                </div>
                <div class="cable-right"></div>
            </div>

            <div class="server">
                <div class="server-bar"></div>
                <div class="server-bar"></div>
                <div class="server-bar"></div>
            </div>
        </div>

        <div class="status-badge">
            <span class="status-dot"></span>
            Connection Lost
        </div>

        <h1>Koneksi Database Terputus</h1>
        <p>Server database tidak dapat dijangkau saat ini.</p>
        <p>Sistem akan mencoba kembali otomatis dalam <span id="retryCountdown">10</span> detik.</p>

        <div class="actions">
            <button class="btn-primary" onclick="window.location.reload()">
                🔄 Coba Ulang Sekarang
            </button>
            <a href="javascript:history.back()" class="btn btn-ghost">
                ← Kembali
            </a>
        </div>

        <div class="retry-info">
            Jika masalah berlanjut, hubungi admin sistem.<br>
            <small>Error code: DB_CONNECTION_LOST · {{ now()->format('Y-m-d H:i:s') }}</small>
        </div>
    </div>

    <script>
        // Auto-retry countdown
        let seconds = 10;
        const countdownEl = document.getElementById('retryCountdown');
        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.reload();
            }
        }, 1000);
    </script>
</body>
</html>
