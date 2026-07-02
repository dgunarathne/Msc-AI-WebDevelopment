<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EVCircle | Sign In - EV Charging Network</title>
    <!-- TailwindCSS + Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Google OAuth Client Library -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        evgreen: '#00E676',
                        evdark: '#0A0A0A',
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-12px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        body {
            background: #000000;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .nav-transparent {
            background: rgba(0, 0, 0, 0);
            backdrop-filter: blur(0px);
            transition: all 0.3s ease;
        }
        .nav-blur {
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 230, 118, 0.2);
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0a; }
        ::-webkit-scrollbar-thumb { background: #00E676; border-radius: 8px; }
        
        .input-focus:focus {
            border-color: #00E676;
            box-shadow: 0 0 0 3px rgba(0, 230, 118, 0.2);
            outline: none;
        }
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        .toast-success {
            background: linear-gradient(135deg, #00E676, #00C853);
            color: #000;
        }
        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }
        .loader {
            border: 3px solid rgba(0, 230, 118, 0.3);
            border-top: 3px solid #00E676;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .valid-input {
            border-color: #00E676 !important;
        }
        .invalid-input {
            border-color: #ef4444 !important;
        }
        /* Google Button Styles */
        .google-btn {
            background: white;
            color: #333;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        .google-btn:hover {
            background: #f8f9fa;
            border-color: #bbb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
        }
        .google-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        .google-icon-wrapper svg {
            width: 20px;
            height: 20px;
        }
    </style>
</head>

<body class="bg-black text-white overflow-x-hidden">

    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 top-0 transition-all duration-300 nav-transparent">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4 md:px-6">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                <img src="https://evcircle.lk/logo.png" alt="EvCircle Logo" class="ev-icon" style="width: 40px; height: 40px;" />
                <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">EVCircle</span>
            </a>
            <div class="flex md:order-2 gap-3 items-center">
                <a href="{{ url('/register') }}" class="text-evgreen border border-evgreen/50 hover:bg-evgreen hover:text-black transition-all duration-300 font-medium rounded-full px-5 py-2 text-sm md:text-base">
                    <i class="fas fa-user-plus mr-1"></i> Sign Up
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="relative min-h-screen flex items-center justify-center px-6 pt-28 pb-16 md:pt-32">
        <!-- Animated Background Blobs -->
        <div class="absolute w-[600px] h-[600px] bg-evgreen/15 blur-[140px] top-[-180px] left-[-200px] rounded-full animate-pulse-slow"></div>
        <div class="absolute w-[500px] h-[500px] bg-evgreen/10 blur-[130px] bottom-[-100px] right-[-150px] rounded-full"></div>
        <div class="absolute w-[300px] h-[300px] bg-evgreen/5 blur-[90px] top-1/3 left-1/2 transform -translate-x-1/2"></div>
        
        <div class="relative z-10 w-full max-w-md">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-evgreen opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-evgreen"></span>
                    </span>
                    <span class="text-xs font-mono text-evgreen tracking-wide">⚡ WELCOME BACK</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold">
                    Sign In to <span class="text-evgreen">EVCircle</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">Access your EV charging dashboard and manage your sessions</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10 shadow-2xl">
                <!-- Google Sign-In Button -->
                <div id="googleSignInContainer" class="mb-6">
                    <div id="googleSignInButton" class="flex justify-center"></div>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-4 my-6">
                    <div class="divider-line"></div>
                    <span class="text-gray-500 text-xs uppercase tracking-wider">or continue with</span>
                    <div class="divider-line"></div>
                </div>

                <form id="loginFormElement">
                    <!-- Email/Phone Field -->
                    <div class="mb-5">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Email or Phone Number</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="text" id="identifierInput" 
                                class="w-full bg-black/50 border border-gray-600 rounded-xl pl-12 pr-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus"
                                placeholder="hello@evcircle.com or +94771234567" autocomplete="username">
                        </div>
                        <span class="text-red-400 text-xs error-message block mt-1" id="identifierFieldError"></span>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="passwordInput" 
                                class="w-full bg-black/50 border border-gray-600 rounded-xl pl-12 pr-12 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus"
                                placeholder="••••••••" autocomplete="current-password">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-evgreen transition">
                                <i id="passwordEyeIcon" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <span class="text-red-400 text-xs error-message block mt-1" id="passwordFieldError"></span>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="flex justify-end mb-6">
                        <a href="{{ url('/forgot-password') }}" class="text-sm text-evgreen hover:text-green-400 transition hover:underline">Forgot Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="loginActionBtn"
                        class="w-full bg-evgreen hover:bg-green-400 text-black font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg shadow-evgreen/30 disabled:opacity-70 disabled:cursor-not-allowed">
                        <i class="fas fa-sign-in-alt"></i>
                        <span id="loginBtnText">Sign In</span>
                        <span id="loginSpinner" class="hidden"><span class="loader"></span></span>
                    </button>
                </form>

                <!-- Sign Up Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">
                        Don't have an account?
                        <a href="{{ url('/register') }}" class="text-evgreen font-semibold hover:underline">Create Account</a>
                    </p>
                </div>
            </div>

            <!-- Features Row -->
            <div class="mt-8 grid grid-cols-3 gap-3 text-center">
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10 hover:border-evgreen/40 transition">
                    <i class="fas fa-shield-alt text-evgreen text-xl"></i>
                    <p class="text-xs text-gray-400 mt-1">Secure</p>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10 hover:border-evgreen/40 transition">
                    <i class="fas fa-bolt text-evgreen text-xl"></i>
                    <p class="text-xs text-gray-400 mt-1">Fast Charging</p>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10 hover:border-evgreen/40 transition">
                    <i class="fas fa-leaf text-evgreen text-xl"></i>
                    <p class="text-xs text-gray-400 mt-1">100% Green</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-800/40 py-8 text-center text-gray-500 text-sm relative z-10 bg-black/30 backdrop-blur-sm">
        <div class="flex justify-center gap-6 mb-3">
            <a href="#" class="hover:text-evgreen transition"><i class="fab fa-twitter"></i></a>
            <a href="#" class="hover:text-evgreen transition"><i class="fab fa-instagram"></i></a>
            <a href="#" class="hover:text-evgreen transition"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="hover:text-evgreen transition"><i class="fab fa-facebook"></i></a>
        </div>
        <p>© 2025 EVCircle – Electrifying the future. All rights reserved.</p>
        <p class="text-xs mt-2 text-gray-600">Smart EV Charging Network | Sri Lanka</p>
    </footer>

    <script>
        // ============================================
        // EVCircle Login Page - COMPLETE FUNCTIONALITY
        // API: https://evcircle.lk/api/login
        // Google OAuth Integration
        // ============================================
        
        // API Configuration
        const API_URL = "https://evcircle.lk/api/login";
        const GOOGLE_LOGIN_URL = "https://evcircle.lk/api/auth/google"; // Your backend endpoint
        const API_KEY = "REPLACE_WITH_API_KEY";
        
        // Google OAuth Configuration - REPLACE WITH YOUR ACTUAL CREDENTIALS
        const GOOGLE_CLIENT_ID = "133369886278-cibrl08curm58ihgta9ephda1b7hsuha.apps.googleusercontent.com";
        
        // Storage keys that match React Native app
        const STORAGE = {
            TOKEN: "access_token",
            TOKEN_TYPE: "token_type",
            USER: "evcircle_user_data",
            FULL_RESP: "evcircle_full_response"
        };

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.classList.add('nav-blur');
                navbar.classList.remove('nav-transparent');
            } else {
                navbar.classList.add('nav-transparent');
                navbar.classList.remove('nav-blur');
            }
        });

        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('passwordEyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }

        // Toast notification system
        function showToast(message, isError = false) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-2xl text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 ${
                isError ? 'toast-error' : 'toast-success'
            }`;
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>${message}`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3200);
        }

        // Save login data to localStorage
        function saveLoginData(response) {
            if (response.access_token) {
                localStorage.setItem(STORAGE.TOKEN, response.access_token);
                localStorage.setItem(STORAGE.TOKEN_TYPE, response.token_type || "Bearer");
            }
            if (response.user) {
                localStorage.setItem(STORAGE.USER, JSON.stringify(response.user));
                localStorage.setItem('user_email', response.user.email);
                localStorage.setItem('user_name', response.user.first_name || '');
                localStorage.setItem('evcircle_first_name', response.user.first_name || '');
                localStorage.setItem('evcircle_last_name', response.user.last_name || '');
                localStorage.setItem('evcircle_email', response.user.email || '');
                if (response.user.id) localStorage.setItem('evcircle_user_id', response.user.id);
                if (response.user.mobile) localStorage.setItem('evcircle_mobile', response.user.mobile);
            }
            localStorage.setItem(STORAGE.FULL_RESP, JSON.stringify(response));
        }

        // Navigate to Dashboard
        function navigateToDashboard() {
            window.location.href = "/dashboard";
        }

        // Check if already logged in and redirect
        function checkAuthAndRedirect() {
            const token = localStorage.getItem(STORAGE.TOKEN);
            if (token && token.length > 10) {
                console.log('User already logged in, redirecting to dashboard');
                window.location.href = "/dashboard";
                return true;
            }
            return false;
        }

        // Clear any stale data
        function clearStaleData() {
            localStorage.removeItem(STORAGE.TOKEN);
            localStorage.removeItem(STORAGE.USER);
            localStorage.removeItem(STORAGE.FULL_RESP);
        }

        // ============================================
        // GOOGLE LOGIN INTEGRATION
        // ============================================
        function initGoogleSignIn() {
            try {
                // Initialize Google Identity Services
                google.accounts.id.initialize({
                    client_id: GOOGLE_CLIENT_ID,
                    callback: handleGoogleSignIn,
                    auto_select: false,
                    cancel_on_tap_outside: true,
                    context: 'signin',
                    state_cookie_domain: window.location.hostname,
                });

                // Render the Google Sign-In button
                google.accounts.id.renderButton(
                    document.getElementById('googleSignInButton'),
                    {
                        type: 'standard',
                        theme: 'outline',
                        size: 'large',
                        text: 'continue_with',
                        shape: 'rectangular',
                        logo_alignment: 'left',
                        width: '100%'
                    }
                );

                console.log('Google Sign-In initialized successfully');
            } catch (error) {
                console.error('Error initializing Google Sign-In:', error);
                showToast('Error loading Google Sign-In. Please try again.', true);
            }
        }

        // Handle Google Sign-In response
        async function handleGoogleSignIn(response) {
            console.log('Google Sign-In response received');
            
            try {
                // Show loading state
                showToast('Authenticating with Google...', false);
                
                // Send the Google token to your backend
                const result = await fetch(GOOGLE_LOGIN_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-KEY': API_KEY,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: response.credential,
                        // You can include additional params if needed
                        source: 'web'
                    })
                });

                const data = await result.json();

                if (result.ok && (data.access_token || data.token)) {
                    // Successful login with Google
                    saveLoginData(data);
                    
                    // Get user name for welcome message
                    const userName = data.user?.first_name || 
                                   data.user?.name || 
                                   data.user?.email?.split('@')[0] || 
                                   'User';
                    
                    showToast(`✅ Welcome back, ${userName}! Redirecting...`, false);
                    
                    setTimeout(() => {
                        navigateToDashboard();
                    }, 1000);
                } else {
                    // Handle error
                    const errorMsg = data.message || data.error || 'Google authentication failed';
                    showToast(`❌ ${errorMsg}`, true);
                }
            } catch (error) {
                console.error('Google login error:', error);
                showToast('⚠️ Failed to authenticate with Google. Please try again.', true);
            }
        }

        // Alternative: Manual Google sign-in button (if you want custom styling)
        function handleManualGoogleSignIn() {
            // This can be used if you want to trigger the Google popup manually
            google.accounts.id.prompt();
        }

        // ============================================
        // FORM SUBMISSION HANDLER
        // ============================================
        async function handleLoginSubmit(e) {
            e.preventDefault();
            
            const identifier = document.getElementById('identifierInput').value.trim();
            const password = document.getElementById('passwordInput').value;
            const identError = document.getElementById('identifierFieldError');
            const passError = document.getElementById('passwordFieldError');
            const loginBtn = document.getElementById('loginActionBtn');
            const btnTextSpan = document.getElementById('loginBtnText');
            const spinnerSpan = document.getElementById('loginSpinner');
            const loginCard = document.querySelector('.bg-white\\/5');
            
            // Clear previous errors
            identError.textContent = '';
            passError.textContent = '';
            
            // Validation
            if (!identifier) {
                identError.textContent = 'Email or phone number required';
                loginCard?.classList.add('shake');
                setTimeout(() => loginCard?.classList.remove('shake'), 500);
                return;
            }
            if (!password) {
                passError.textContent = 'Password is required';
                loginCard?.classList.add('shake');
                setTimeout(() => loginCard?.classList.remove('shake'), 500);
                return;
            }
            
            // Show loading state
            loginBtn.disabled = true;
            btnTextSpan.style.display = 'none';
            spinnerSpan.style.display = 'inline-block';
            
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 15000);
                
                const requestBody = {
                    email: identifier,
                    password: password
                };
                
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-KEY': API_KEY,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestBody),
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                let data;
                try {
                    const textResponse = await response.text();
                    data = JSON.parse(textResponse);
                } catch(e) {
                    throw new Error('Invalid response from server');
                }
                
                if (response.ok && (data.access_token || data.token)) {
                    saveLoginData(data);
                    const userName = data.user?.first_name || identifier.split('@')[0] || 'User';
                    showToast(`✅ Welcome back, ${userName}! Redirecting...`, false);
                    
                    setTimeout(() => {
                        navigateToDashboard();
                    }, 1000);
                } else {
                    const errorMsg = data.message || data.error || 'Invalid email or password';
                    showToast(`❌ ${errorMsg}`, true);
                    identError.textContent = errorMsg;
                    
                    loginCard?.classList.add('shake');
                    setTimeout(() => loginCard?.classList.remove('shake'), 500);
                    
                    document.getElementById('passwordInput').value = '';
                }
            } catch (err) {
                console.error('Login error:', err);
                let errorText = 'Network error. Please check your connection.';
                if (err.name === 'AbortError') {
                    errorText = 'Request timeout. Please try again.';
                } else if (err.message) {
                    errorText = err.message;
                }
                showToast(`⚠️ ${errorText}`, true);
                document.getElementById('identifierFieldError').textContent = errorText;
                
                const loginCard = document.querySelector('.bg-white\\/5');
                loginCard?.classList.add('shake');
                setTimeout(() => loginCard?.classList.remove('shake'), 500);
            } finally {
                loginBtn.disabled = false;
                btnTextSpan.style.display = 'inline-flex';
                spinnerSpan.style.display = 'none';
            }
        }

        // Enter key support
        function setupEnterKeySupport() {
            const identifierInput = document.getElementById('identifierInput');
            const passwordInput = document.getElementById('passwordInput');
            const form = document.getElementById('loginFormElement');
            
            if (identifierInput) {
                identifierInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.dispatchEvent(new Event('submit'));
                    }
                });
            }
            
            if (passwordInput) {
                passwordInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.dispatchEvent(new Event('submit'));
                    }
                });
            }
        }

        // Initialize login functionality
        function initLogin() {
            // Check if already logged in
            if (checkAuthAndRedirect()) {
                return;
            }
            
            // Clear stale data
            clearStaleData();
            
            // Initialize Google Sign-In
            if (typeof google !== 'undefined' && google.accounts) {
                initGoogleSignIn();
            } else {
                console.warn('Google Sign-In library not loaded. Check your internet connection.');
                // Fallback: try loading the script again
                const script = document.createElement('script');
                script.src = 'https://accounts.google.com/gsi/client';
                script.async = true;
                script.defer = true;
                script.onload = initGoogleSignIn;
                document.head.appendChild(script);
            }
            
            // Get form elements
            const form = document.getElementById('loginFormElement');
            if (form) {
                form.addEventListener('submit', handleLoginSubmit);
            }
            
            // Setup enter key support
            setupEnterKeySupport();
        }

        // Start the login system when DOM is ready
        document.addEventListener('DOMContentLoaded', initLogin);
    </script>
</body>
</html>