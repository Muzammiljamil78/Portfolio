<?php
// 1. Error reporting
error_reporting(0);

// 2. Database Connection
if (file_exists('../db.php')) {
    include('../db.php');
} else if (file_exists('../camp/db.php')) {
    include('../camp/db.php');
} else {
    include('db.php');
}

if (!isset($db) && isset($conn)) { $db = $conn; }
if (!isset($db)) { die("Connection Error"); }

// 3. Clean Input & Fetch Offer
$offer_id = isset($_GET['o']) ? mysqli_real_escape_string($db, $_GET['o']) : '';

$query = "SELECT * FROM offers WHERE offer_id = '$offer_id' AND status = 'active' LIMIT 1";
$run = mysqli_query($db, $query);

if(mysqli_num_rows($run) < 1) {
    echo "<script>window.location.href='https://admincamp.in/o/over.php';</script>";
    exit(); 
}

$rows = mysqli_fetch_array($run);

// 4. Data Preparation
$offer_name = $rows['offer_name'] ?? "Unknown Offer";
$default_logo = "https://admincamp.in/offer/c/logo.jpeg";
$offer_logo_url = !empty($rows['offer_img']) ? htmlspecialchars($rows['offer_img']) : $default_logo;

// 5. Payout Logic
$user_amounts = array_map('trim', explode(',', $rows["user_amounts"] ?? '0'));
$refer_amounts = array_map('trim', explode(',', $rows["refer_amounts"] ?? '0'));
$events = array_map('trim', explode(',', $rows["payble_events"] ?? 'Event'));

$default_user = (float)end($user_amounts);
$default_refer = (float)end($refer_amounts);
$total_payout = $default_user + $default_refer;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Refer&Earn || <?= htmlspecialchars($rows["offer_name"]) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#0f0f23">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6c5ce7;
            --primary-light: #a29bfe;
            --primary-dark: #5a4bd1;
            --accent: #00cec9;
            --success: #00b894;
            --success-light: #55efc4;
            --bg: #0f0f23;
            --bg-card: #1a1a2e;
            --bg-card-2: #16213e;
            --text: #ffffff;
            --text-muted: #b2bec3;
            --glass: rgba(255,255,255,0.05);
            --glass-border: rgba(255,255,255,0.1);
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; outline: none; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex; justify-content: center;
            min-height: 100vh; position: relative; overflow-x: hidden;
        }


        /* === PRELOADER === */
        .preloader { position: fixed; inset: 0; background: var(--bg); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.6s ease, visibility 0.6s ease; }
        .preloader.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        .preloader-logo { width: 80px; height: 80px; border-radius: 20px; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; animation: preloader-pulse 1.5s ease-in-out infinite; position: relative; }
        .preloader-logo::before { content: ''; position: absolute; inset: -4px; border-radius: 24px; background: conic-gradient(from 0deg, var(--primary), var(--accent), var(--primary)); z-index: -1; animation: preloader-spin 2s linear infinite; }
        .preloader-logo::after { content: ''; position: absolute; inset: -2px; border-radius: 22px; background: var(--bg); z-index: -1; }
        .preloader-logo i { font-size: 32px; color: white; }
        @keyframes preloader-pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }
        @keyframes preloader-spin { to{transform:rotate(360deg)} }
        .preloader-text { margin-top: 20px; color: var(--text-muted); font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; animation: preloader-fade 1.5s ease-in-out infinite; }
        @keyframes preloader-fade { 0%,100%{opacity:0.5} 50%{opacity:1} }
        .preloader-bar { width: 200px; height: 3px; background: rgba(255,255,255,0.1); border-radius: 10px; margin-top: 15px; overflow: hidden; }
        .preloader-bar::after { content: ''; display: block; width: 40%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent)); border-radius: 10px; animation: preloader-progress 1.2s ease-in-out infinite; }
        @keyframes preloader-progress { 0%{transform:translateX(-100%)} 100%{transform:translateX(350%)} }

        /* === MESH BG === */
        .mesh-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .mesh-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: orb-drift 20s ease-in-out infinite; }
        .mesh-orb:nth-child(1) { width: 400px; height: 400px; background: var(--primary); top: -100px; left: -100px; }
        .mesh-orb:nth-child(2) { width: 300px; height: 300px; background: var(--accent); bottom: -50px; right: -50px; animation-delay: -5s; }
        .mesh-orb:nth-child(3) { width: 250px; height: 250px; background: #fd79a8; top: 50%; left: 50%; animation-delay: -10s; }
        @keyframes orb-drift { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-30px) scale(1.1)} 50%{transform:translate(-30px,50px) scale(0.9)} 75%{transform:translate(30px,30px) scale(1.05)} }


        /* === CONTAINER === */
        .container { width: 100%; max-width: 480px; min-height: 100vh; position: relative; z-index: 1; padding: 0 0 100px; }

        /* === HEADER === */
        .hero-header { position: relative; padding: 50px 25px 80px; text-align: center; overflow: hidden; }
        .hero-header::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(108,92,231,0.2) 0%, transparent 100%); }
        .hero-header::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80%; height: 1px; background: linear-gradient(90deg, transparent, var(--glass-border), transparent); }
        .header-badge { display: inline-flex; align-items: center; gap: 6px; background: var(--glass); border: 1px solid var(--glass-border); padding: 8px 16px; border-radius: 100px; font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 18px; backdrop-filter: blur(10px); }
        .header-badge .dot { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; animation: live-dot 1.5s infinite; }
        @keyframes live-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(0.7)} }
        .hero-header h1 { font-size: 28px; font-weight: 900; color: white; letter-spacing: -1px; margin-bottom: 10px; line-height: 1.2; }
        .hero-header h1 span { background: linear-gradient(135deg, var(--primary-light), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-header p { color: var(--text-muted); font-size: 14px; font-weight: 500; }

        /* === MAIN CARD === */
        .content-wrap { padding: 0 16px; margin-top: -50px; position: relative; }
        .main-card { background: var(--bg-card); border-radius: 28px; padding: 80px 24px 30px; border: 1px solid var(--glass-border); position: relative; backdrop-filter: blur(20px); overflow: visible; }
        .main-card::before { content: ''; position: absolute; top: 0; left: 20px; right: 20px; height: 1px; background: linear-gradient(90deg, transparent, var(--primary-light), transparent); }

        /* === ANIMATED LOGO === */
        .logo-wrapper { position: absolute; top: -55px; left: 50%; transform: translateX(-50%); z-index: 10; }
        .logo-ring { width: 110px; height: 110px; border-radius: 30px; position: relative; display: flex; align-items: center; justify-content: center; }
        .logo-ring::before { content: ''; position: absolute; inset: 0; border-radius: 30px; padding: 3px; background: conic-gradient(from 0deg, var(--primary), var(--accent), #fd79a8, var(--primary)); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; animation: ring-rotate 3s linear infinite; }
        @keyframes ring-rotate { to { transform: rotate(360deg); } }
        .logo-ring::after { content: ''; position: absolute; inset: 8px; border-radius: 26px; background: var(--bg-card); z-index: 0; }
        .logo-inner { width: 80px; height: 80px; border-radius: 22px; overflow: hidden; position: relative; z-index: 2; box-shadow: 0 10px 40px rgba(108,92,231,0.3); animation: logo-breathe 3s ease-in-out infinite; }
        @keyframes logo-breathe { 0%,100%{transform:scale(1);box-shadow:0 10px 40px rgba(108,92,231,0.3)} 50%{transform:scale(1.05);box-shadow:0 15px 50px rgba(108,92,231,0.5)} }
        .logo-inner img { width: 100%; height: 100%; object-fit: cover; }


        /* === PAGE TITLE === */
        .page-title { font-size: 22px; font-weight: 800; text-align: center; color: white; margin-bottom: 20px; letter-spacing: -0.5px; }

        /* === STATS GRID === */
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 22px; }
        .stat-box { background: var(--bg-card-2); border-radius: 18px; padding: 16px 12px; text-align: center; border: 1px solid var(--glass-border); transition: all 0.3s ease; }
        .stat-box:hover { transform: translateY(-3px); border-color: rgba(108,92,231,0.3); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
        .stat-box.profit { border-color: rgba(0,184,148,0.3); background: rgba(0,184,148,0.08); }
        .stat-label { font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; display: block; }
        .stat-value { font-size: 22px; font-weight: 900; color: white; }
        .stat-value.profit-value { color: var(--success-light); }

        /* === INPUT FIELDS === */
        .input-group { margin-bottom: 16px; position: relative; }
        .input-group label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; padding-left: 4px; }
        .input-group label i { color: var(--primary-light); font-size: 13px; }
        .input-field { width: 100%; background: var(--bg-card-2); border: 2px solid var(--glass-border); padding: 16px 18px; border-radius: 16px; font-size: 15px; font-weight: 600; color: white; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-family: 'Inter', sans-serif; }
        .input-field::placeholder { color: #636e72; }
        .input-field:focus { border-color: var(--primary); background: rgba(108,92,231,0.05); box-shadow: 0 0 0 4px rgba(108,92,231,0.15), 0 8px 25px rgba(108,92,231,0.2); transform: translateY(-2px); }

        /* === UPI SUGGESTIONS === */
        .upi-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card); border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.5); z-index: 100; display: none; margin-top: 5px; overflow: hidden; border: 1px solid var(--glass-border); max-height: 200px; overflow-y: auto; }
        .suggestion-item { padding: 12px 15px; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--text-muted); border-bottom: 1px solid var(--glass-border); transition: all 0.2s; }
        .suggestion-item:last-child { border-bottom: none; }
        .suggestion-item:hover { background: rgba(108,92,231,0.1); color: var(--primary-light); transform: translateX(5px); }


        /* === GENERATE BUTTON === */
        .btn-cta { width: 100%; position: relative; overflow: hidden; background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 50%, var(--accent) 100%); background-size: 200% 200%; color: white; border: none; padding: 18px; border-radius: 16px; font-size: 15px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); font-family: 'Inter', sans-serif; letter-spacing: 0.3px; margin-top: 8px; animation: btn-gradient 4s ease infinite; box-shadow: 0 10px 40px rgba(108,92,231,0.4); }
        @keyframes btn-gradient { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
        .btn-cta::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.6s ease; }
        .btn-cta:hover::before { left: 100%; }
        .btn-cta:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 50px rgba(108,92,231,0.6); }
        .btn-cta:active { transform: translateY(-1px) scale(0.98); }
        .btn-cta:disabled { background: linear-gradient(135deg, #2d3436, #636e72); cursor: not-allowed; box-shadow: none; animation: none; transform: none; }
        .btn-cta:disabled::before { display: none; }
        .btn-cta .btn-icon { width: 28px; height: 28px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .btn-cta:not(:disabled) .btn-icon { animation: icon-pulse 2s infinite; }
        @keyframes icon-pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.2)} }

        /* === CUSTOMIZE BUTTON === */
        .btn-customize { width: 100%; margin-top: 14px; padding: 15px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 16px; color: var(--text-muted); font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease; font-family: 'Inter', sans-serif; cursor: pointer; }
        .btn-customize:hover { border-color: var(--primary); color: var(--primary-light); background: rgba(108,92,231,0.08); transform: translateY(-2px); }

        /* === RESULT BOX === */
        .result-box { display: none; margin-top: 25px; padding: 20px; background: rgba(0,184,148,0.1); border-radius: 20px; border: 1px solid rgba(0,184,148,0.3); text-align: center; animation: slideIn 0.4s ease-out; }
        .result-box.show { display: block; }
        @keyframes slideIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .result-header { font-size: 12px; color: var(--success-light); font-weight: 800; text-transform: uppercase; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .result-link { width: 100%; border: none; background: var(--bg-card-2); padding: 14px; border-radius: 12px; text-align: center; font-weight: 700; color: var(--success-light); font-size: 13px; font-family: 'Inter', sans-serif; border: 1px solid var(--glass-border); }


        /* === MODAL === */
        .modal-content { border-radius: 24px; border: 1px solid var(--glass-border); background: var(--bg-card); box-shadow: 0 25px 60px rgba(0,0,0,0.6); }
        .modal-body { padding: 24px; }
        .modal-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header-custom h5 { font-weight: 900; color: white; margin: 0; font-size: 18px; }
        .btn-close { filter: invert(1); }
        .max-payout-box { background: var(--bg-card-2); padding: 20px; border-radius: 18px; text-align: center; margin-bottom: 18px; border: 1px solid var(--glass-border); }
        .max-payout-label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .max-payout-value { font-size: 28px; font-weight: 900; background: linear-gradient(135deg, var(--primary-light), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .profit-display { display: flex; justify-content: space-between; padding: 14px 16px; background: var(--bg-card-2); border-radius: 12px; margin-top: 14px; font-weight: 700; font-size: 14px; border: 1px solid var(--glass-border); }
        .profit-display span:first-child { color: var(--text-muted); }
        .profit-display span:last-child { color: var(--success-light); font-weight: 900; }

        /* === FLOAT TRACK === */
        .float-track { position: fixed; bottom: 25px; right: 25px; width: 56px; height: 56px; background: linear-gradient(135deg, var(--primary), #8b5cf6); color: white; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 8px 30px rgba(108,92,231,0.4); z-index: 1000; text-decoration: none; animation: tg-float 3s ease-in-out infinite; transition: all 0.3s ease; }
        @keyframes tg-float { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-8px) rotate(3deg)} }
        .float-track:hover { transform: scale(1.1) translateY(-5px); box-shadow: 0 12px 40px rgba(108,92,231,0.6); border-radius: 50%; }

        /* === TOAST === */
        #toast-box { position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%); background: var(--bg-card); color: white; padding: 14px 28px; border-radius: 14px; font-size: 14px; font-weight: 700; z-index: 3000; border: 1px solid var(--glass-border); box-shadow: 0 20px 50px rgba(0,0,0,0.5); backdrop-filter: blur(20px); transition: bottom 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        #toast-box.show { bottom: 30px; }

        /* === SECURITY FOOTER === */
        .security-footer { text-align: center; font-size: 12px; color: #636e72; margin-top: 35px; padding: 20px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .security-footer i { color: var(--success); font-size: 14px; }

        /* === RESPONSIVE === */
        @media (max-width: 360px) { .hero-header h1{font-size:24px} .page-title{font-size:18px} .stat-value{font-size:18px} }
    </style>
</head>

<body>

<!-- Preloader -->
<div class="preloader" id="preloader">
    <div class="preloader-logo"><i class="fas fa-link"></i></div>
    <p class="preloader-text">Loading</p>
    <div class="preloader-bar"></div>
</div>

<!-- Mesh Background -->
<div class="mesh-bg">
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
</div>

<div class="container">
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="header-badge"><span class="dot"></span> Refer & Earn</div>
        <h1>Create <span>Link</span> &<br>Share with Friends</h1>
        <p>Earn commission on every referral</p>
    </div>

    <!-- Main Content Card -->
    <div class="content-wrap">
        <div class="main-card">
            <!-- Animated Logo -->
            <div class="logo-wrapper">
                <div class="logo-ring">
                    <div class="logo-inner">
                        <img src="<?= $offer_logo_url ?>" alt="logo">
                    </div>
                </div>
            </div>

            <h3 class="page-title"><?= htmlspecialchars($rows["offer_name"]) ?></h3>

            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-label">User Gets</span>
                    <span class="stat-value" id="viewUserAmt"><?= $default_user ?></span>
                </div>
                <div class="stat-box profit">
                    <span class="stat-label">Your Profit</span>
                    <span class="stat-value profit-value"><span id="viewMyProfit"><?= $default_refer ?></span></span>
                </div>
            </div>


            <div class="input-group">
                <label><i class="fas fa-wallet"></i> Your UPI ID</label>
                <input type="text" id="referUPI" class="input-field" placeholder="Enter Number or ID" autocomplete="off">
                <div id="upiList" class="upi-suggestions"></div>
            </div>

            <div class="input-group">
                <label><i class="fab fa-telegram"></i> Channel Link (Optional)</label>
                <input type="text" id="tgLink" class="input-field" placeholder="t.me/channel">
            </div>

            <button id="genBtn" class="btn-cta">
                <span>Generate Link</span>
                <div class="btn-icon"><i class="fas fa-magic"></i></div>
            </button>

            <button class="btn-customize" data-bs-toggle="modal" data-bs-target="#customModal">
                <i class="fas fa-sliders-h"></i> Customize Amount
            </button>

            <div class="result-box" id="resultArea">
                <div class="result-header"><i class="fas fa-check-circle"></i> Link Ready & Copied</div>
                <input type="text" id="finalLink" class="result-link" readonly>
            </div>
        </div>
    </div>

    <div class="security-footer">
        <i class="fas fa-shield-halved"></i> 256-bit Secure Connection
    </div>
</div>

<a href="/track?o=<?= $offer_id ?>" class="float-track">
    <i class="fas fa-chart-line"></i>
</a>


<!-- Modal -->
<div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-header-custom">
                    <h5>Customize Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="max-payout-box">
                    <div class="max-payout-label">Max Payout</div>
                    <div class="max-payout-value"><?= $total_payout ?></div>
                </div>
                <div class="input-group">
                    <label>Amount for User</label>
                    <input type="number" id="modalUserAmt" class="input-field" value="<?= $default_user ?>" min="0" max="<?= $total_payout ?>" oninput="calculateLiveProfit(this.value)">
                </div>
                <div class="profit-display">
                    <span>Your Net Profit:</span>
                    <span><span id="modalProfit"><?= $default_refer ?></span></span>
                </div>
                <button type="button" class="btn-cta" style="margin-top: 20px;" data-bs-dismiss="modal" onclick="saveCustom()">
                    <span>Apply Settings</span>
                    <div class="btn-icon"><i class="fas fa-check"></i></div>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast-box">Message</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Preloader
    window.addEventListener('load', function() {
        setTimeout(function() { document.getElementById('preloader').classList.add('hidden'); }, 1200);
    });

    const totalPayout = <?= $total_payout ?>;
    const upiInput = document.getElementById('referUPI');
    const upiList = document.getElementById('upiList');
    const handles = ['@okaxis', '@ybl', '@upi', '@ibl', '@okhdfcbank', '@okicici', '@fam', '@ptsbi', '@ptyes', '@naviaxis'];
    
    function calculateLiveProfit(val) {
        let userAmt = parseFloat(val) || 0;
        const modalProfit = document.getElementById("modalProfit");
        const modalInput = document.getElementById("modalUserAmt");
        if (userAmt > totalPayout) { userAmt = totalPayout; modalInput.value = totalPayout; showToast("Max limit reached"); }
        let profit = totalPayout - userAmt;
        modalProfit.innerText = profit.toFixed(2);
    }

    upiInput.addEventListener('input', function() {
        let val = this.value.trim();
        upiList.innerHTML = '';
        if (val.length > 0 && !val.includes('@')) {
            handles.forEach(handle => {
                let div = document.createElement('div');
                div.className = 'suggestion-item';
                div.innerHTML = '<i class="fas fa-at" style="margin-right:6px;color:var(--primary-light)"></i>' + val + handle;
                div.onclick = function() { upiInput.value = val + handle; upiList.style.display = 'none'; };
                upiList.appendChild(div);
            });
            upiList.style.display = 'block';
        } else { upiList.style.display = 'none'; }
    });

    document.addEventListener('click', (e) => { if(e.target !== upiInput) upiList.style.display = 'none'; });

    function saveCustom() {
        const userAmt = parseFloat(document.getElementById("modalUserAmt").value) || 0;
        document.getElementById("viewUserAmt").innerText = userAmt;
        document.getElementById("viewMyProfit").innerText = (totalPayout - userAmt).toFixed(2);
        showToast("Amount Settings Applied!");
    }

    function showToast(msg) {
        const x = document.getElementById("toast-box");
        x.innerText = msg;
        x.classList.add("show");
        setTimeout(() => { x.classList.remove("show"); }, 3000);
    }

    document.getElementById("genBtn").addEventListener("click", function () {
        const upi = upiInput.value.trim();
        const upiRegex = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/;
        if(!upiRegex.test(upi)) { showToast("Invalid UPI ID Format"); return; }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Checking UPI...</span>';

        const formData = new FormData();
        formData.append("campaign", "<?= $offer_id ?>");
        formData.append("num", upi);
        formData.append("tgLink", document.getElementById("tgLink").value.trim());
        formData.append("user_payouts", document.getElementById("viewUserAmt").innerText.replace('₹',''));
        formData.append("refer_payouts", document.getElementById("viewMyProfit").innerText);
        formData.append("events_list", "<?= end($events) ?>");

        fetch("red.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                document.getElementById("finalLink").value = data.url;
                document.getElementById("resultArea").classList.add("show");
                navigator.clipboard.writeText(data.url);
                showToast('Link Copied!');
                btn.innerHTML = "<span>Generated!</span> <div class='btn-icon'><i class='fas fa-check'></i></div>";
            } else {
                showToast(data.message || "Error");
                btn.disabled = false;
                btn.innerHTML = "<span>Generate Link</span> <div class='btn-icon'><i class='fas fa-magic'></i></div>";
            }
        }).catch(() => {
            showToast("Network Error");
            btn.disabled = false;
            btn.innerHTML = "<span>Generate Link</span> <div class='btn-icon'><i class='fas fa-magic'></i></div>";
        });
    });
</script>
</body>
</html>