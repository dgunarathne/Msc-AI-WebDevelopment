{{-- resources/views/register.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EVCircle | Create Account - EV Charging Network</title>
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
        .toast-success {
            background: linear-gradient(135deg, #00E676, #00C853);
            color: #000;
        }
        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }
        .valid-input {
            border-color: #00E676 !important;
        }
        .invalid-input {
            border-color: #ef4444 !important;
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

    <!-- Registration Section -->
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
                    <span class="text-xs font-mono text-evgreen tracking-wide">⚡ JOIN THE EVOLUTION</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold">
                    Create <span class="text-evgreen">Account</span>
                </h1>
                <p class="text-gray-400 text-sm mt-2">Power your journey with EVCircle's smart charging network</p>
            </div>

            <!-- Registration Card -->
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10 shadow-2xl">
                <form id="registerForm">
                    @csrf
                    
                    <!-- First Name & Last Name Row -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">First Name <span class="text-evgreen">*</span></label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" maxlength="10"
                                class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus"
                                placeholder="John">
                            <span class="text-red-400 text-xs error-message block mt-1" id="error-first_name"></span>
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Last Name <span class="text-evgreen">*</span></label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" maxlength="12"
                                class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus"
                                placeholder="Doe">
                            <span class="text-red-400 text-xs error-message block mt-1" id="error-last_name"></span>
                        </div>
                    </div>

                    <!-- Mobile Number -->
                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Mobile Number <span class="text-evgreen">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">+94</span>
                            <input type="tel" name="mobile" id="mobile" value="{{ old('mobile') }}" maxlength="10"
                                class="w-full bg-black/50 border border-gray-600 rounded-xl pl-12 pr-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus"
                                placeholder="77 123 4567">
                        </div>
                        <span class="text-gray-500 text-xs">10 digits (e.g., 771234567)</span>
                        <span class="text-red-400 text-xs error-message block mt-1" id="error-mobile"></span>
                    </div>

                    <!-- Email Address -->
                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Email Address <span class="text-evgreen">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus"
                            placeholder="you@example.com">
                        <span class="text-red-400 text-xs error-message block mt-1" id="error-email"></span>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Password <span class="text-evgreen">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus pr-12"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-evgreen transition">
                                <i id="password-eye" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <span class="text-gray-500 text-xs">Minimum 8 characters, no spaces</span>
                        <span class="text-red-400 text-xs error-message block mt-1" id="error-password"></span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Confirm Password <span class="text-evgreen">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus pr-12"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-evgreen transition">
                                <i id="confirm-eye" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <span class="text-red-400 text-xs error-message block mt-1" id="error-password_confirmation"></span>
                    </div>

                    <!-- Privacy Policy Agreement -->
                    <div class="mb-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="agree_to_privacy" id="agree_to_privacy" value="1" class="w-5 h-5 mt-0.5 rounded border-gray-600 bg-black/50 focus:ring-evgreen focus:ring-offset-0 text-evgreen">
                            <span class="text-gray-300 text-sm leading-tight">
                                I agree to the
                                <a href="{{ url('/Privacy-Policy') }}" class="text-evgreen hover:underline">Privacy Policy</a>
                                and
                                <a href="{{ url('/Terms-of-Service') }}" class="text-evgreen hover:underline">Terms & Conditions</a>
                            </span>
                        </label>
                        <span class="text-red-400 text-xs error-message block mt-1" id="error-agree_to_privacy"></span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                        class="w-full bg-evgreen hover:bg-green-400 text-black font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg shadow-evgreen/30 disabled:opacity-70 disabled:cursor-not-allowed">
                        <i class="fas fa-user-plus"></i>
                        <span id="btnText">Sign Up</span>
                        <span id="loaderIcon" class="hidden"><span class="loader"></span></span>
                    </button>
                </form>

                <!-- Sign In Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">
                        Already have an account?
                        <a href="{{ url('/login') }}" class="text-evgreen font-semibold hover:underline">Sign In</a>
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
        // COMPLETE REGISTRATION WITH MOBILE API
        // ============================================
        
        // API Configuration
        const API_URL = '{{ env("API_URL") }}';
        const API_KEY = '{{ env("API_KEY") }}';
        
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
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = fieldId === 'password' ? document.getElementById('password-eye') : document.getElementById('confirm-eye');
            if (field.type === 'password') {
                field.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                field.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }

        // Validation rules (matches mobile app exactly)
        const validationRules = {
            first_name: {
                regex: /^[A-Za-z]{1,10}$/,
                message: 'First name must contain only letters (max 10 characters)'
            },
            last_name: {
                regex: /^[A-Za-z]{1,12}$/,
                message: 'Last name must contain only letters (max 12 characters)'
            },
            mobile: {
                regex: /^[0-9]{10}$/,
                message: 'Mobile number must be exactly 10 digits'
            },
            email: {
                regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            password: {
                validate: (val) => {
                    if (val.length < 8) return 'Password must be at least 8 characters';
                    if (val.includes(' ')) return 'Password cannot contain spaces';
                    return '';
                }
            },
            password_confirmation: {
                validate: (val, formData) => val === formData.password ? '' : 'Passwords do not match'
            },
            agree_to_privacy: {
                validate: (val) => val ? '' : 'You must agree to the Privacy Policy and Terms & Conditions'
            }
        };

        // Get form data helper
        function getFormData() {
            return {
                first_name: document.getElementById('first_name')?.value.trim() || '',
                last_name: document.getElementById('last_name')?.value.trim() || '',
                mobile: document.getElementById('mobile')?.value.trim() || '',
                email: document.getElementById('email')?.value.trim() || '',
                password: document.getElementById('password')?.value || '',
                password_confirmation: document.getElementById('password_confirmation')?.value || '',
                agree_to_privacy: document.getElementById('agree_to_privacy')?.checked || false
            };
        }

        // Validate a single field
        function validateField(fieldName) {
            const formData = getFormData();
            let error = '';
            let isValid = true;
            
            if (fieldName === 'password_confirmation') {
                const rule = validationRules.password_confirmation;
                error = rule.validate(formData.password_confirmation, formData);
                isValid = error === '';
            } else if (fieldName === 'agree_to_privacy') {
                const rule = validationRules.agree_to_privacy;
                error = rule.validate(formData.agree_to_privacy);
                isValid = error === '';
            } else if (validationRules[fieldName]) {
                const rule = validationRules[fieldName];
                const value = formData[fieldName];
                
                if (rule.regex) {
                    isValid = rule.regex.test(value);
                    error = isValid ? '' : rule.message;
                } else if (rule.validate) {
                    error = rule.validate(value, formData);
                    isValid = error === '';
                }
            }
            
            // Update UI
            const inputElement = document.getElementById(fieldName);
            const errorSpan = document.getElementById(`error-${fieldName}`);
            
            if (errorSpan) {
                errorSpan.textContent = error;
            }
            
            if (inputElement) {
                if (error) {
                    inputElement.classList.remove('valid-input');
                    inputElement.classList.add('invalid-input');
                } else if (formData[fieldName] && formData[fieldName].length > 0) {
                    inputElement.classList.remove('invalid-input');
                    inputElement.classList.add('valid-input');
                } else {
                    inputElement.classList.remove('valid-input', 'invalid-input');
                }
            }
            
            return isValid;
        }

        // Validate all fields
        function validateAllFields() {
            const fields = ['first_name', 'last_name', 'mobile', 'email', 'password', 'password_confirmation', 'agree_to_privacy'];
            let allValid = true;
            fields.forEach(field => {
                if (!validateField(field)) allValid = false;
            });
            return allValid;
        }

        // Show toast notification
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

        // Add real-time validation listeners
        const inputs = ['first_name', 'last_name', 'mobile', 'email', 'password', 'password_confirmation'];
        inputs.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.addEventListener('input', () => validateField(field));
                element.addEventListener('blur', () => validateField(field));
            }
        });
        
        const privacyCheckbox = document.getElementById('agree_to_privacy');
        if (privacyCheckbox) {
            privacyCheckbox.addEventListener('change', () => validateField('agree_to_privacy'));
        }

        // Form submission
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const loaderIcon = document.getElementById('loaderIcon');
        const btnText = document.getElementById('btnText');

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Validate all fields
            if (!validateAllFields()) {
                const card = document.querySelector('.bg-white\\/5');
                card?.classList.add('shake');
                setTimeout(() => card?.classList.remove('shake'), 500);
                showToast('Please fix the errors before submitting', 'error');
                return;
            }
            
            // Get FCM token for web
            let fcmToken = localStorage.getItem('evcircle_fcm_token');
            if (!fcmToken) {
                fcmToken = 'web_' + Math.random().toString(36).substring(2, 18) + '_' + Date.now();
                localStorage.setItem('evcircle_fcm_token', fcmToken);
            }
            
            // Show loading state
            submitBtn.disabled = true;
            loaderIcon?.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Creating Account...';
            
            const formData = getFormData();
            
            const requestBody = {
                first_name: formData.first_name,
                last_name: formData.last_name,
                mobile: formData.mobile,
                email: formData.email,
                password: formData.password,
                password_confirmation: formData.password_confirmation,
                fcm: fcmToken,
                platform: 'web'
            };
            
            try {
                // Call your existing mobile API
                const response = await fetch(`${API_URL}/api/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-API-KEY': API_KEY,
                    },
                    body: JSON.stringify(requestBody)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Store user data in localStorage
                    if (data.access_token) {
                        localStorage.setItem('evcircle_access_token', data.access_token);
                    }
                    if (data.user) {
                        localStorage.setItem('evcircle_user_id', data.user.id);
                        localStorage.setItem('evcircle_first_name', data.user.first_name);
                        localStorage.setItem('evcircle_last_name', data.user.last_name);
                        localStorage.setItem('evcircle_email', data.user.email);
                        localStorage.setItem('evcircle_mobile', formData.mobile);
                    }
                    
                    showToast('Account created successfully!', 'success');
                    
                    // Redirect to OTP verification page
                    setTimeout(() => {
                        window.location.href = '{{ url("/verify-otp") }}?phone=' + formData.mobile;
                    }, 1500);
                } else {
                    let errorMessage = 'Sign up failed. Please try again.';
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }
                    showToast(errorMessage, 'error');
                    
                    // Reset button
                    submitBtn.disabled = false;
                    loaderIcon?.classList.add('hidden');
                    if (btnText) btnText.textContent = 'Sign Up';
                }
            } catch (error) {
                console.error('Registration error:', error);
                showToast('Network error. Please check your connection and try again.', 'error');
                submitBtn.disabled = false;
                loaderIcon?.classList.add('hidden');
                if (btnText) btnText.textContent = 'Sign Up';
            }
        });
    </script>
</body>

</html>