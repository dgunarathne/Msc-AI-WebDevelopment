<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>404 - Page Not Found | EvCircle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0a1a0f 0%, #020805 50%, #000000 100%);
            overflow-x: hidden;
        }

        .glow-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.3;
            z-index: 0;
        }

        .orb1 {
            width: 70vw;
            height: 70vw;
            background: radial-gradient(circle, rgba(0,255,100,0.25), rgba(0,80,30,0));
            top: -30%;
            right: -20%;
        }

        .orb2 {
            width: 55vw;
            height: 55vw;
            background: radial-gradient(circle, rgba(0,220,80,0.2), rgba(0,60,20,0));
            bottom: -40%;
            left: -15%;
        }

        .container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .error-card {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 2rem;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeSlideUp 0.5s ease-out;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #00b85a, #008c42);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .error-icon {
            font-size: 4rem;
            color: #00b85a;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 1.8rem;
            color: #1a2a1f;
            margin-bottom: 1rem;
        }

        p {
            color: #6b7c6e;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(95deg, #00b85a, #008c42);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 184, 90, 0.3);
        }

        .btn-secondary {
            background: rgba(0, 184, 90, 0.1);
            color: #008c42;
            padding: 0.8rem 1.8rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(0, 184, 90, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(0, 184, 90, 0.2);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="glow-orb orb1"></div>
    <div class="glow-orb orb2"></div>
    
    <div class="container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-charging-station"></i>
            </div>
            <div class="error-code">404</div>
            <h1>Page Not Found</h1>
            <p>Oops! The page you're looking for doesn't exist or has been moved.<br>
            Let's get you back on track.</p>
            <div class="btn-group">
                <a href="{{ url('/') }}" class="btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <a href="{{ url('/dashboard') }}" class="btn-secondary">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>