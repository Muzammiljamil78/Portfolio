<?php
include("db.php"); 

// 1. Fetch Dynamic Data for Ticker
$ticker_query = mysqli_query($conn, "SELECT offer_name, user_amounts FROM `offers` WHERE `status`='active' ORDER BY RAND() LIMIT 12");
$ticker_items = array();
while ($row = mysqli_fetch_array($ticker_query)) {
    $amounts = explode(',', $row['user_amounts']);
    $ticker_items[] = ['name' => $row['offer_name'], 'amount' => end($amounts)];
}
$random_names = ['Aman', 'Suresh', 'Priya', 'Rahul', 'Deepak', 'Anjali', 'Vikram', 'Sneha', 'Rohit', 'Ishaan', 'Karan', 'Mehak', 'Tushar', 'Sonia'];

// 2. Fetch All Active Offers
$camps = mysqli_query($conn, "SELECT * FROM `offers` WHERE `status`='active' ORDER BY `created_at` DESC");
$campaigns = array();
while ($res = mysqli_fetch_array($camps)) {
    $amounts = explode(',', $res['user_amounts']);
    $campaigns[] = [
        'title' => $res['offer_name'],
        'img'   => $res['offer_img'],
        'step'  => $res['step'],
        'id'    => $res['offer_id'],
        'amo'   => end($amounts)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdminCamp | Fast Performance Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #6c5ce7;
            --primary-light: #a29bfe;
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

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* === PRELOADER === */
        .preloader {
            position: fixed; inset: 0; background: var(--bg);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            z-index: 9999; transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        .preloader.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        .preloader-logo {
            width: 80px; height: 80px; border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            animation: preloader-pulse 1.5s ease-in-out infinite; position: relative;
        }
        .preloader-logo::before {
            content: ''; position: absolute; inset: -4px; border-radius: 24px;
            background: conic-gradient(from 0deg, var(--primary), var(--accent), var(--primary));
            z-index: -1; animation: preloader-spin 2s linear infinite;
        }
        .preloader-logo::after {
            content: ''; position: absolute; inset: -2px; border-radius: 22px; background: var(--bg); z-index: -1;
        }
        .preloader-logo i { font-size: 32px; color: white; }
        @keyframes preloader-pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }
        @keyframes preloader-spin { to{transform:rotate(360deg)} }
        .preloader-text {
            margin-top: 20px; color: var(--text-muted); font-weight: 600;
            font-size: 14px; letter-spacing: 2px; text-transform: uppercase;
            animation: preloader-fade 1.5s ease-in-out infinite;
        }
        @keyframes preloader-fade { 0%,100%{opacity:0.5} 50%{opacity:1} }
        .preloader-bar { width: 200px; height: 3px; background: rgba(255,255,255,0.1); border-radius: 10px; margin-top: 15px; overflow: hidden; }
        .preloader-bar::after {
            content: ''; display: block; width: 40%; height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 10px; animation: preloader-progress 1.2s ease-in-out infinite;
        }
        @keyframes preloader-progress { 0%{transform:translateX(-100%)} 100%{transform:translateX(350%)} }

        /* === MESH BG === */
        .mesh-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .mesh-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: orb-drift 20s ease-in-out infinite; }
        .mesh-orb:nth-child(1) { width: 400px; height: 400px; background: var(--primary); top: -100px; left: -100px; }
        .mesh-orb:nth-child(2) { width: 300px; height: 300px; background: var(--accent); bottom: -50px; right: -50px; animation-delay: -5s; }
        .mesh-orb:nth-child(3) { width: 250px; height: 250px; background: #fd79a8; top: 50%; left: 50%; animation-delay: -10s; }
        @keyframes orb-drift {
            0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-30px) scale(1.1)}
            50%{transform:translate(-30px,50px) scale(0.9)} 75%{transform:translate(30px,30px) scale(1.05)}
        }

        /* === TICKER === */
        .payout-ticker {
            background: rgba(0,0,0,0.5); backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            padding: 12px 0; font-size: 13px; overflow: hidden; white-space: nowrap;
            position: relative; z-index: 10;
        }
        .ticker-content { display: inline-block; animation: ticker 35s linear infinite; }
        .ticker-item { display: inline-block; margin-right: 40px; color: var(--text-muted); }
        .ticker-item span { color: var(--success-light); font-weight: 800; }
        .ticker-item i { color: var(--success); margin-right: 5px; }
        @keyframes ticker { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }

        /* === NAVBAR === */
        .nav-bar {
            background: rgba(26,26,46,0.9); backdrop-filter: blur(20px);
            padding: 14px 5%; display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 2000;
            border-bottom: 1px solid var(--glass-border);
        }
        .logo-box { display: flex; align-items: center; gap: 10px; text-decoration: none; color: white; }
        .logo-box img { height: 35px; border-radius: 10px; }
        .logo-box b { font-size: 18px; font-weight: 900; letter-spacing: -0.5px; }
        .nav-support {
            color: var(--accent); text-decoration: none; font-weight: 700; font-size: 12px;
            padding: 8px 16px; border: 1px solid rgba(0,206,201,0.3); border-radius: 100px;
            background: rgba(0,206,201,0.08); transition: all 0.3s ease;
        }
        .nav-support:hover { background: rgba(0,206,201,0.15); transform: translateY(-1px); }

        /* === HERO === */
        .hero {
            position: relative; padding: 70px 5% 120px; text-align: center; z-index: 1;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(108,92,231,0.15) 0%, transparent 100%);
            pointer-events: none;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--glass); border: 1px solid var(--glass-border);
            padding: 8px 16px; border-radius: 100px; font-size: 11px;
            font-weight: 700; color: var(--accent); text-transform: uppercase;
            letter-spacing: 1.5px; margin-bottom: 20px; backdrop-filter: blur(10px);
        }
        .hero-badge .dot { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; animation: live-dot 1.5s infinite; }
        @keyframes live-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(0.7)} }

        .hero h1 { font-size: 42px; font-weight: 900; letter-spacing: -2px; margin-bottom: 15px; line-height: 1.1; }
        .hero h1 span {
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero p { color: var(--text-muted); font-size: 15px; font-weight: 500; max-width: 400px; margin: 0 auto; }

        /* === STATS === */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
            max-width: 600px; margin: -60px auto 30px; padding: 0 16px; position: relative; z-index: 2;
        }
        .stat-card {
            background: var(--bg-card); border: 1px solid var(--glass-border);
            padding: 20px 12px; border-radius: 20px; text-align: center;
            backdrop-filter: blur(10px); transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: rgba(108,92,231,0.3); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .stat-card h2 { font-size: 22px; font-weight: 900; margin-bottom: 4px;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .stat-card p { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        /* === SECTION TITLE === */
        .section-title {
            text-align: center; padding: 30px 16px 20px; position: relative; z-index: 1;
        }
        .section-title h2 { font-size: 20px; font-weight: 800; color: white; margin-bottom: 6px; }
        .section-title p { font-size: 13px; color: var(--text-muted); }

        /* === OFFER GRID === */
        .offer-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;
            padding: 0 16px 40px; position: relative; z-index: 1; max-width: 700px; margin: 0 auto;
        }

        .offer-card {
            background: var(--bg-card); border: 1px solid var(--glass-border);
            border-radius: 22px; padding: 18px 14px; display: flex; flex-direction: column;
            align-items: center; transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .offer-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            opacity: 0; transition: opacity 0.3s;
        }
        .offer-card:hover { transform: translateY(-5px); border-color: rgba(108,92,231,0.4); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .offer-card:hover::before { opacity: 1; }
        .offer-card:active { transform: scale(0.97); }

        /* Animated Logo Ring for Offer Cards */
        .offer-logo-wrapper {
            position: relative; width: 80px; height: 80px; margin: 0 auto 12px;
        }
        .offer-logo-ring {
            width: 80px; height: 80px; border-radius: 22px; position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .offer-logo-ring::before {
            content: ''; position: absolute; inset: 0; border-radius: 22px; padding: 2.5px;
            background: conic-gradient(from 0deg, var(--primary), var(--accent), #fd79a8, var(--primary));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            animation: offer-ring-rotate 3s linear infinite;
        }
        @keyframes offer-ring-rotate { to { transform: rotate(360deg); } }
        .offer-logo-ring::after {
            content: ''; position: absolute; inset: 6px; border-radius: 18px;
            background: var(--bg-card); z-index: 0;
        }
        .offer-logo-inner {
            width: 58px; height: 58px; border-radius: 16px; overflow: hidden;
            position: relative; z-index: 2;
            box-shadow: 0 8px 25px rgba(108,92,231,0.3);
            animation: offer-logo-breathe 3s ease-in-out infinite;
        }
        @keyframes offer-logo-breathe {
            0%,100% { transform: scale(1); box-shadow: 0 8px 25px rgba(108,92,231,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 12px 35px rgba(108,92,231,0.5); }
        }
        .offer-logo-inner img { width: 100%; height: 100%; object-fit: cover; }

        .offer-payout {
            background: linear-gradient(135deg, rgba(0,184,148,0.15), rgba(85,239,196,0.08));
            border: 1px solid rgba(0,184,148,0.3); color: var(--success-light);
            font-weight: 900; font-size: 15px; padding: 6px 16px;
            border-radius: 100px; margin-bottom: 10px; position: relative; overflow: hidden;
        }
        .offer-payout::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 200%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer { 0%{transform:translateX(-50%)} 100%{transform:translateX(50%)} }

        .offer-card h3 {
            font-size: 13px; font-weight: 800; text-align: center; margin-bottom: 6px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; color: white;
        }
        .offer-card .desc {
            font-size: 10px; color: var(--text-muted); text-align: center;
            height: 26px; overflow: hidden; line-height: 1.3; margin-bottom: 12px;
        }

        .btn-start {
            width: 100%; background: linear-gradient(135deg, var(--primary), #8b5cf6);
            color: white; text-align: center; text-decoration: none;
            padding: 10px; border-radius: 12px; font-weight: 800; font-size: 12px;
            letter-spacing: 0.3px; transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(108,92,231,0.3);
        }
        .btn-start:hover { box-shadow: 0 8px 30px rgba(108,92,231,0.5); transform: translateY(-2px); }

        /* === TELEGRAM FLOAT === */
        .tg-float {
            position: fixed; bottom: 25px; right: 25px; width: 56px; height: 56px;
            background: linear-gradient(135deg, #0088cc, #00b4d8); color: white;
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 24px; box-shadow: 0 8px 30px rgba(0,136,204,0.4);
            z-index: 1000; text-decoration: none; animation: tg-float 3s ease-in-out infinite;
            transition: all 0.3s ease;
        }
        @keyframes tg-float { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-8px) rotate(3deg)} }
        .tg-float:hover { transform: scale(1.1) translateY(-5px); box-shadow: 0 12px 40px rgba(0,136,204,0.6); border-radius: 50%; }

        /* === FOOTER === */
        .footer {
            background: var(--bg-card); border-top: 1px solid var(--glass-border);
            padding: 30px 20px; text-align: center; font-size: 12px; color: #636e72;
        }
        .footer i { color: var(--primary-light); margin: 0 4px; }

        /* === RESPONSIVE === */
        @media (min-width: 768px) {
            .offer-grid { grid-template-columns: repeat(3, 1fr); max-width: 900px; }
            .hero h1 { font-size: 56px; }
        }
        @media (min-width: 1024px) {
            .offer-grid { grid-template-columns: repeat(4, 1fr); max-width: 1100px; }
        }
    </style>
</head>
<body>

<!-- Preloader -->
<div class="preloader" id="preloader">
    <div class="preloader-logo"><i class="fas fa-bolt"></i></div>
    <p class="preloader-text">Loading</p>
    <div class="preloader-bar"></div>
</div>

<!-- Mesh Background -->
<div class="mesh-bg">
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
</div>

<!-- Ticker -->
<div class="payout-ticker">
    <div class="ticker-content">
        <?php foreach ($ticker_items as $t_item): 
            $randName = $random_names[array_rand($random_names)];
        ?>
            <div class="ticker-item">
                <i class="fas fa-check-circle"></i> 
                <?= $randName ?> earned <span>&#8377;<?= $t_item['amount'] ?></span> from <b><?= htmlspecialchars($t_item['name']) ?></b>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Navbar -->
<nav class="nav-bar">
    <a href="/" class="logo-box">
        <img src="https://admincamp.in/logo.jpeg" alt="Logo">
        <b>ADMINCAMP</b>
    </a>
    <a href="https://t.me/AskForAdmin_bot" class="nav-support">
        <i class="fas fa-headset"></i> SUPPORT
    </a>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-badge"><span class="dot"></span> Live Network</div>
    <h1>Fastest <span>Payouts</span><br>in India</h1>
    <p>India's #1 Affiliate Network for smart publishers. Complete offers & earn instant cashback.</p>
</section>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h2>12k+</h2>
        <p>Active Users</p>
    </div>
    <div class="stat-card">
        <h2>500+</h2>
        <p>Offers</p>
    </div>
    <div class="stat-card">
        <h2>LIVE</h2>
        <p>Tracking</p>
    </div>
</div>

<!-- Section Title -->
<div class="section-title">
    <h2>Available Offers</h2>
    <p>Complete & earn instant rewards</p>
</div>

<!-- Offer Grid -->
<div class="offer-grid">
    <?php foreach ($campaigns as $camp): ?>
    <div class="offer-card">
        <div class="offer-logo-wrapper">
            <div class="offer-logo-ring">
                <div class="offer-logo-inner">
                    <img src="<?= htmlspecialchars($camp['img']) ?>" onerror="this.src='https://admincamp.in/logo.jpeg'">
                </div>
            </div>
        </div>
        <div class="offer-payout">&#8377;<?= $camp['amo'] ?></div>
        <h3><?= htmlspecialchars($camp['title']) ?></h3>
        <p class="desc"><?= htmlspecialchars($camp['step']) ?></p>
        <a href="o/?o=<?= $camp['id'] ?>" class="btn-start">START NOW</a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Telegram Float -->
<a href="https://t.me/+YxtpxT2Vwhg2Njc1" class="tg-float">
    <i class="fab fa-telegram-plane"></i>
</a>

<!-- Footer -->
<footer class="footer">
    <i class="fas fa-shield-halved"></i> &copy; 2026 AdminCamp Performance Network. All rights reserved.
</footer>

<script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.getElementById('preloader').classList.add('hidden');
        }, 1200);
    });
</script>

</body>
</html>
