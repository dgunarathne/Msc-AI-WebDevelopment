<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EVCircle | Verify OTP - Mobile Verification</title>
    <!-- TailwindCSS + Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        
        .otp-input {
            width: 70px;
            height: 70px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #374151;
            border-radius: 16px;
            color: white;
            transition: all 0.2s ease;
        }
        .otp-input:focus {
            border-color: #00E676;
            box-shadow: 0 0 0 3px rgba(0, 230, 118, 0.2);
            outline: none;
        }
        .loader {
            border: 3px solid rgba(0, 230, 118, 0.3);
            border-top: 3px solid #00E676;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .toast-success {
            background: linear-gradient(135deg, #00E676, #00C853);
            color: #000;
        }
        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }
        .shake-effect {
            animation: shakeInput 0.4s ease-in-out;
        }
        @keyframes shakeInput {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }
        .otp-container {
            transition: all 0.2s;
        }
    </style>
</head>

<body class="bg-black text-white overflow-x-hidden">

    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 top-0 transition-all duration-300 nav-transparent">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4 md:px-6">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-evgreen to-green-700 flex items-center justify-center shadow-lg">
                        <i class="fas fa-charging-station text-black text-lg"></i>
                    </div>
                    <div class="absolute inset-0 rounded-full bg-evgreen/30 blur-md group-hover:bg-evgreen/40 transition"></div>
                </div>
                <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">EVCircle</span>
            </a>
            <div class="flex md:order-2 gap-3 items-center">
                <a href="{{ url('/login') }}" class="text-evgreen border border-evgreen/50 hover:bg-evgreen hover:text-black transition-all duration-300 font-medium rounded-full px-5 py-2 text-sm md:text-base">
                    <i class="fas fa-sign-in-alt mr-1"></i> Sign In
                </a>
            </div>
        </div>
    </nav>

    <!-- OTP Verification Section -->
    <section class="relative min-h-screen flex items-center justify-center px-6 pt-28 pb-16 md:pt-32">
        <div class="absolute w-[600px] h-[600px] bg-evgreen/15 blur-[140px] top-[-180px] left-[-200px] rounded-full animate-pulse-slow"></div>
        <div class="absolute w-[500px] h-[500px] bg-evgreen/10 blur-[130px] bottom-[-100px] right-[-150px] rounded-full"></div>
        
        <div class="relative z-10 w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-evgreen opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-evgreen"></span>
                    </span>
                    <span class="text-xs font-mono text-evgreen tracking-wide">🔐 VERIFY YOUR ACCOUNT</span>
                </div>
                <div class="flex justify-center mb-4">
                    <div class="w-20 h-20 bg-evgreen/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-evgreen text-4xl"></i>
                    </div>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold">
                    Verify <span class="text-evgreen">Account</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2" id="subtitleText">Loading...</p>
            </div>

            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10 shadow-2xl">
                <div class="flex justify-center gap-3 mb-8 otp-container" id="otpContainer">
                    <input type="text" id="otp_1" maxlength="1" class="otp-input w-14 h-14 md:w-16 md:h-16 text-center text-2xl font-bold bg-black/50 border-2 border-gray-600 rounded-xl text-white focus:border-evgreen" autofocus>
                    <input type="text" id="otp_2" maxlength="1" class="otp-input w-14 h-14 md:w-16 md:h-16 text-center text-2xl font-bold bg-black/50 border-2 border-gray-600 rounded-xl text-white focus:border-evgreen">
                    <input type="text" id="otp_3" maxlength="1" class="otp-input w-14 h-14 md:w-16 md:h-16 text-center text-2xl font-bold bg-black/50 border-2 border-gray-600 rounded-xl text-white focus:border-evgreen">
                    <input type="text" id="otp_4" maxlength="1" class="otp-input w-14 h-14 md:w-16 md:h-16 text-center text-2xl font-bold bg-black/50 border-2 border-gray-600 rounded-xl text-white focus:border-evgreen">
                </div>

                <div class="text-center mb-6">
                    <button id="resendBtn" disabled class="text-evgreen font-medium hover:underline transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="resendText">Resend Code in <span id="timer">60</span>s</span>
                    </button>
                </div>

                <button id="verifyBtn" class="w-full bg-evgreen hover:bg-green-400 text-black font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg shadow-evgreen/30 disabled:opacity-70 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle"></i>
                    <span id="btnText">Verify</span>
                    <span id="loaderIcon" class="hidden"><span class="loader"></span></span>
                </button>

                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">
                        Wrong number?
                        <a href="{{ url('/register') }}" class="text-evgreen font-semibold hover:underline">Go Back</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-gray-800/40 py-6 text-center text-gray-500 text-sm relative z-10 bg-black/30 backdrop-blur-sm">
        <p>© 2025 EVCircle – Electrifying the future. All rights reserved.</p>
    </footer>

    <script>
        // API Configuration - Uses your existing Laravel mobile API endpoints
        const API_URL = '{{ env("API_URL") }}';
        const API_KEY = '{{ env("API_KEY") }}';
        
        // DOM Elements
        const otpElements = ['otp_1', 'otp_2', 'otp_3', 'otp_4'].map(id => document.getElementById(id));
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const resendTextSpan = document.getElementById('resendText');
        const timerSpan = document.getElementById('timer');
        const btnText = document.getElementById('btnText');
        const loaderIcon = document.getElementById('loaderIcon');
        const subtitleText = document.getElementById('subtitleText');
        const otpContainer = document.getElementById('otpContainer');
        
        // State variables
        let userPhone = '';
        let currentValidOTP = '';      // Stores the correct OTP extracted from backend (array or string)
        let resendTimer = 60;
        let canResend = false;
        let isLoading = false;
        let timerInterval = null;
        let accessToken = '';
        
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
        
        // Helper: Format masked phone number
        function formatMaskedPhone(phone) {
            if (!phone) return '';
            let normalized = phone;
            if (normalized.startsWith('0')) {
                normalized = normalized.substring(1);
            }
            if (normalized.length < 9) return `+94 ${normalized}`;
            const firstTwo = normalized.substring(0, 2);
            const lastThree = normalized.substring(normalized.length - 3);
            return `+94 ${firstTwo}X XXX X${lastThree}`;
        }
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-2xl text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 ${
                type === 'success' ? 'toast-success' : 'toast-error'
            }`;
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>${message}`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Shake OTP container on error
        function shakeOtpInputs() {
            if (otpContainer) {
                otpContainer.classList.add('shake-effect');
                setTimeout(() => {
                    otpContainer.classList.remove('shake-effect');
                }, 400);
            }
        }
        
        // Clear all OTP fields and focus first
        function clearOtpInputs() {
            otpElements.forEach(input => {
                if (input) input.value = '';
            });
            if (otpElements[0]) otpElements[0].focus();
        }
        
        // Get concatenated OTP from inputs
        function getEnteredOtp() {
            return otpElements.map(input => input?.value || '').join('');
        }
        
        // Set loading state
        function setLoading(loading) {
            isLoading = loading;
            if (verifyBtn) verifyBtn.disabled = loading;
            if (loading) {
                if (loaderIcon) loaderIcon.classList.remove('hidden');
                if (btnText) btnText.textContent = 'Verifying...';
            } else {
                if (loaderIcon) loaderIcon.classList.add('hidden');
                if (btnText) btnText.textContent = 'Verify';
            }
        }
        
        // Auto-submit when all 4 digits are entered (no premature clear)
        function autoSubmitIfComplete() {
            const allFilled = otpElements.every(inp => inp && inp.value.length === 1);
            if (allFilled && !isLoading) {
                handleVerifyOTP();
            }
        }
        
        // Setup OTP input fields with proper navigation & avoid unintended clearing
        function setupOtpInputs() {
            otpElements.forEach((input, index) => {
                if (!input) return;
                
                // Handle input event: move to next field if value entered
                input.addEventListener('input', (e) => {
                    const value = e.target.value;
                    // Only keep last character if multiple pasted (though maxlength=1 handles)
                    if (value && value.length > 0) {
                        // Extract only the last character (or first) for single-digit field
                        const finalChar = value.slice(-1);
                        input.value = finalChar;
                        // Move to next field if not last
                        if (index < 3 && finalChar) {
                            otpElements[index + 1]?.focus();
                        }
                    } else if (value === '') {
                        // allow deletion
                    }
                    // Auto-verify after all four are filled (no automatic clear ever)
                    autoSubmitIfComplete();
                });
                
                // Handle keydown for Backspace navigation
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        e.preventDefault();
                        otpElements[index - 1]?.focus();
                        otpElements[index - 1].value = '';
                    }
                    // Also allow left/right arrow keys for convenience
                    if (e.key === 'ArrowLeft' && index > 0) {
                        e.preventDefault();
                        otpElements[index - 1]?.focus();
                    }
                    if (e.key === 'ArrowRight' && index < 3) {
                        e.preventDefault();
                        otpElements[index + 1]?.focus();
                    }
                });
                
                // Prevent non-numeric input if needed, but we accept digits only
                input.addEventListener('beforeinput', (e) => {
                    if (e.inputType === 'insertText' && !/^\d$/.test(e.data)) {
                        e.preventDefault();
                    }
                });
            });
        }
        
        // ***** CRITICAL FIX: Extract OTP from network response (supports array or string) *****
        function extractOtpFromResponse(data) {
            // Backend returns: { success: true, number: ["1","5","7","4"], result: "...", phone: "..." }
            // Also could be { number: "1574" } or { otp: "1234" } — robust extraction.
            if (data.number) {
                if (Array.isArray(data.number)) {
                    // Join array elements to form 4-digit code
                    return data.number.join('');
                } else if (typeof data.number === 'string') {
                    return data.number;
                }
            }
            if (data.otp) return String(data.otp);
            if (data.code) return String(data.code);
            if (data.token) return String(data.token);
            return '';
        }
        
        // Send OTP via API and store the received OTP code correctly
        async function sendOtp(phone, type = 'send') {
            setLoading(true);
            // Normalize phone: remove leading 0 if present
            const phoneToSend = phone.startsWith('0') ? phone.substring(1) : phone;
            const apiUrl = `${API_URL}/api/send_otp?for=${type}&phone=${phoneToSend}`;
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${accessToken}`,
                        'X-API-KEY': API_KEY,
                        'Content-Type': 'application/json',
                    },
                });
                
                const data = await response.json();
                
                if (response.ok && data.success === true) {
                    // *** FIX: properly extract OTP from the network response (number array or string) ***
                    const extractedOtp = extractOtpFromResponse(data);
                    if (extractedOtp && extractedOtp.length === 4) {
                        currentValidOTP = extractedOtp;
                        console.log('✅ OTP retrieved from server:', currentValidOTP);
                    } else {
                        // Fallback: If no OTP in response, try to use any fallback but warn (should not happen)
                        console.warn('⚠️ OTP not found in response structure', data);
                        // For demo purpose, but we do not set invalid OTP. Instead treat as error
                        if (type === 'send') {
                            showToast('Could not retrieve OTP code. Please try again.', 'error');
                            setLoading(false);
                            return false;
                        }
                    }
                    startResendTimer(60);
                    showToast(type === 'send' ? '✅ Verification code sent!' : '📲 New code sent successfully!', 'success');
                    // Clear OTP inputs after fresh send
                    clearOtpInputs();
                    return true;
                } else {
                    let errorMessage = data.message || 'Failed to send OTP. Please try again.';
                    showToast(errorMessage, 'error');
                    return false;
                }
            } catch (error) {
                console.error('API send error:', error);
                showToast('Connection error. Please check your internet.', 'error');
                return false;
            } finally {
                setLoading(false);
            }
        }
        
        // Timer for resend button
        function startResendTimer(seconds) {
            if (timerInterval) clearInterval(timerInterval);
            resendTimer = seconds;
            canResend = false;
            if (resendBtn) resendBtn.disabled = true;
            timerInterval = setInterval(() => {
                if (resendTimer > 0) {
                    resendTimer--;
                    if (timerSpan) timerSpan.textContent = resendTimer;
                    if (resendTextSpan) resendTextSpan.innerHTML = `Resend Code in <span id="timer">${resendTimer}</span>s`;
                } else {
                    clearInterval(timerInterval);
                    canResend = true;
                    if (resendBtn) resendBtn.disabled = false;
                    if (resendTextSpan) resendTextSpan.innerHTML = 'Resend Code';
                }
            }, 1000);
        }
        
        // Verify user API call (your existing /verify endpoint)
        async function verifyUser() {
            try {
                const response = await fetch(`${API_URL}/api/verify`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${accessToken}`,
                        'Content-Type': 'application/json',
                        'X-API-KEY': API_KEY,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (response.ok) return true;
                showToast(data.message || 'Verification failed on server', 'error');
                return false;
            } catch (error) {
                console.error('Verify user API error:', error);
                showToast('Network error while verifying account.', 'error');
                return false;
            }
        }
        
        // Send welcome notification
        async function sendWelcomeNotification() {
            try {
                await fetch(`${API_URL}/api/welcome`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${accessToken}`,
                        'X-API-KEY': API_KEY,
                    },
                });
            } catch (error) {
                console.error('Welcome notification error:', error);
            }
        }
        
        // Handle OTP verification with proper validation — NO AUTOMATIC CLEAR UNLESS WRONG
        async function handleVerifyOTP() {
            const enteredOtp = getEnteredOtp();
            if (enteredOtp.length !== 4) {
                showToast('Please enter the complete 4-digit verification code', 'error');
                shakeOtpInputs();
                return;
            }
            
            if (!currentValidOTP || currentValidOTP.length !== 4) {
                showToast('No OTP code found. Please resend code.', 'error');
                return;
            }
            
            setLoading(true);
            
            // Compare entered OTP with stored OTP from backend response
            if (enteredOtp === currentValidOTP) {
                const isVerified = await verifyUser();
                if (isVerified) {
                    localStorage.setItem('evcircle_profile_status', '1');
                    await sendWelcomeNotification();
                    showToast('🎉 Account verified successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ url("/register-success") }}';
                    }, 1500);
                } else {
                    // Verification API failed, but OTP is correct? Should not clear OTP but let user retry.
                    shakeOtpInputs();
                    setLoading(false);
                }
            } else {
                // Incorrect OTP: show error, shake fields, and clear inputs for fresh entry (clear only on wrong)
                showToast('❌ Incorrect verification code. Please try again.', 'error');
                shakeOtpInputs();
                // Clear only the input fields to allow re-entry (standard UX)
                clearOtpInputs();
                setLoading(false);
            }
            // if OTP matched but server verify failed, do NOT clear OTP fields
            if (enteredOtp === currentValidOTP) {
                // OTP matched, but we already called setLoading(false) only if verify fails? Actually setLoading false is done below separately.
                // handle inside condition above.
            }
            // Ensure loading is turned off if not already turned off in error paths.
            if (isLoading) setLoading(false);
        }
        
        // Resend OTP handler
        async function handleResendOTP() {
            if (canResend && !isLoading && userPhone) {
                const success = await sendOtp(userPhone, 'resend');
                if (success) {
                    // After resend, we have new OTP stored in currentValidOTP already inside sendOtp
                    // Ensure we also clean fields for new code
                    clearOtpInputs();
                }
            } else if (!canResend) {
                showToast('Please wait before requesting a new code', 'error');
            }
        }
        
        // Initialize page: get phone, request OTP, store token
        async function initialize() {
            accessToken = localStorage.getItem('evcircle_access_token') || '';
            const urlParams = new URLSearchParams(window.location.search);
            userPhone = urlParams.get('phone') || localStorage.getItem('evcircle_mobile') || '';
            
            if (!userPhone) {
                showToast('Phone number not found. Please sign up again.', 'error');
                setTimeout(() => window.location.href = '{{ url("/register") }}', 2000);
                return;
            }
            
            localStorage.setItem('evcircle_mobile', userPhone);
            subtitleText.innerHTML = `📱 We've sent a 4-digit code to <strong class="text-evgreen">${formatMaskedPhone(userPhone)}</strong>`;
            // Send OTP on load (this will fill currentValidOTP from API)
            await sendOtp(userPhone, 'send');
        }
        
        // Event listeners
        verifyBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            if (!isLoading) handleVerifyOTP();
        });
        resendBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            handleResendOTP();
        });
        
        setupOtpInputs();
        initialize();
        
        // Cleanup timer on page unload
        window.addEventListener('beforeunload', () => {
            if (timerInterval) clearInterval(timerInterval);
        });
    </script>
</body>
</html>