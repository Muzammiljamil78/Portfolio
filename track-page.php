<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
if (file_exists('../db.php')) {
    include('../db.php');
} else {
    include('db.php');
}

if (!isset($db) && isset($conn)) {
    $db = $conn;
}

if (!isset($db)) {
    die("<div style='color:red; text-align:center; padding:20px;'>Database Error: Connection variable not found.</div>");
}

// Clean Input
$offer_id = isset($_GET['o']) ? intval($_GET['o']) : 0;

if ($offer_id <= 0) {
    die("<div style='font-family:sans-serif;text-align:center;padding:50px;color:#ef4444;'>Invalid Offer ID</div>");
}

// Fetch Offer Data
$query = "SELECT offer_name, offer_img FROM offers WHERE offer_id = '$offer_id' LIMIT 1";
$offer_q = mysqli_query($db, $query);

if (!$offer_q) {
    die("<div style='color:red; text-align:center; padding:20px;'>SQL Error: " . mysqli_error($db) . "</div>");
}

$offer_data = mysqli_fetch_assoc($offer_q);
$offer_name = $offer_data['offer_name'] ?? "Unknown Offer";

// Logo Logic
$default_logo   = "https://admincamp.in/logo.jpeg";
$offer_logo_url = !empty($offer_data['offer_img']) ? htmlspecialchars($offer_data['offer_img']) : $default_logo;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Track: <?=htmlspecialchars($offer_name)?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#0f0f23">

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
            --warning: #fdcb6e;
            --danger: #ff7675;
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


        /* === PAGE TITLE === */
        .page-title {
            font-size: 22px;
            font-weight: 800;
            text-align: center;
            color: white;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        /* === INPUT FIELD === */
        .input-group {
            margin-bottom: 18px;
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

        /* === TRACK BUTTON === */
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


        /* === BACK BUTTON === */
        .btn-back {
            width: 100%;
            margin-top: 20px;
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
            cursor: pointer;
        }

        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary-light);
            background: rgba(108,92,231,0.08);
            transform: translateY(-2px);
        }

        /* === RESULTS HEADER === */
        .results-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .results-header-title {
            font-size: 22px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .results-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* === STATS GRID === */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: var(--bg-card-2);
            border-radius: 18px;
            padding: 16px 12px;
            text-align: center;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border-color: rgba(108,92,231,0.3);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            margin: 0 auto 10px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.total {
            background: rgba(108,92,231,0.2);
            color: var(--primary-light);
        }

        .stat-icon.success {
            background: rgba(0,184,148,0.2);
            color: var(--success-light);
        }

        .stat-icon.failed {
            background: rgba(255,118,117,0.2);
            color: var(--danger);
        }

        .stat-value {
            font-size: 24px;
            font-weight: 900;
            color: var(--text);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }


        /* === USER INFO CARD === */
        .user-info-card {
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            box-shadow: 0 10px 40px rgba(108,92,231,0.4);
            position: relative;
            overflow: hidden;
        }

        .user-info-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .user-info-header {
            font-size: 11px;
            opacity: 0.9;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            position: relative;
            z-index: 1;
        }

        .user-info-row:last-child {
            border-bottom: none;
        }

        .user-info-label {
            font-size: 13px;
            opacity: 0.9;
            font-weight: 600;
        }

        .user-info-value {
            font-size: 13px;
            font-weight: 800;
        }

        /* === ACTIVITY TIMELINE === */
        .activity-timeline {
            margin-top: 25px;
        }

        .timeline-header {
            font-size: 16px;
            font-weight: 900;
            color: var(--text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .timeline-header i {
            font-size: 20px;
            color: var(--primary-light);
        }

        .timeline-count {
            background: rgba(108,92,231,0.2);
            color: var(--primary-light);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            margin-left: auto;
            border: 1px solid rgba(108,92,231,0.3);
        }

        /* === EVENT ITEM === */
        .event-item {
            background: var(--bg-card-2);
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 12px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideInRight 0.5s ease-out backwards;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .event-item:hover {
            border-color: rgba(108,92,231,0.3);
            transform: translateX(5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .event-item:nth-child(2) { animation-delay: 0.1s; }
        .event-item:nth-child(3) { animation-delay: 0.15s; }
        .event-item:nth-child(4) { animation-delay: 0.2s; }
        .event-item:nth-child(5) { animation-delay: 0.25s; }
        .event-item:nth-child(6) { animation-delay: 0.3s; }

        .event-check {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            position: relative;
        }

        .event-check.success {
            background: rgba(0,184,148,0.2);
            color: var(--success-light);
            box-shadow: 0 4px 15px rgba(0,184,148,0.3);
        }

        .event-check.failed {
            background: rgba(255,118,117,0.2);
            color: var(--danger);
            box-shadow: 0 4px 15px rgba(255,118,117,0.3);
        }

        .event-check::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            z-index: -1;
            opacity: 0.3;
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        .event-check.success::after {
            background: var(--success);
        }

        .event-check.failed::after {
            background: var(--danger);
        }

        @keyframes ping {
            75%, 100% { transform: scale(1.5); opacity: 0; }
        }

        .event-content {
            flex: 1;
        }

        .event-text {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .event-time {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-badge {
            padding: 5px 12px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-width: fit-content;
        }

        .event-badge.user { 
            background: rgba(108,92,231,0.2);
            color: var(--primary-light);
            border: 1px solid rgba(108,92,231,0.3);
        }

        .event-badge.referral { 
            background: rgba(253,121,168,0.15);
            color: #fd79a8;
            border: 1px solid rgba(253,121,168,0.3);
        }

        .referred-info {
            background: rgba(253,121,168,0.1);
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 12px;
            margin-top: 8px;
            border: 1px solid rgba(253,121,168,0.2);
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
        }

        .referred-info i {
            color: #fd79a8;
        }

        .referred-info span {
            color: #fd79a8;
            font-weight: 700;
        }


        /* === EMPTY STATE === */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.4;
            display: block;
            color: var(--primary-light);
        }

        .empty-state .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: white;
        }

        .empty-state .subtitle {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* === TOAST === */
        #toast-box {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-card);
            color: white;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            z-index: 2000;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px);
            transition: bottom 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #toast-box.show {
            bottom: 30px;
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

        /* === TYPE BADGES === */
        .type-badge {
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .type-badge.user { 
            background: rgba(108,92,231,0.2);
            color: var(--primary-light);
            border: 1px solid rgba(108,92,231,0.3);
        }

        .type-badge.referral { 
            background: rgba(253,121,168,0.15);
            color: #fd79a8;
            border: 1px solid rgba(253,121,168,0.3);
        }

        /* === RESPONSIVE === */
        @media (max-width: 400px) {
            .hero-header h1 { font-size: 24px; }
            .page-title { font-size: 20px; }
            .main-card { padding: 70px 18px 25px; }
            .stats-grid { gap: 8px; }
            .stat-value { font-size: 20px; }
        }
    </style>
</head>


<body>

<!-- Preloader -->
<div class="preloader" id="preloader">
    <div class="preloader-logo">
        <i class="fas fa-satellite-dish"></i>
    </div>
    <p class="preloader-text">Loading Tracker</p>
    <div class="preloader-bar"></div>
</div>

<!-- Animated Mesh Background -->
<div class="mesh-bg">
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
    <div class="mesh-orb"></div>
</div>

<div class="container">
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="header-badge">
            <span class="dot"></span>
            Live Tracker
        </div>
        <h1>Status <span>Tracker</span></h1>
        <p>Real-time Event Monitoring</p>
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

            <div id="form-view">
                <h3 class="page-title"><?= htmlspecialchars($offer_name) ?></h3>
                
                <div class="input-group">
                    <label>
                        <i class="fas fa-search"></i>
                        Enter Registered UPI ID
                    </label>
                    <input type="text" id="upiInput" class="input-field" placeholder="example@okaxis">
                </div>

                <button type="button" id="btnTrack" class="btn-cta">
                    <span>Track Status</span>
                    <div class="btn-icon"><i class="fas fa-arrow-right"></i></div>
                </button>
            </div>

            <div id="results-view" style="display: none;">
                <div class="results-header">
                    <div class="results-header-title">
                        <i class="fas fa-chart-line"></i>
                        Tracking Report
                    </div>
                    <div class="results-subtitle">Complete Activity Details</div>
                </div>

                <div id="stats-boxes"></div>

                <div id="user-details"></div>

                <div id="transaction-history"></div>

                <button type="button" onclick="resetView()" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Check Another
                </button>
            </div>
        </div>
    </div>

    <div class="security-footer">
        <i class="fas fa-shield-halved"></i>
        256-bit Secure Tracking
    </div>
</div>

<div id="toast-box">Message</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Preloader
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.getElementById('preloader').classList.add('hidden');
        }, 1500);
    });

    const offerId = "<?= $offer_id ?>";
    let currentData = null;

    function showToast(msg, isError = false) {
        const x = document.getElementById("toast-box");
        x.innerText = msg;
        x.className = "show";
        x.style.background = isError 
            ? "linear-gradient(135deg, #ff7675, #d63031)" 
            : "linear-gradient(135deg, #1a1a2e, #16213e)";
        x.style.borderColor = isError ? "rgba(255,118,117,0.3)" : "rgba(255,255,255,0.1)";
        setTimeout(function(){ x.className = ""; }, 3000);
    }

    function maskUPI(upi) {
        if (!upi || !upi.includes('@')) return upi;
        let parts = upi.split('@');
        let name = parts[0];
        let domain = parts[1];
        if (name.length <= 3) return "***@" + domain;
        let visible = name.substring(0, 3);
        return visible + "***@" + domain;
    }

    function formatDateIST(dateStr) {
        if (!dateStr) return 'N/A';
        let safeDate = dateStr.replace(/-/g, '/');
        const date = new Date(safeDate);
        if(isNaN(date.getTime())) return dateStr;
        return date.toLocaleString('en-IN', { 
            hour12: true, 
            day: 'numeric', month: 'short', year: 'numeric', 
            hour: '2-digit', minute: '2-digit'
        });
    }

    function getTypeBadge(type) {
        const icon = type === 'user' ? 'fa-user' : 'fa-users';
        return `<span class="type-badge ${type}"><i class="fas ${icon}"></i> ${type}</span>`;
    }

    function displayResults(data) {
        currentData = data;
        
        if (!data || !data.transactions || data.transactions.length === 0) {
            $('#stats-boxes').empty();
            $('#user-details').html(`
                <div class="empty-state">
                    <i class="far fa-folder-open"></i>
                    <div class="title">No Records Found</div>
                    <div class="subtitle">No transactions found for this UPI ID</div>
                </div>
            `);
            $('#transaction-history').empty();
            return;
        }

        const role = data.role;
        const transactions = data.transactions;

        // Calculate Stats
        const totalEvents = transactions.length;
        const successEvents = transactions.filter(t => !t.status.toLowerCase().includes('fail')).length;
        const failedEvents = totalEvents - successEvents;

        // 1. Stats Boxes
        $('#stats-boxes').html(`
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-icon total">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value">${totalEvents}</div>
                    <div class="stat-label">Total Events</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">${successEvents}</div>
                    <div class="stat-label">Success</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon failed">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value">${failedEvents}</div>
                    <div class="stat-label">Failed</div>
                </div>
            </div>
        `);

        // 2. User Info Card (Only UPI ID)
        $('#user-details').html(`
            <div class="user-info-card">
                <div class="user-info-header">
                    <i class="fas fa-wallet"></i> Your UPI ID
                </div>
                <div style="text-align: center; padding: 10px 0; position: relative; z-index: 1;">
                    <div style="font-size: 20px; font-weight: 900; letter-spacing: 0.5px;">
                        ${maskUPI(data.upi)}
                    </div>
                </div>
            </div>
        `);

        // 3. Activity Timeline
        displayTransactions(transactions);
    }

    function displayTransactions(transactions) {
        let transHtml = `
            <div class="activity-timeline">
                <div class="timeline-header">
                    <i class="fas fa-clock-rotate-left"></i>
                    Event Logs
                    <span class="timeline-count">${transactions.length} Events</span>
                </div>`;

        transactions.forEach((trans) => {
            let isSuccess = !trans.status.toLowerCase().includes('fail');
            let checkClass = isSuccess ? 'success' : 'failed';
            let icon = isSuccess ? 'fa-check' : 'fa-times';

            transHtml += `
                <div class="event-item">
                    <div class="event-check ${checkClass}">
                        <i class="fas ${icon}"></i>
                    </div>
                    
                    <div class="event-content">
                        <div class="event-text">${trans.event}</div>
                        <div class="event-time">
                            <i class="far fa-clock"></i>
                            ${formatDateIST(trans.created_at)}
                        </div>
                        ${trans.type === 'referral' && trans.referred_user ? `
                            <div class="referred-info">
                                <i class="fas fa-user-plus"></i>
                                Referred: <span>${maskUPI(trans.referred_user)}</span>
                            </div>` : ''}
                    </div>
                </div>`;
        });

        transHtml += `</div>`;
        $('#transaction-history').html(transHtml);
    }

    $('#btnTrack').click(function() {
        const upi = $('#upiInput').val().trim();
        const upiRegex = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/;

        if (!upiRegex.test(upi)) { 
            showToast("Please enter a valid UPI ID", true); 
            return; 
        }

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> <span>Checking...</span>');

        $.ajax({
            url: 'red.php',
            type: 'POST',
            data: { campaign: offerId, num: upi },
            dataType: 'text', 
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.error) { 
                        showToast(data.message || "An error occurred", true); 
                        return; 
                    }
                    $('#form-view').hide();
                    $('#results-view').show();
                    displayResults(data);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    if (response.includes('<') || response.includes('table')) {
                        showToast("Please update red.php to return JSON format", true);
                    } else {
                        showToast("Invalid response format", true);
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = "Connection Error";
                if (xhr.status === 404) errorMsg = "red.php not found";
                if (xhr.status === 500) errorMsg = "Server error - Check red.php";
                showToast(errorMsg + " - Please try again", true);
            },
            complete: function() { 
                btn.prop('disabled', false).html(originalText); 
            }
        });
    });

    $('#upiInput').on('keypress', function(e) {
        if (e.which === 13) { $('#btnTrack').click(); }
    });

    function resetView() {
        $('#results-view').hide();
        $('#form-view').fadeIn();
        $('#upiInput').val('').focus();
        $('#stats-boxes, #user-details, #transaction-history').empty();
        currentData = null;
    }
</script>

</body>
</html>
