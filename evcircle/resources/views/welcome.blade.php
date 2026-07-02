<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EVCircle | Smart EV Charging Network - Sri Lanka</title>
    <!-- TailwindCSS + Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { evgreen: '#00E676', evdark: '#0A0A0A' },
                    animation: { 'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite', 'float': 'float 6s ease-in-out infinite' },
                    keyframes: { float: { '0%, 100%': { transform: 'translateY(0px)' }, '50%': { transform: 'translateY(-12px)' } } }
                }
            }
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background: #000000;
            font-family: 'Inter', system-ui, sans-serif;
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

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0a0a0a;
        }

        ::-webkit-scrollbar-thumb {
            background: #00E676;
            border-radius: 8px;
        }

        .phone-glow {
            box-shadow: 0 20px 40px -12px rgba(0, 230, 118, 0.3), 0 0 0 6px rgba(0, 230, 118, 0.15);
        }

        .map-dot-pulse {
            box-shadow: 0 0 0 0 rgba(0, 230, 118, 0.7);
            animation: pulse-green 1.8s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 230, 118, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(0, 230, 118, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 230, 118, 0);
            }
        }

        .leaflet-container {
            background: #0a0f0a;
            border-radius: 1.5rem;
        }

        .leaflet-popup-content-wrapper {
            background: #1a1f1a;
            color: white;
            border-left: 4px solid #00E676;
            border-radius: 12px;
        }

        .contact-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .contact-input:focus {
            border-color: #00E676;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 230, 118, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        /* District Filter Styles */
        .district-filter-container {
            position: relative;
            z-index: 1000;
            margin-bottom: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .district-filter-select {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            border: 2px solid #00E676;
            color: white;
            padding: 0.625rem 2.5rem 0.625rem 1.25rem;
            border-radius: 12px;
            font-size: 0.9rem;
            min-width: 200px;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300E676' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            transition: all 0.3s ease;
        }
        
        .district-filter-select:hover {
            border-color: rgba(0, 230, 118, 0.6);
            background: rgba(0, 0, 0, 0.9);
        }
        
        .district-filter-select:focus {
            outline: none;
            border-color: #00E676;
            box-shadow: 0 0 0 2px rgba(0, 230, 118, 0.2);
        }
        
        .district-filter-select option {
            background: #1a1f1a;
            color: white;
            padding: 8px;
        }
        
        .filter-stats {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 230, 118, 0.3);
            padding: 0.625rem 1.25rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #9ca3af;
        }
        
        .filter-stats span {
            color: #00E676;
            font-weight: bold;
        }
        
        .clear-filter-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #9ca3af;
            padding: 0.625rem 1rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .clear-filter-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .clear-filter-btn.hidden {
            display: none;
        }

        /* Success Progress Bar */
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        .animate-progress {
            animation: progress 3s ease-in-out infinite;
        }

        /* Infinite Scroll Animation */
        @keyframes scroll {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
        .animate-scroll {
            animation: scroll 20s linear infinite;
        }
        .animate-scroll:hover {
            animation-play-state: paused;
        }

        /* Float Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        /* Custom Scrollbar Hide */
        .max-h-\[200px\] {
            max-height: 200px;
            overflow-y: hidden;
        }

        /* Pulse Animation for Button */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Glow Effect */
        .phone-glow {
            box-shadow: 0 0 30px rgba(34, 197, 94, 0.1), 0 0 60px rgba(34, 197, 94, 0.05);
        }
    </style>
</head>

<body class="bg-black text-white overflow-x-hidden">

    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 top-0 transition-all duration-300 nav-transparent">
        <div class="max-w-screen-2xl flex flex-wrap items-center justify-between mx-auto p-4 md:px-6">
            <a href="#home" class="flex items-center space-x-3 group smooth-scroll">
                <img src="https://evcircle.lk/logo.png"
                    class="h-8 w-8 rounded-full group-hover:scale-110 transition-transform" alt="EVCircle Logo">
                <span
                    class="text-xl font-bold tracking-tight bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">EVCircle</span>
            </a>
            <div class="flex md:order-2 gap-3 items-center">
                <button id="ctaBtn" onclick="goToPage()"
                    class="text-white bg-evgreen hover:bg-green-400 transition-all duration-300 font-medium rounded-full px-5 py-2 shadow-lg shadow-evgreen/30 text-sm md:text-base flex items-center">

                    <i class="fas fa-bolt mr-1"></i>
                    <span id="ctaText" class="inline-block w-[95px] text-left"></span>
                </button>

                <script>
                    const textEl = document.getElementById("ctaText");

                    const items = [
                        { text: "Get Started", link: "{{ route('login') }}" },
                        { text: "Add Charger", link: "{{ route('login') }}" }
                    ];

                    let wordIndex = 0;
                    let charIndex = 0;
                    let isDeleting = false;

                    function typeEffect() {
                        const currentWord = items[wordIndex].text;

                        if (!isDeleting) {
                            textEl.innerText = currentWord.substring(0, charIndex + 1);
                            charIndex++;

                            if (charIndex === currentWord.length) {
                                isDeleting = true;
                                setTimeout(typeEffect, 1200);
                                return;
                            }
                        } else {
                            textEl.innerText = currentWord.substring(0, charIndex - 1);
                            charIndex--;

                            if (charIndex === 0) {
                                isDeleting = false;
                                wordIndex = (wordIndex + 1) % items.length;
                            }
                        }

                        setTimeout(typeEffect, isDeleting ? 60 : 100);
                    }

                    typeEffect();

                    function goToPage() {
                        window.location.href = items[wordIndex].link;
                    }
                </script>
                <button id="mobile-menu-button" type="button"
                    class="md:hidden inline-flex items-center p-2 w-10 h-10 justify-center text-white rounded-full hover:bg-white/10 transition focus:outline-none">
                    <span class="sr-only">Open menu</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            <div id="mobile-menu" class="hidden w-full md:flex md:w-auto md:order-1 mt-4 md:mt-0">
                <ul
                    class="flex flex-col md:flex-row md:space-x-8 space-y-3 md:space-y-0 bg-black/60 md:bg-transparent rounded-2xl p-5 md:p-0 backdrop-blur-md md:backdrop-blur-none border border-white/10 md:border-0">
                    <li><a href="#home" class="text-gray-200 hover:text-evgreen transition smooth-scroll">Home</a></li>
                    <li><a href="#map-section" class="text-gray-200 hover:text-evgreen transition smooth-scroll">Map</a>
                    </li>
                    <li><a href="#seller-mode" class="text-gray-200 hover:text-evgreen transition smooth-scroll">Seller
                            Mode</a></li>
                    <li><a href="#testimonials"
                            class="text-gray-200 hover:text-evgreen transition smooth-scroll">Reviews</a></li>
                    <li><a href="#contact" class="text-gray-200 hover:text-evgreen transition smooth-scroll">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home"
        class="relative flex flex-col lg:flex-row items-center justify-between min-h-screen px-6 pt-28 pb-16 md:pt-32 gap-12 max-w-screen-2xl mx-auto">
        <div
            class="absolute w-[600px] h-[600px] bg-evgreen/15 blur-[140px] top-[-180px] left-[-200px] rounded-full animate-pulse-slow">
        </div>
        <div class="flex-1 text-center lg:text-left z-10 max-w-xl">
            <div
                class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-6">
                <span class="relative flex h-2 w-2"><span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-evgreen opacity-75"></span><span
                        class="relative inline-flex rounded-full h-2 w-2 bg-evgreen"></span></span>
                <span class="text-xs font-mono text-evgreen tracking-wide">🇱🇰 SRI LANKA WIDE NETWORK</span>
            </div>
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold leading-tight tracking-tight">Power Every Journey in
                <span class="text-evgreen bg-gradient-to-r from-evgreen to-green-300 bg-clip-text text-transparent">Sri
                    Lanka</span></h1>
            <p class="mt-6 text-gray-300 text-lg md:text-xl leading-relaxed">From Colombo to Kandy, Galle to Jaffna
                EVCircle brings ultra-fast EV charging across the island. Seamless, stable-24/7 live, and always ready
                for you.</p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="#map-section"
                    class="group px-7 py-3.5 bg-evgreen text-black font-semibold rounded-xl hover:bg-green-400 transition-all duration-300 flex items-center justify-center gap-2 shadow-xl shadow-evgreen/30 smooth-scroll"><i
                        class="fas fa-map-marked-alt"></i> Explore Live Map <i
                        class="fas fa-arrow-right group-hover:translate-x-1 transition"></i></a>
                <a href="javascript:void(0)" onclick="downloadApp()"
                    class="px-7 py-3.5 border-2 border-evgreen/70 text-evgreen rounded-xl hover:bg-evgreen hover:text-black transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fab fa-android"></i>
                    <i class="fab fa-apple"></i>
                    Download App
                </a>

                <script>
                    function downloadApp() {
                        const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

                        if (isIOS) {
                            window.location.href = 'https://apps.apple.com/app/org.jepsoft.evcircle';
                        } else {
                            window.location.href = 'https://play.google.com/store/apps/details?id=com.jepsoft.evcircle';
                        }
                    }
                </script>
            </div>
            <div class="mt-12 grid grid-cols-3 gap-4 sm:gap-8 text-center">
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-3 border border-white/10">
                    <div class="text-white text-xl md:text-2xl font-bold flex items-center justify-center gap-1"><i
                            class="fas fa-map-marker-alt text-evgreen"></i> <span id="stationCount">2</span><span
                            class="text-evgreen">+</span></div>
                    <div class="text-gray-400 text-sm">Active Stations</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-3 border border-white/10">
                    <div class="text-white text-xl md:text-xl font-bold flex items-center justify-center gap-1"><i
                            class="far fa-clock text-evgreen"></i> 24/7</div>
                    <div class="text-gray-400 text-sm">Support</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-3 border border-white/10">
                    <div class="text-white text-xl md:text-xl font-bold flex items-center justify-center gap-1"><i
                            class="fas fa-solar-panel text-evgreen"></i> 100%</div>
                    <div class="text-gray-400 text-sm">Uptime</div>
                </div>
            </div>
        </div>
        <!-- Phone Mockup -->
       <div class="flex-1 flex justify-center lg:justify-end z-10 relative">
    <div class="relative animate-float">
        <div
            class="relative w-[280px] sm:w-[320px] md:w-[360px] rounded-[3rem] bg-gradient-to-br from-gray-900 to-black border-4 border-gray-700/50 shadow-2xl phone-glow">
            <!-- Phone Notch -->
            <div
                class="absolute top-0 left-1/2 transform -translate-x-1/2 w-36 h-7 bg-black rounded-b-2xl flex justify-center items-center gap-1 z-20">
                <div class="w-2 h-2 rounded-full bg-evgreen/70"></div>
                <div class="w-16 h-3 bg-gray-800 rounded-full"></div>
            </div>
            
            <div class="rounded-[2.5rem] overflow-hidden bg-gradient-to-b from-black to-gray-900">
                <div class="pt-10 pb-6 px-4 flex flex-col items-center">
                    
                    <!-- Success Notification Banner -->
                    <div class="w-full bg-gradient-to-r from-evgreen/20 to-evgreen/5 rounded-2xl p-3 mb-3 backdrop-blur-sm border border-evgreen/40 relative overflow-hidden">
                        <div class="absolute inset-0 bg-evgreen/5 animate-pulse"></div>
                        <div class="flex items-center gap-3 relative z-10">
                            <div class="w-10 h-10 bg-evgreen/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check-circle text-evgreen text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-white">Charger Added!</p>
                                <p class="text-[10px] text-evgreen">Successfully registered</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[8px] text-gray-400">Just now</span>
                                <span class="w-2 h-2 bg-evgreen rounded-full animate-ping mt-1"></span>
                            </div>
                        </div>
                        <!-- Success Progress Bar -->
                        <div class="w-full h-0.5 bg-evgreen/30 mt-2 rounded-full overflow-hidden relative z-10">
                            <div class="h-full bg-evgreen rounded-full animate-progress"></div>
                        </div>
                    </div>

                    <!-- Infinite Success Messages Feed -->
                    <div class="w-full max-h-[200px] overflow-y-hidden relative">
                        <div class="space-y-2 animate-scroll">
                            <!-- Message 1 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-bolt text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">Tesla Charger added</p>
                                    <p class="text-[10px] text-gray-400">Colombo • 2 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 2 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-plug text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">ABB Fast Charger</p>
                                    <p class="text-[10px] text-gray-400">Galle • 5 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 3 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-charging-station text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">EV Box 22kW</p>
                                    <p class="text-[10px] text-gray-400">Kandy • 8 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 4 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-bolt text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">ChargePoint CPE250</p>
                                    <p class="text-[10px] text-gray-400">Negombo • 12 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 5 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-plug text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">Wallbox Pulsar Plus</p>
                                    <p class="text-[10px] text-gray-400">Colombo 07 • 18 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 6 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-charging-station text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">Zappi EV Charger</p>
                                    <p class="text-[10px] text-gray-400">Matara • 25 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 7 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-bolt text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">Easee Home Charger</p>
                                    <p class="text-[10px] text-gray-400">Kurunegala • 32 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Message 8 -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-plug text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">Hypervolt Home 3.0</p>
                                    <p class="text-[10px] text-gray-400">Anuradhapura • 41 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>

                            <!-- Duplicate messages for seamless loop -->
                            <!-- Message 1 Duplicate -->
                            <div class="bg-white/5 rounded-xl p-3 border border-evgreen/20 flex items-center gap-3">
                                <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-bolt text-evgreen text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-white truncate">Tesla Charger added</p>
                                    <p class="text-[10px] text-gray-400">Colombo • 2 min ago</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-evgreen text-[10px]"></i>
                                    <span class="text-[8px] text-evgreen">Done</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Stats -->
                    <div class="w-full grid grid-cols-3 gap-2 mt-4">
                        <div class="bg-white/5 rounded-xl p-2.5 text-center">
                            <p class="text-lg font-bold text-evgreen">8</p>
                            <p class="text-[8px] text-gray-400">Total Chargers</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-2.5 text-center">
                            <p class="text-lg font-bold text-evgreen">+6</p>
                            <p class="text-[8px] text-gray-400">New This Week</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-2.5 text-center">
                            <p class="text-lg font-bold text-evgreen">✓</p>
                            <p class="text-[8px] text-gray-400">All Verified</p>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <a href="{{ route('dashboard') }}"
                        class="w-full mt-4 bg-evgreen/90 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 text-sm shadow-lg transition hover:bg-evgreen group relative overflow-hidden">
                        <span class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500"></span>
                        <i class="fas fa-plus-circle"></i>
                        Add Your Charger Now
                        <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full animate-pulse">+</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
    </section>

    <!-- About EVCircle App -->
    
    
    <!-- Map Section -->
    <section id="map-section" class="relative max-w-7xl mx-auto px-6 py-16 z-10 scroll-mt-24">
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center gap-2 bg-evgreen/10 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                <i class="fas fa-map-pin text-evgreen text-xs"></i><span
                    class="text-xs font-mono text-evgreen tracking-wide">LIVE CHARGER LOCATIONS</span></div>
            <h2
                class="text-3xl md:text-5xl font-bold bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">
                Find Your Nearest EV Charger</h2>
            <p class="text-gray-400 mt-3 max-w-2xl mx-auto">Real-time locations. Fast chargers (≥50kW) marked with
                lightning bolt.</p>
        </div>
        
        <!-- District Filter Dropdown - Added above map -->
        <div class="district-filter-container">
            <div class="filter-stats" id="filterStats">
                <i class="fas fa-map-marker-alt text-evgreen"></i>
                <span id="visibleCount">0</span> chargers visible
            </div>
            <select id="districtFilter" class="district-filter-select">
                <option value="all">All Sri Lanka</option>
                <option value="Ampara">Ampara</option> 
                <option value="Anuradhapura">Anuradhapura</option>
                <option value="Badulla">Badulla</option>
                <option value="Batticaloa">Batticaloa</option>
                <option value="Colombo">Colombo</option>
                <option value="Galle">Galle</option>
                <option value="Gampaha">Gampaha</option>
                <option value="Hambantota">Hambantota</option>
                <option value="Jaffna">Jaffna</option>
                <option value="Kalutara">Kalutara</option>
                <option value="Kandy">Kandy</option>
                <option value="Kegalle">Kegalle</option>
                <option value="Kilinochchi">Kilinochchi</option>
                <option value="Kurunegala">Kurunegala</option>
                <option value="Mannar">Mannar</option>
                <option value="Matale">Matale</option>
                <option value="Matara">Matara</option>
                <option value="Monaragala">Monaragala</option>
                <option value="Mullaitivu">Mullaitivu</option>
                <option value="Nuwara Eliya">Nuwara Eliya</option>
                <option value="Polonnaruwa">Polonnaruwa</option>
                <option value="Puttalam">Puttalam</option>
                <option value="Ratnapura">Ratnapura</option>
                <option value="Trincomalee">Trincomalee</option>
                <option value="Vavuniya">Vavuniya</option>
            </select>
            <button id="clearFilter" class="clear-filter-btn hidden">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
        
        <div class="relative w-full rounded-2xl overflow-hidden border border-evgreen/30 shadow-2xl">
            <div id="evMap" style="height: 520px; width: 100%;"></div>
            <div
                class="absolute bottom-4 right-4 bg-black/80 backdrop-blur-md rounded-xl p-3 z-20 border border-evgreen/40 text-xs flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-evgreen"></div><span>Standard Charger</span>
                </div>
                <div class="flex items-center gap-2"><i class="fas fa-bolt text-yellow-400 text-sm"></i><span>Fast
                        Charger (≥50kW)</span></div>
            </div>
        </div>
    </section>
<section id="about-app" class="relative max-w-7xl mx-auto px-6 py-20">
        <div class="text-center mb-14">
            <div
                class="inline-flex items-center gap-2 bg-evgreen/10 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                <i class="fas fa-mobile-alt text-evgreen text-xs"></i>
                <span class="text-xs font-mono text-evgreen tracking-wide">THE EVCIRCLE APP</span>
            </div>

            <h2 class="text-4xl md:text-5xl font-bold">
                Everything You Need For
                <span class="text-evgreen">EV Charging</span>
            </h2>

            <p class="text-gray-400 max-w-3xl mx-auto mt-4 text-lg">
                EVCircle connects EV drivers and charger owners across Sri Lanka through one powerful platform.
                Discover chargers, reserve charging slots, monitor availability in real-time, and manage your EV journey
                effortlessly.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">

            <!-- Feature 1 -->
            <div
                class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:border-evgreen/40 transition-all duration-300">
                <div class="w-16 h-16 bg-evgreen/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-map-marked-alt text-evgreen text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Live Charger Map</h3>
                <p class="text-gray-400">
                    Find nearby charging stations instantly with live availability,
                    pricing details, connector types, and charging speeds.
                </p>
            </div>

            <!-- Feature 2 -->
            <div
                class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:border-evgreen/40 transition-all duration-300">
                <div class="w-16 h-16 bg-evgreen/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-calendar-check text-evgreen text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Reserve Chargers</h3>
                <p class="text-gray-400">
                    Book charging slots before you arrive and avoid waiting in line.
                    Charge with confidence wherever your journey takes you.
                </p>
            </div>

            <!-- Feature 3 -->
            <div
                class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:border-evgreen/40 transition-all duration-300">
                <div class="w-16 h-16 bg-evgreen/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-wallet text-evgreen text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Cashless Payments</h3>
                <p class="text-gray-400">
                    Pay securely through the app and track your charging history,
                    receipts, and spending in one convenient place.
                </p>
            </div>

            <!-- Feature 4 -->
            <div
                class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:border-evgreen/40 transition-all duration-300">
                <div class="w-16 h-16 bg-evgreen/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-bolt text-evgreen text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Real-Time Status</h3>
                <p class="text-gray-400">
                    Know exactly which chargers are available, busy, or offline
                    before reaching the station.
                </p>
            </div>

            <!-- Feature 5 -->
            <div
                class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:border-evgreen/40 transition-all duration-300">
                <div class="w-16 h-16 bg-evgreen/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-store text-evgreen text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Seller Dashboard</h3>
                <p class="text-gray-400">
                    Charger owners can manage stations, monitor bookings,
                    earnings, and customer activity from one dashboard.
                </p>
            </div>

            <!-- Feature 6 -->
            <div
                class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:border-evgreen/40 transition-all duration-300">
                <div class="w-16 h-16 bg-evgreen/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-shield-alt text-evgreen text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Reliable & Secure</h3>
                <p class="text-gray-400">
                    Built for Sri Lanka's growing EV community with secure access,
                    reliable performance, and 24/7 platform support.
                </p>
            </div>

        </div>
    </section>
    <!-- Smart Seller Mode -->
    <section id="seller-mode"
        class="relative max-w-7xl mx-auto px-6 py-16 z-10 border-t border-b border-evgreen/20 my-4 scroll-mt-20">
        <div class="text-center mb-12">
            <div
                class="inline-flex items-center gap-2 bg-evgreen/10 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                <i class="fas fa-store text-evgreen text-xs"></i><span
                    class="text-xs font-mono text-evgreen tracking-wide">FOR CHARGER OWNERS</span></div>
            <h2
                class="text-3xl md:text-5xl font-bold bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">
                Seller Mode</h2>
            <p class="text-gray-400 max-w-2xl mx-auto mt-3">Manage your EV chargers, track bookings, and view your
                earnings all in one place.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-evgreen/30">
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="bg-evgreen/20 p-3 rounded-xl"><i
                                class="fas fa-charging-station text-evgreen text-2xl"></i></div>
                        <div>
                            <h4 class="text-xl font-semibold">Manage Chargers in Real-time</h4>
                            <p class="text-gray-400 text-sm">Add, edit, or pause your charging stations dynamically.
                                Update availability and pricing from your dashboard.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="bg-evgreen/20 p-3 rounded-xl"><i
                                class="fas fa-calendar-check text-evgreen text-2xl"></i></div>
                        <div>
                            <h4 class="text-xl font-semibold">Track Bookings & Revenue</h4>
                            <p class="text-gray-400 text-sm">View live booking history, daily earnings, and performance
                                analytics.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="bg-evgreen/20 p-3 rounded-xl"><i
                                class="fas fa-chart-line text-evgreen text-2xl"></i></div>
                        <div>
                            <h4 class="text-xl font-semibold">Seller Insights Hub</h4>
                            <p class="text-gray-400 text-sm">Get detailed reports, peak usage hours, and customer
                                feedback.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8"><button
                        class="bg-evgreen text-white font-bold py-2.5 px-6 rounded-xl flex items-center gap-2 hover:bg-green-400 transition"><i
                            class="fas fa-user-plus"></i>Seller Dashboard</button></div>
            </div>
            <div class="flex justify-center">
                <div
                    class="w-full max-w-sm bg-gradient-to-br from-gray-900 to-black rounded-2xl p-5 border border-evgreen/40">
                    <div class="flex justify-between border-b border-gray-700 pb-3 mb-4"><span
                            class="font-bold text-evgreen"><i class="fas fa-chart-simple"></i> Seller
                            Dashboard</span><span class="text-xs bg-evgreen/20 px-2 py-1 rounded-full">Live</span></div>
                    <div class="space-y-3">
                        <div class="flex justify-between"><span>Active Chargers:</span><span
                                class="text-evgreen font-bold">2 Stations</span></div>
                        <div class="flex justify-between"><span>Today's Bookings:</span><span>8 sessions</span></div>
                        <div class="flex justify-between"><span>Estimated Earnings:</span><span
                                class="text-evgreen font-bold">LKR 18,500</span></div>
                        <div class="w-full bg-gray-800 h-1.5 rounded-full">
                            <div class="bg-evgreen w-2/3 h-1.5 rounded-full"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3">
                            <div class="bg-white/5 rounded-lg p-2 text-center"><i
                                    class="fas fa-wallet text-evgreen"></i>
                                <p class="text-xs">Bookings</p><span class="text-evgreen text-sm font-bold">150 +</span>
                            </div>
                            <div class="bg-white/5 rounded-lg p-2 text-center"><i class="fas fa-users text-evgreen"></i>
                                <p class="text-xs">Customers</p><span class="text-white text-sm font-bold">300 +</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="relative max-w-7xl mx-auto px-6 py-16 z-10 scroll-mt-20">
        <div class="text-center mb-10">
            <div
                class="inline-flex items-center gap-2 bg-evgreen/10 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                <i class="fas fa-star text-evgreen text-xs"></i><span
                    class="text-xs font-mono text-evgreen tracking-wide">REAL STORIES</span></div>
            <h2
                class="text-3xl md:text-5xl font-bold bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">
                What EV Owners Say</h2>
            <p class="text-gray-400 max-w-2xl mx-auto mt-3">Trusted by EV drivers across Sri Lanka.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-full bg-evgreen/20 flex items-center justify-center">
                        <i class="fas fa-user-circle text-evgreen text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold">Kavindu Ravishan</h4>
                        <p class="text-xs text-evgreen"><i class="fas fa-map-marker-alt"></i> Hathporuwa</p>
                    </div>
                </div>
                <div class="flex mb-2 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-300 text-sm">
                    "EVCircle makes EV charging so simple. I can locate chargers easily and check live status anytime!"
                </p>
            </div>

            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-full bg-evgreen/20 flex items-center justify-center">
                        <i class="fas fa-user-circle text-evgreen text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold">Amaya Perera</h4>
                        <p class="text-xs text-evgreen"><i class="fas fa-map-marker-alt"></i> Colombo</p>
                    </div>
                </div>
                <div class="flex mb-2 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-300 text-sm">
                    "The best EV platform! It helps me plan trips confidently without worrying about battery range."
                </p>
            </div>

            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-full bg-evgreen/20 flex items-center justify-center">
                        <i class="fas fa-user-circle text-evgreen text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold">Nimal Silva</h4>
                        <p class="text-xs text-evgreen"><i class="fas fa-map-marker-alt"></i> Kandy</p>
                    </div>
                </div>
                <div class="flex mb-2 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p class="text-gray-300 text-sm">
                    "Very reliable app. The charger availability information is accurate, and payments are always
                    seamless."
                </p>
            </div>

            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-full bg-evgreen/20 flex items-center justify-center">
                        <i class="fas fa-user-circle text-evgreen text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold">Tharushi Fernando</h4>
                        <p class="text-xs text-evgreen"><i class="fas fa-map-marker-alt"></i> Galle</p>
                    </div>
                </div>
                <div class="flex mb-2 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-300 text-sm">
                    "I love the clean design and quick booking process. Finding nearby chargers has never been easier."
                </p>
            </div>

            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-full bg-evgreen/20 flex items-center justify-center">
                        <i class="fas fa-user-circle text-evgreen text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold">Supul Namina</h4>
                        <p class="text-xs text-evgreen"><i class="fas fa-map-marker-alt"></i> Nadugala</p>
                    </div>
                </div>
                <div class="flex mb-2 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p class="text-gray-300 text-sm">
                    "The real-time charger updates save me a lot of time. I always know where to charge before I leave."
                </p>
            </div>

            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-full bg-evgreen/20 flex items-center justify-center">
                        <i class="fas fa-user-circle text-evgreen text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold">Maneth Dulwan</h4>
                        <p class="text-xs text-evgreen"><i class="fas fa-map-marker-alt"></i> Matara</p>
                    </div>
                </div>
                <div class="flex mb-2 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-300 text-sm">
                    "EVCircle has become my go-to EV companion. The navigation, booking, and payment features work
                    perfectly together."
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section with WhatsApp -->
    <section id="contact" class="relative max-w-7xl mx-auto px-6 py-16 z-10 scroll-mt-24">
        <div class="text-center mb-10">
            <div
                class="inline-flex items-center gap-2 bg-evgreen/10 backdrop-blur-sm rounded-full px-4 py-1.5 border border-evgreen/30 mb-4">
                <i class="fab fa-whatsapp text-evgreen text-xs"></i><span
                    class="text-xs font-mono text-evgreen tracking-wide">GET IN TOUCH</span></div>
            <h2
                class="text-3xl md:text-5xl font-bold bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">
                Chat With Us on WhatsApp</h2>
            <p class="text-gray-400 mt-3">Click the button below to send us a message directly on WhatsApp. We reply
                within minutes!</p>
        </div>
        <div class="max-w-3xl mx-auto bg-white/5 backdrop-blur-md rounded-2xl p-8 border border-evgreen/30 text-center">
            <i class="fab fa-whatsapp text-6xl text-green-500 mb-4"></i>
            <h3 class="text-2xl font-bold mb-3">Quick Support via WhatsApp</h3>
            <p class="text-gray-300 mb-6">Need help finding a charger, have a question about billing, or want to become
                a seller? Our team is just one tap away.</p>
            <a href="https://wa.me/94717566861?text=Hello%20EVCircle%2C%20I%20need%20help%20with%20EV%20charging"
                target="_blank"
                class="inline-flex items-center gap-3 bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 text-lg shadow-xl shadow-green-600/30">
                <i class="fab fa-whatsapp text-2xl"></i> Message us on WhatsApp
            </a>
            <p class="text-gray-400 text-sm mt-6">Call us directly: <a href="tel:+94776231796"
                    class="text-evgreen hover:underline">071 756 6861</a> (24/7 Support Line)</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-800/40 pt-12 pb-8 relative z-10 bg-black/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-evgreen/20 rounded-full flex items-center justify-center"><img
                                src="https://evcircle.lk/logo.png" alt="EVCircle Logo"
                                class="w-full h-full object-contain"></div><span
                            class="font-bold text-xl">EVCircle</span>
                    </div>
                    <p class="text-gray-400 text-sm">Empowering Sri Lanka's electric future.</p>
                </div>
                <div>
                    <h5 class="text-white font-semibold mb-3">Network</h5>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#map-section" class="hover:text-evgreen smooth-scroll">Charger Map</a></li>
                        <li><a href="#" class="hover:text-evgreen">Add Your Charger</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-white font-semibold mb-3">Support</h5>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#contact" class="hover:text-evgreen smooth-scroll">Contact Us</a></li>
                        <li><a href="#" class="hover:text-evgreen">Help Center</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-white font-semibold mb-3">Legal</h5>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-evgreen">Privacy</a></li>
                        <li><a href="#" class="hover:text-evgreen">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div
                class="border-t border-gray-800/40 pt-6 flex flex-col md:flex-row justify-between items-center text-gray-500 text-xs">
                <div class="flex gap-6"><a href="#" class="hover:text-evgreen"><i class="fab fa-twitter"></i></a><a
                        href="#" class="hover:text-evgreen"><i class="fab fa-instagram"></i></a><a href="#"
                        class="hover:text-evgreen"><i class="fab fa-linkedin"></i></a></div>
                <p>© 2025 EVCircle – Powering Sri Lanka with Seamless EV Charging. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Map Script - WITH District Filter -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let map, allMarkers = [], allChargerData = [];
            let markerLayerGroup;

            // Initialize map
            map = L.map('evMap').setView([7.8731, 80.7718], 8);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { 
                attribution: '&copy; OSM & CartoDB', 
                subdomains: 'abcd' 
            }).addTo(map);

            // Custom icons
            const standardIcon = L.divIcon({ 
                html: '<div class="flex items-center justify-center w-8 h-8 bg-evgreen rounded-full shadow-lg border-2 border-white"><i class="fas fa-charging-station text-black text-sm"></i></div>', 
                className: 'custom-marker', 
                iconSize: [32, 32], 
                popupAnchor: [0, -16] 
            });
            
            const fastIcon = L.divIcon({ 
                html: '<div class="flex items-center justify-center w-9 h-9 bg-yellow-500 rounded-full shadow-lg border-2 border-white animate-pulse"><i class="fas fa-bolt text-black text-sm"></i></div>', 
                className: 'custom-marker', 
                iconSize: [36, 36], 
                popupAnchor: [0, -18] 
            });

            // Create marker layer group
            markerLayerGroup = L.layerGroup().addTo(map);

            // Helper function to escape HTML
            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            // Sri Lanka district boundaries - approximate center points for each district
            const districtCenters = {
                'Ampara': [7.2915, 81.6723],
                'Anuradhapura': [8.3114, 80.4037],
                'Badulla': [6.9934, 81.0550],
                'Batticaloa': [7.7178, 81.7004],
                'Colombo': [6.9271, 79.8612],
                'Galle': [6.0535, 80.2210],
                'Gampaha': [7.0873, 79.9955],
                'Hambantota': [6.1241, 81.1263],
                'Jaffna': [9.6615, 80.0255],
                'Kalutara': [6.5851, 79.9607],
                'Kandy': [7.2906, 80.6337],
                'Kegalle': [7.2512, 80.3399],
                'Kilinochchi': [9.3964, 80.3960],
                'Kurunegala': [7.4791, 80.3656],
                'Mannar': [8.9830, 79.9138],
                'Matale': [7.4670, 80.6234],
                'Matara': [5.9482, 80.5350],
                'Monaragala': [6.8724, 81.3484],
                'Mullaitivu': [9.2678, 80.7880],
                'Nuwara Eliya': [6.9497, 80.7891],
                'Polonnaruwa': [7.9393, 81.0024],
                'Puttalam': [8.0331, 79.8350],
                'Ratnapura': [6.7055, 80.3843],
                'Trincomalee': [8.5779, 81.2352],
                'Vavuniya': [8.7491, 80.4982]
            };

            // Function to find the nearest district based on coordinates
            function findNearestDistrict(lat, lng) {
                let nearestDistrict = 'Unknown';
                let minDistance = Infinity;

                for (const [district, center] of Object.entries(districtCenters)) {
                    const dlat = lat - center[0];
                    const dlng = lng - center[1];
                    const distance = Math.sqrt(dlat * dlat + dlng * dlng);
                    
                    if (distance < minDistance) {
                        minDistance = distance;
                        nearestDistrict = district;
                    }
                }

                // Only return district if it's reasonably close (within ~1 degree)
                if (minDistance < 1.5) {
                    return nearestDistrict;
                }
                return 'Unknown';
            }

            // Function to get district from location data
            function extractDistrict(charger) {
                // First try: check if location field contains a district name
                if (charger.location) {
                    const locationStr = String(charger.location);
                    const districts = Object.keys(districtCenters);
                    
                    for (let district of districts) {
                        if (locationStr.toLowerCase().includes(district.toLowerCase())) {
                            return district;
                        }
                    }

                    // If location is coordinates, try to parse them
                    const coordMatch = locationStr.match(/([-+]?\d+\.\d+)\s*[,，]\s*([-+]?\d+\.\d+)/);
                    if (coordMatch) {
                        const lat = parseFloat(coordMatch[1]);
                        const lng = parseFloat(coordMatch[2]);
                        if (!isNaN(lat) && !isNaN(lng)) {
                            const district = findNearestDistrict(lat, lng);
                            if (district !== 'Unknown') {
                                return district;
                            }
                        }
                    }
                }

                // Second try: use coordinates from latitude/longitude fields
                if (charger.latitude && charger.longitude) {
                    const lat = parseFloat(charger.latitude);
                    const lng = parseFloat(charger.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const district = findNearestDistrict(lat, lng);
                        if (district !== 'Unknown') {
                            return district;
                        }
                    }
                }

                return 'Unknown';
            }

            // Function to update map with filtered markers
            function updateMapFilter(selectedDistrict) {
                // Clear existing markers
                markerLayerGroup.clearLayers();
                
                let visibleCount = 0;
                const bounds = [];

                allChargerData.forEach(charger => {
                    const chargerDistrict = extractDistrict(charger);
                    
                    // Check if this charger should be shown
                    const shouldShow = selectedDistrict === 'all' || chargerDistrict === selectedDistrict;
                    
                    if (shouldShow) {
                        // Get coordinates from latitude/longitude or parse from location
                        let lat, lng;
                        
                        if (charger.latitude && charger.longitude) {
                            lat = parseFloat(charger.latitude);
                            lng = parseFloat(charger.longitude);
                        } else if (charger.location) {
                            const coordMatch = String(charger.location).match(/([-+]?\d+\.\d+)\s*[,，]\s*([-+]?\d+\.\d+)/);
                            if (coordMatch) {
                                lat = parseFloat(coordMatch[1]);
                                lng = parseFloat(coordMatch[2]);
                            }
                        }

                        if (isNaN(lat) || isNaN(lng)) {
                            return;
                        }

                        // Determine if fast charger
                        const powerKW = parseFloat(charger.power_output) || 0;
                        const isFast = (powerKW >= 50) || (charger.charger_type === 'fast');
                        const icon = isFast ? fastIcon : standardIcon;

                        // Availability status
                        const now = new Date();
                        const isWithinSchedule = charger.active_from && charger.active_until && 
                            new Date(charger.active_from) <= now && now <= new Date(charger.active_until);
                        const isAvailable = isWithinSchedule;
                        const availText = isAvailable
                            ? '<span class="text-evgreen text-xs"><i class="fas fa-circle"></i> Available Now</span>'
                            : '<span class="text-red-400 text-xs"><i class="fas fa-circle"></i> Currently Unavailable</span>';
                        
                        const activeFrom = charger.active_from ? new Date(charger.active_from).toLocaleDateString() : 'N/A';
                        const activeUntil = charger.active_until ? new Date(charger.active_until).toLocaleDateString() : 'N/A';

                        // Get location display name
                        let locationDisplay = charger.location || 'Unknown Location';
                        // If location is coordinates, try to show a better name
                        if (locationDisplay.match(/([-+]?\d+\.\d+)\s*[,，]\s*([-+]?\d+\.\d+)/)) {
                            locationDisplay = charger.station_name || 'EV Charger';
                        }

                        // Build popup
                        const popupContent = `
                            <div class="p-2 min-w-[220px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-charging-station text-evgreen text-lg"></i>
                                    <h3 class="font-bold text-white text-base">${escapeHtml(charger.station_name || 'EV Charger')}</h3>
                                </div>
                                <p class="text-gray-300 text-xs mb-1"><i class="fas fa-location-dot text-evgreen mr-1"></i> ${escapeHtml(locationDisplay)}</p>
                                <p class="text-gray-300 text-xs mb-1"><i class="fas fa-bolt text-yellow-400 mr-1"></i> ${escapeHtml(charger.charger_type || 'Standard')} · ${powerKW} kW (${escapeHtml(charger.power_type || 'AC')})</p>
                                <p class="text-gray-300 text-xs mb-1"><i class="fas fa-plug mr-1"></i> Connector: ${escapeHtml(charger.connector_type || 'Type 2')}</p>
                                <p class="text-evgreen font-bold text-sm mb-2">💰 LKR ${parseFloat(charger.price_per_kwh || 0).toFixed(2)} / kWh</p>
                                ${charger.promo_code ? `<p class="text-yellow-400 text-xs mb-2"><i class="fas fa-tag"></i> Promo: ${escapeHtml(charger.promo_code)}</p>` : ''}
                                <div class="flex justify-between items-center mt-1">
                                    ${availText}
                                    <button class="bg-evgreen/20 hover:bg-evgreen text-evgreen hover:text-black px-2 py-1 rounded text-xs transition"><i class="fas fa-bolt"></i> Reserve</button>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-2"><i class="fas fa-calendar-alt"></i> Active: ${activeFrom} - ${activeUntil}</p>
                                <p class="text-[10px] text-gray-500"><i class="fas fa-map-marker-alt"></i> ${lat.toFixed(5)}°N, ${lng.toFixed(5)}°E</p>
                                <p class="text-[10px] text-evgreen mt-1"><i class="fas fa-flag"></i> District: ${chargerDistrict}</p>
                            </div>
                        `;

                        const marker = L.marker([lat, lng], { icon })
                            .bindPopup(popupContent);
                        
                        markerLayerGroup.addLayer(marker);
                        visibleCount++;
                        bounds.push([lat, lng]);
                    }
                });

                // Update visible count
                document.getElementById('visibleCount').textContent = visibleCount;

                // Fit bounds if there are visible markers
                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                } else {
                    // If no markers, show whole Sri Lanka
                    map.setView([7.8731, 80.7718], 7);
                    // Show a message
                    L.popup()
                        .setLatLng([7.8731, 80.7718])
                        .setContent('<div class="text-center p-2"><i class="fas fa-info-circle text-evgreen text-xl"></i><p class="mt-1">No chargers found in this district.</p></div>')
                        .openOn(map);
                }

                // Show/hide clear button
                const clearBtn = document.getElementById('clearFilter');
                if (selectedDistrict !== 'all') {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            // Fetch real data from API
            fetch('https://evcircle.lk/api/chargersg', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    console.log('API Response:', data);
                    const chargers = data.chargers || data;

                    if (chargers && Array.isArray(chargers) && chargers.length > 0) {
                        allChargerData = chargers;
                        
                        // Update station count in UI
                        document.getElementById('stationCount').innerText = chargers.length;
                        if (document.getElementById('mobileStationCount')) {
                            document.getElementById('mobileStationCount').innerText = chargers.length;
                        }

                        // Show all markers initially
                        updateMapFilter('all');

                    } else {
                        console.warn('No chargers found in API response');
                        map.setView([7.8731, 80.7718], 7);
                        L.popup()
                            .setLatLng([7.8731, 80.7718])
                            .setContent('<div class="text-center p-2"><i class="fas fa-info-circle text-evgreen text-xl"></i><p class="mt-1">No chargers available yet.<br>Check back soon!</p></div>')
                            .openOn(map);
                    }
                })
                .catch(err => {
                    console.error('API fetch failed:', err);
                    map.setView([7.8731, 80.7718], 7);
                    L.popup()
                        .setLatLng([7.8731, 80.7718])
                        .setContent('<div class="text-center p-2"><i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i><p class="mt-1">Unable to load charger data.<br>Please try again later.</p></div>')
                        .openOn(map);
                });

            // District filter change handler
            document.getElementById('districtFilter').addEventListener('change', function() {
                const selectedDistrict = this.value;
                updateMapFilter(selectedDistrict);
            });

            // Clear filter button handler
            document.getElementById('clearFilter').addEventListener('click', function() {
                document.getElementById('districtFilter').value = 'all';
                updateMapFilter('all');
            });

            // Click to show coordinates
            map.on('click', e => {
                L.popup()
                    .setLatLng(e.latlng)
                    .setContent(`📍 <b>Coordinates:</b><br>Lat: ${e.latlng.lat.toFixed(5)}°N<br>Lng: ${e.latlng.lng.toFixed(5)}°E`)
                    .openOn(map);
                setTimeout(() => map.closePopup(), 2500);
            });

            L.control.scale({ metric: true, position: 'bottomleft' }).addTo(map);
        });
    </script>

    <!-- Smooth Scroll & Navbar -->
    <script>
        (function () {
            const navbar = document.getElementById('navbar');
            function handleNavScroll() { window.scrollY > 15 ? (navbar.classList.add('nav-blur'), navbar.classList.remove('nav-transparent')) : (navbar.classList.add('nav-transparent'), navbar.classList.remove('nav-blur')); }
            handleNavScroll(); window.addEventListener('scroll', handleNavScroll);
            document.querySelectorAll('.smooth-scroll').forEach(anchor => { anchor.addEventListener('click', function (e) { e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }); });
            const mobileBtn = document.getElementById('mobile-menu-button'), mobileMenu = document.getElementById('mobile-menu');
            if (mobileBtn && mobileMenu) mobileBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        })();
    </script>
</body>

</html>