<?php
// ... (PHP Logic same as before)
include("../camp/db.php");
$offer_id   = $_GET["o"]   ?? '';
$refer_code = $_GET["ref"] ?? '34opkw';

$sql_offer = "SELECT * FROM offers WHERE offer_id = ?";
$stmt_offer = mysqli_prepare($db, $sql_offer);
mysqli_stmt_bind_param($stmt_offer, 's', $offer_id);
mysqli_stmt_execute($stmt_offer);
$result_offer = mysqli_stmt_get_result($stmt_offer);
$rows = mysqli_fetch_assoc($result_offer);

if (!$rows) { echo "<script>alert('Campaign Not Found');window.location.href='/error.php';</script>"; exit; }

if (isset($rows['status']) && ($rows['status'] == 'pause' || $rows['status'] == 'inactive')) {
    header("Location: over.php");
    exit;
}

$sql_refer = "SELECT * FROM refer WHERE refer_code = ?";
$stmt_refer = mysqli_prepare($db, $sql_refer);
mysqli_stmt_bind_param($stmt_refer, 's', $refer_code);
mysqli_stmt_execute($stmt_refer);
$result_refer = mysqli_stmt_get_result($stmt_refer);
$roww = mysqli_fetch_assoc($result_refer);

$amount_string = ($roww && !empty($roww['userPo'])) ? $roww['userPo'] : $rows['user_amounts'];
$amounts_array = explode(',', $amount_string);
$userAmount    = htmlspecialchars(trim(end($amounts_array)));

$default_logo = "https://admincamp.in/logo.jpeg";
$offer_logo_url = (!empty($rows['offer_img'])) ? htmlspecialchars($rows['offer_img']) : $default_logo;

$final_tg_link = (!empty($roww['link'])) ? $roww['link'] : 'https://t.me/+YxtpxT2Vwhg2Njc1';
$offer_name  = htmlspecialchars($rows["offer_name"]);
$offer_steps = array_filter(array_map('trim', explode(',', $rows["step"])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $offer_name ?> | AdminCamp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #6c5ce7;
            --primary-light: #a29bfe;
            --primary-dark: #5a4bd1;
            --accent: #00cec9;
            --accent-dark: #00b894;
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

        * { 
            box-sizing: border-box; 
            -webkit-tap-highlight-color: transparent; 
            outline: none; 
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex; 
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* === PRELOADER === */
        .preloader {
            position: fixed;
            inset: 0;
            background: var(--bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }

        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .preloader-logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            animation: preloader-pulse 1.5s ease-in-out infinite;
            position: relative;
        }

        .preloader-logo::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 24px;
            background: conic-gradient(from 0deg, var(--primary), var(--accent), var(--primary));
            z-index: -1;
            animation: preloader-spin 2s linear infinite;
        }

        .preloader-logo::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 22px;
            background: var(--bg);
            z-index: -1;
        }


        .preloader-logo i {
            font-size: 32px;
            color: white;
        }

        @keyframes preloader-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes preloader-spin {
            to { transform: rotate(360deg); }
        }

        .preloader-text {
            margin-top: 20px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: preloader-fade 1.5s ease-in-out infinite;
        }

        @keyframes preloader-fade {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .preloader-bar {
            width: 200px;
            height: 3px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-top: 15px;
            overflow: hidden;
        }

        .preloader-bar::after {
            content: '';
            display: block;
            width: 40%;
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 10px;
            animation: preloader-progress 1.2s ease-in-out infinite;
        }

        @keyframes preloader-progress {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }

        /* === ANIMATED MESH BACKGROUND === */
        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .mesh-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: orb-drift 20s ease-in-out infinite;
        }

        .mesh-orb:nth-child(1) {
            width: 400px; height: 400px;
            background: var(--primary);
            top: -100px; left: -100px;
            animation-delay: 0s;
        }

        .mesh-orb:nth-child(2) {
            width: 300px; height: 300px;
            background: var(--accent);
            bottom: -50px; right: -50px;
            animation-delay: -5s;
        }

        .mesh-orb:nth-child(3) {
            width: 250px; height: 250px;
            background: #fd79a8;
            top: 50%; left: 50%;
            animation-delay: -10s;
        }

        @keyframes orb-drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, -30px) scale(1.1); }
            50% { transform: translate(-30px, 50px) scale(0.9); }
            75% { transform: translate(30px, 30px) scale(1.05); }
        }


        /* === CONTAINER === */
        .container { 
            width: 100%; 
            max-width: 480px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
            padding: 0 0 100px;
        }

        /* === HEADER === */
        .hero-header {
            position: relative;
            padding: 50px 25px 80px;
            text-align: center;
            overflow: hidden;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(108,92,231,0.2) 0%, transparent 100%);
        }

        .hero-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 18px;
            backdrop-filter: blur(10px);
        }

        .header-badge .dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: live-dot 1.5s infinite;
        }

        @keyframes live-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.7); }
        }

        .hero-header h1 {
            font-size: 28px;
            font-weight: 900;
            color: white;
            letter-spacing: -1px;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .hero-header h1 span {
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-header p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }


        /* === MAIN CARD === */
        .content-wrap {
            padding: 0 16px;
            margin-top: -50px;
            position: relative;
        }

        .main-card {
            background: var(--bg-card);
            border-radius: 28px;
            padding: 80px 24px 30px;
            border: 1px solid var(--glass-border);
            position: relative;
            backdrop-filter: blur(20px);
            overflow: visible;
        }

        .main-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20px;
            right: 20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
        }

        /* === ANIMATED LOGO === */
        .logo-wrapper {
            position: absolute;
            top: -55px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .logo-ring {
            width: 110px;
            height: 110px;
            border-radius: 30px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-ring::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 30px;
            padding: 3px;
            background: conic-gradient(from 0deg, var(--primary), var(--accent), #fd79a8, var(--primary));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: ring-rotate 3s linear infinite;
        }

        @keyframes ring-rotate {
            to { transform: rotate(360deg); }
        }

        .logo-ring::after {
            content: '';
            position: absolute;
            inset: 8px;
            border-radius: 26px;
            background: var(--bg-card);
            z-index: 0;
        }

        .logo-inner {
            width: 80px;
            height: 80px;
            border-radius: 22px;
            overflow: hidden;
            position: relative;
            z-index: 2;
            box-shadow: 0 10px 40px rgba(108,92,231,0.3);
            animation: logo-breathe 3s ease-in-out infinite;
        }

        @keyframes logo-breathe {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 40px rgba(108,92,231,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 15px 50px rgba(108,92,231,0.5); }
        }

        .logo-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* === OFFER INFO === */
        .offer-name {
            font-size: 22px;
            font-weight: 800;
            text-align: center;
            color: white;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .reward-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 0 auto 30px;
            width: fit-content;
            padding: 12px 28px;
            background: linear-gradient(135deg, rgba(0,184,148,0.15), rgba(85,239,196,0.08));
            border: 1px solid rgba(0,184,148,0.3);
            border-radius: 100px;
            position: relative;
            overflow: hidden;
        }

        .reward-pill::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: reward-shine 3s infinite;
        }

        @keyframes reward-shine {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(50%); }
        }

        .reward-pill .coin-icon {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #ffd32a, #f39c12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
            animation: coin-rotate 2s ease-in-out infinite;
            box-shadow: 0 4px 15px rgba(255,211,42,0.3);
        }

        @keyframes coin-rotate {
            0%, 100% { transform: rotateY(0deg); }
            50% { transform: rotateY(180deg); }
        }

        .reward-pill .amount {
            font-size: 20px;
            font-weight: 900;
            color: var(--success-light);
            letter-spacing: -0.5px;
        }

        .reward-pill .label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }


        /* === FORM SECTION === */
        .form-section {
            margin-top: 10px;
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            padding-left: 4px;
        }

        .input-group label i {
            color: var(--primary-light);
            font-size: 13px;
        }

        .input-field {
            width: 100%;
            background: var(--bg-card-2);
            border: 2px solid var(--glass-border);
            padding: 16px 18px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 600;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
        }

        .input-field::placeholder {
            color: #636e72;
        }

        .input-field:focus {
            border-color: var(--primary);
            background: rgba(108,92,231,0.05);
            box-shadow: 
                0 0 0 4px rgba(108,92,231,0.15),
                0 8px 25px rgba(108,92,231,0.2);
            transform: translateY(-2px);
        }

        /* === CTA BUTTON === */
        .btn-cta {
            width: 100%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 50%, var(--accent) 100%);
            background-size: 200% 200%;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
            margin-top: 8px;
            animation: btn-gradient 4s ease infinite;
            box-shadow: 0 10px 40px rgba(108,92,231,0.4);
        }

        @keyframes btn-gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .btn-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-cta:hover::before {
            left: 100%;
        }

        .btn-cta:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 50px rgba(108,92,231,0.6);
        }

        .btn-cta:active {
            transform: translateY(-1px) scale(0.98);
        }

        .btn-cta:disabled {
            background: linear-gradient(135deg, #2d3436, #636e72);
            cursor: not-allowed;
            box-shadow: none;
            animation: none;
            transform: none;
        }

        .btn-cta:disabled::before { display: none; }

        .btn-cta .btn-icon {
            width: 28px;
            height: 28px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .btn-cta:not(:disabled) .btn-icon {
            animation: icon-pulse 2s infinite;
        }

        @keyframes icon-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* === TRACK BUTTON === */
        .btn-track {
            width: 100%;
            margin-top: 14px;
            padding: 15px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: var(--text-muted);
            font-weight: 700;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-track:hover {
            border-color: var(--primary);
            color: var(--primary-light);
            background: rgba(108,92,231,0.08);
            transform: translateY(-2px);
        }


        /* === STEPS SECTION === */
        .steps-section {
            margin-top: 35px;
        }

        .steps-title {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-align: center;
            margin-bottom: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .steps-title::before,
        .steps-title::after {
            content: '';
            width: 30px;
            height: 1px;
            background: var(--glass-border);
        }

        .step-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 14px;
            opacity: 0;
            transform: translateY(20px);
            animation: step-appear 0.6s ease forwards;
        }

        .step-item:nth-child(1) { animation-delay: 0.3s; }
        .step-item:nth-child(2) { animation-delay: 0.45s; }
        .step-item:nth-child(3) { animation-delay: 0.6s; }
        .step-item:nth-child(4) { animation-delay: 0.75s; }
        .step-item:nth-child(5) { animation-delay: 0.9s; }

        @keyframes step-appear {
            to { opacity: 1; transform: translateY(0); }
        }

        .step-num {
            width: 30px;
            height: 30px;
            min-width: 30px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(108,92,231,0.3);
            position: relative;
        }

        .step-num::after {
            content: '';
            position: absolute;
            bottom: -14px;
            left: 50%;
            transform: translateX(-50%);
            width: 1px;
            height: 14px;
            background: var(--glass-border);
        }

        .step-item:last-child .step-num::after {
            display: none;
        }

        .step-content {
            flex: 1;
            background: var(--bg-card-2);
            border: 1px solid var(--glass-border);
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .step-content:hover {
            border-color: rgba(108,92,231,0.3);
            background: rgba(108,92,231,0.05);
            transform: translateX(5px);
        }

        /* === TELEGRAM FLOATING BUTTON === */
        .tg-float {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #0088cc, #00b4d8);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 8px 30px rgba(0, 136, 204, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
            text-decoration: none;
            animation: tg-float 3s ease-in-out infinite;
        }

        @keyframes tg-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(3deg); }
        }

        .tg-float:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 136, 204, 0.6);
            border-radius: 50%;
        }

        /* === TOAST === */
        #toast {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background: var(--bg-card);
            color: white;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            display: none;
            z-index: 2000;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px);
            animation: toast-in 0.4s ease forwards;
        }

        @keyframes toast-in {
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        /* === SECURITY FOOTER === */
        .security-footer {
            text-align: center;
            font-size: 12px;
            color: #636e72;
            margin-top: 35px;
            padding: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .security-footer i {
            color: var(--success);
            font-size: 14px;
        }

        /* === RESPONSIVE === */
        @media (max-width: 400px) {
            .hero-header h1 { font-size: 24px; }
            .offer-name { font-size: 20px; }
            .reward-pill .amount { font-size: 18px; }
            .btn-cta { font-size: 14px; padding: 16px; }
            .main-card { padding: 70px 18px 25px; }
        }
    </style>
</head>

<body>

<!-- Preloader -->
<div class="preloader" id="preloader">
    <div class="preloader-logo">
        <i class="fas fa-bolt"></i>
    </div>
    <p class="preloader-text">Loading Offer</p>
    <div class="preloader-bar"></div>
</div>

<!-- Animated Mesh Background -->
<div class="mesh-bg">
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
</div>

<div id="toast"></div>

<div class="container">
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="header-badge">
            <span class="dot"></span>
            Live Offer
        </div>
        <h1>Complete & <span>Earn Instant</span><br>Cashback</h1>
        <p>Trusted by 50,000+ users across India</p>
    </div>

    <!-- Main Content Card -->
    <div class="content-wrap">
        <div class="main-card">
            <!-- Animated Logo -->
            <div class="logo-wrapper">
                <div class="logo-ring">
                    <div class="logo-inner">
                        <img src="<?= $offer_logo_url ?>" alt="<?= $offer_name ?>">
                    </div>
                </div>
            </div>

            <h3 class="offer-name"><?= $offer_name ?></h3>

            <div class="reward-pill">
                <div class="coin-icon"><i class="fas fa-rupee-sign"></i></div>
                <span class="amount"><?= $userAmount ?></span>
                <span class="label">Cashback</span>
            </div>

            <!-- Form -->
            <div class="form-section">
                <div class="input-group">
                    <label>
                        <i class="fas fa-wallet"></i>
                        UPI Address
                    </label>
                    <input type="text" id="userPaytm" class="input-field" placeholder="yourname@upi">
                </div>

                <?php if ($rows['mobile_input'] == 'on'): ?>
                <div class="input-group">
                    <label>
                        <i class="fas fa-mobile-screen"></i>
                        Mobile Number
                    </label>
                    <input type="tel" id="userMobile" class="input-field" placeholder="10 Digit Number" maxlength="10">
                </div>
                <?php endif; ?>

                <button id="gopromo" class="btn-cta" disabled>
                    <span>Start & Earn Cashback</span>
                    <div class="btn-icon"><i class="fas fa-arrow-right"></i></div>
                </button>

                <a href="/track?o=<?= urlencode($offer_id) ?>" class="btn-track">
                    <i class="fas fa-chart-line"></i> Track My Status
                </a>
            </div>


            <!-- Steps -->
            <div class="steps-section">
                <div class="steps-title">
                    <span>How to Complete</span>
                </div>
                <?php $i=1; foreach($offer_steps as $step): ?>
                <div class="step-item">
                    <div class="step-num"><?= $i ?></div>
                    <div class="step-content"><?= htmlspecialchars($step) ?></div>
                </div>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </div>

    <div class="security-footer">
        <i class="fas fa-shield-halved"></i>
        256-bit SSL Encrypted & Secure
    </div>
</div>

<!-- Telegram Float Button -->
<a href="<?= $final_tg_link ?>" target="_blank" class="tg-float">
    <i class="fab fa-telegram-plane"></i>
</a>

<script>
    // Preloader
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.getElementById('preloader').classList.add('hidden');
        }, 1500);
    });

    const paytm = document.getElementById("userPaytm");
    const mobile = document.getElementById("userMobile");
    const btn = document.getElementById("gopromo");

    function showToast(msg) {
        const x = document.getElementById("toast");
        x.innerHTML = msg;
        x.style.display = "block";
        setTimeout(() => { x.style.display = "none"; }, 3000);
    }

    function validate() {
        const upiRegex = /^[\w.-]+@[\w.-]+$/;
        const mobileRegex = /^[6-9]\d{9}$/;
        let isUpiValid = upiRegex.test(paytm.value.trim());
        let isMobileValid = mobile ? mobileRegex.test(mobile.value.trim()) : true;
        btn.disabled = !(isUpiValid && isMobileValid);
    }

    paytm.addEventListener("input", validate);
    if (mobile) mobile.addEventListener("input", validate);

    btn.addEventListener("click", function () {
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Processing...</span>';

        const formData = new FormData();
        formData.append("campaign", "<?= $offer_id ?>");
        formData.append("num", paytm.value.trim());
        formData.append("ref", "<?= $refer_code ?>");
        if (mobile) formData.append("mobile", mobile.value.trim());

        fetch("red.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === true || data.status === "success") {
                showToast('Launching Offer...');
                setTimeout(() => { window.location.href = data.url; }, 1000);
            } else {
                showToast(data.message || "Something went wrong");
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        })
        .catch(() => {
            showToast('Connection Error. Try again.');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    });
</script>

</body>
</html>
