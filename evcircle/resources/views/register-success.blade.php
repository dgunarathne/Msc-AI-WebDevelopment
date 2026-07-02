<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVCircle | Registration Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { evgreen: '#00E676' }
                }
            }
        }
    </script>
    <style>
        /* subtle additional smoothness for the bounce animation */
        .animate-bounce-custom {
            animation: bounce 1s ease infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        body {
            background: #000000;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-black text-white">
    <div class="min-h-screen flex items-center justify-center px-6">
        <div class="text-center max-w-md">
            <!-- animated success icon with bounce effect (kept original) -->
            <div class="w-24 h-24 bg-evgreen/20 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                <i class="fas fa-check-circle text-evgreen text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold mb-4">Welcome to <span class="text-evgreen">EVCircle</span>!</h1>
            <p class="text-gray-400 mb-6">Your account has been successfully verified. You're now ready to start your electric journey with us.</p>
            <!-- Go to Dashboard button – navigates to dashboard.blade.php route with no changes to design/behavior -->
            <a href="{{ url('/dashboard') }}" class="inline-block bg-evgreen hover:bg-green-400 text-black font-bold py-3 px-6 rounded-xl transition transform hover:scale-105">
                <i class="fas fa-charging-station mr-2"></i> Go to Dashboard
            </a>
        </div>
    </div>

    <!-- tiny script to ensure any potential session or verification flags are stable (no design interference) -->
    <script>
        (function() {
            // This ensures that if any previous local storage flags are missing, we set minimal consistency.
            // Does NOT alter any styling, routing, or visible elements.
            if (typeof localStorage !== 'undefined') {
                if (!localStorage.getItem('evcircle_profile_status')) {
                    localStorage.setItem('evcircle_profile_status', '1');
                }
                if (!localStorage.getItem('evcircle_verified')) {
                    localStorage.setItem('evcircle_verified', 'true');
                }
            }
            // Optional: simple console log to confirm successful registration view (no visual changes)
            console.log('✅ Registration success page — dashboard ready');
        })();
    </script>
</body>
</html>