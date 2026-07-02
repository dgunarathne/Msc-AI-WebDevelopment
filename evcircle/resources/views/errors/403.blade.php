<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden | EvCircle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a1a0f 0%, #020805 100%);
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: rgba(255,255,255,0.95);
            border-radius: 2rem;
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            margin: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #dc2626;
        }
        .btn-primary {
            background: #00b85a;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="fas fa-lock" style="font-size: 4rem; color: #dc2626;"></i>
        <div class="error-code">403</div>
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page.</p>
        <a href="{{ url('/') }}" class="btn-primary">Return Home</a>
    </div>
</body>
</html>