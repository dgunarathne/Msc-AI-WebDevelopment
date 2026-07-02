<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EVCircle • Seller Portal | Manage Chargers & Subscription</title>
    <!-- TailwindCSS + Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                        'spin-slow': 'spin 8s linear infinite',
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #000000;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .nav-blur {
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(12px);
            /* border-bottom: 1px solid rgba(0, 230, 118, 0.2); */
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0a; }
        ::-webkit-scrollbar-thumb { background: #00E676; border-radius: 8px; }
        
        .input-focus:focus {
            border-color: #00E676;
            box-shadow: 0 0 0 3px rgba(0, 230, 118, 0.2);
            outline: none;
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
        .progress-bar {
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #00E676, #9bffb0);
            border-radius: 8px;
            transition: width 0.3s ease;
        }
        .leaflet-container {
            border-radius: 1rem;
            background: #1a1a1a;
        }
    </style>
</head>
<body class="bg-black text-white overflow-x-hidden">

    <div id="appRoot">
        <div class="min-h-screen flex items-center justify-center">
            <div class="flex flex-col items-center gap-3">
                <div class="loader"></div>
                <p class="text-evgreen text-sm animate-pulse">Loading secure dashboard...</p>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // EVCircle Seller Portal - COMPLETE FUNCTIONALITY
        // API: https://evcircle.lk/api
        // ============================================
        
        const API_URL = "https://evcircle.lk/api";
        const API_KEY = "REPLACE_WITH_API_KEY";
        const STORAGE = { TOKEN: "access_token", USER: "evcircle_user_data" };
        
        // Global state
        let currentUser = null;
        let accessToken = null;
        let userChargers = [];
        let plansData = [];
        let currentSubscription = null;
        let referralData = null;
        let referralGoals = [];
        let selectedPlanIndex = 0;
        let planQuantities = [];
        let currentMap = null;
        let currentMarker = null;
        let selectedLocation = { lat: 6.9271, lng: 79.8612 };
        
        // Helper Functions
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
        
        function formatDateTime(isoString) {
            if (!isoString) return 'N/A';
            return new Date(isoString).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
        }
        
        function getAuthHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${accessToken}`,
                'X-API-KEY': API_KEY
            };
        }
        
        // API Calls
        async function fetchUserChargers() {
            try {
                const res = await fetch(`${API_URL}/getUserChargers`, { headers: getAuthHeaders() });
                if (res.ok) {
                    const data = await res.json();
                    userChargers = data.data || data || [];
                } else userChargers = [];
            } catch(e) { userChargers = []; }
            return userChargers;
        }
        
        async function fetchPlans() {
            try {
                const res = await fetch(`${API_URL}/subscription-plans`, { headers: getAuthHeaders() });
                if (!res.ok) throw new Error();
                let result = await res.json();
                const now = new Date();
                let active = result.filter(p => !p.end_date || new Date(p.end_date) > now);
                active.sort((a,b) => (parseFloat(a.price)||0) - (parseFloat(b.price)||0));
                plansData = active;
                planQuantities = active.map(() => 1);
            } catch(e) {
                plansData = [
                    { id: '1', name: 'Free Plan', duration: '1 Day', days: 1, price: '0.00', popular: false },
                    { id: '2', name: 'Day Plan', duration: '1 Day', days: 1, price: '300.00', popular: false },
                    { id: '3', name: 'Weekly Plan', duration: '7 Days', days: 7, price: '1750.00', popular: true },
                    { id: '4', name: 'Monthly Plan', duration: '30 Days', days: 30, price: '5000.00', popular: false }
                ];
                planQuantities = plansData.map(() => 1);
            }
            return plansData;
        }
        
        async function fetchCurrentSubscription() {
            try {
                const res = await fetch(`${API_URL}/subscription-plans`, { headers: getAuthHeaders() });
                if (res.ok) {
                    const data = await res.json();
                    currentSubscription = data.data || data;
                }
            } catch(e) { currentSubscription = null; }
            return currentSubscription;
        }
        
        async function fetchReferralData() {
            try {
                const res = await fetch(`${API_URL}/referrals`, { headers: getAuthHeaders() });
                if (res.ok) {
                    const data = await res.json();
                    referralData = data.data || data;
                    referralGoals = [
                        { type: 'Day', required: 3, rewardDays: 1, current: referralData.day_referrals || 0, completed: (referralData.day_referrals || 0) >= 3 },
                        { type: 'Week', required: 5, rewardDays: 7, current: referralData.week_referrals || 0, completed: (referralData.week_referrals || 0) >= 5 },
                        { type: 'Month', required: 10, rewardDays: 30, current: referralData.month_referrals || 0, completed: (referralData.month_referrals || 0) >= 10 }
                    ];
                    return referralData;
                }
            } catch(e) {}
            referralData = { code: 'EVCIRCLE2025', total_earned: 0, balance: 0, day_referrals: 0, week_referrals: 0, month_referrals: 0 };
            referralGoals = [
                { type: 'Day', required: 3, rewardDays: 1, current: 0, completed: false },
                { type: 'Week', required: 5, rewardDays: 7, current: 0, completed: false },
                { type: 'Month', required: 10, rewardDays: 30, current: 0, completed: false }
            ];
            return referralData;
        }
        
        // REGISTER CHARGER - now checks if user already has a charger
        async function registerCharger(chargerData) {
            // Check if user already has at least one charger
            if (userChargers.length > 0) {
                showToast("⚠️ You already have a registered charger. Only one charger is allowed per user.", true);
                return { success: false, error: 'limit_reached' };
            }

            try {
                const res = await fetch(`${API_URL}/register-charger`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify(chargerData)
                });
                const data = await res.json();
                if (res.ok) {
                    showToast("✅ Charger registered successfully!");
                    await fetchUserChargers();
                    // Update the badge count in the nav tab
                    const chargerTab = document.querySelector('.nav-tab[data-tab="chargers"]');
                    if (chargerTab) {
                        chargerTab.innerHTML = `<i class="fas fa-list-ul mr-2"></i>My Chargers (${userChargers.length})`;
                    }
                    return { success: true };
                } else {
                    showToast(data.message || 'Registration failed', true);
                    return { success: false };
                }
            } catch(e) {
                showToast('Network error', true);
                return { success: false };
            }
        }
        
        async function activateSubscription(planId, quantity, totalDays, totalPrice) {
            try {
                const endDate = new Date(Date.now() + totalDays * 86400000).toISOString().split('T')[0];
                const res = await fetch(`${API_URL}/subscribed`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({ date_to: endDate, duration: totalDays, price: totalPrice, start_date: new Date().toISOString().split('T')[0] })
                });
                const data = await res.json();
                if (res.ok) {
                    if (totalPrice === 0) {
                        showToast("✅ Free subscription activated!");
                        await fetchCurrentSubscription();
                        return { success: true };
                    } else {
                        showToast(`💰 Proceed to payment: LKR ${totalPrice.toFixed(2)}`);
                        return { success: true, redirect: true };
                    }
                } else {
                    showToast(data.message || 'Activation failed', true);
                    return { success: false };
                }
            } catch(e) {
                showToast('Network error', true);
                return { success: false };
            }
        }
        
        async function activateReferralPackage(type, days) {
            try {
                const res = await fetch(`${API_URL}/activate-referral`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({ date_to: new Date(Date.now() + days * 86400000).toISOString().split('T')[0], amount: days, type: type })
                });
                if (!res.ok) throw new Error();
                showToast(`✨ ${type} referral package activated (${days} days free!)`);
                await fetchReferralData();
                renderSubscriptionPlans();
            } catch(e) {
                showToast('Activation failed', true);
            }
        }
        
        // Map Functions
        function initMap(lat, lng, onSelect) {
            if (currentMap) currentMap.remove();
            currentMap = L.map('locationMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(currentMap);
            
            const greenIcon = L.divIcon({
                html: '<div style="background:#00E676; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid white; box-shadow:0 0 10px rgba(0,230,118,0.5);"><i class="fas fa-charging-station" style="color:black; font-size:16px;"></i></div>',
                iconSize: [36, 36],
                popupAnchor: [0, -18]
            });
            
            currentMarker = L.marker([lat, lng], { icon: greenIcon, draggable: true }).addTo(currentMap);
            currentMarker.on('dragend', e => {
                const pos = e.target.getLatLng();
                selectedLocation = { lat: pos.lat, lng: pos.lng };
                if (onSelect) onSelect(pos.lat, pos.lng);
                document.getElementById('latLngDisplay').innerHTML = `<i class="fas fa-map-marker-alt text-evgreen mr-1"></i>Lat: ${pos.lat.toFixed(6)}, Lng: ${pos.lng.toFixed(6)}`;
            });
            currentMap.on('click', e => {
                selectedLocation = { lat: e.latlng.lat, lng: e.latlng.lng };
                currentMarker.setLatLng(e.latlng);
                if (onSelect) onSelect(e.latlng.lat, e.latlng.lng);
                document.getElementById('latLngDisplay').innerHTML = `<i class="fas fa-map-marker-alt text-evgreen mr-1"></i>Lat: ${e.latlng.lat.toFixed(6)}, Lng: ${e.latlng.lng.toFixed(6)}`;
            });
        }
        
        // Render Functions
        function updateStats() {
            const statsContainer = document.getElementById('statsContainer');
            if (statsContainer) {
                const totalEarnings = userChargers.reduce((s, c) => s + (parseFloat(c.price_per_kwh) || 0), 0);
                statsContainer.innerHTML = `
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-evgreen/40 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-evgreen/20 flex items-center justify-center"><i class="fas fa-charging-station text-evgreen text-xl"></i></div>
                            <div><p class="text-gray-400 text-xs uppercase tracking-wider">Total Chargers</p><p class="text-3xl font-bold text-white">${userChargers.length}</p></div>
                        </div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-evgreen/40 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-evgreen/20 flex items-center justify-center"><i class="fas fa-coins text-evgreen text-xl"></i></div>
                            <div><p class="text-gray-400 text-xs uppercase tracking-wider">Avg. Price/kWh</p><p class="text-3xl font-bold text-white">LKR ${totalEarnings.toLocaleString()}</p></div>
                        </div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-evgreen/40 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-evgreen/20 flex items-center justify-center"><i class="fas fa-badge-check text-evgreen text-xl"></i></div>
                            <div><p class="text-gray-400 text-xs uppercase tracking-wider">Seller Status</p><p class="text-3xl font-bold text-white">Verified</p></div>
                        </div>
                    </div>
                `;
            }
        }
        
        function renderRegisterCharger() {
            const container = document.getElementById('dynamicContent');
            
            // Check if user already has a charger and show message accordingly
            const hasCharger = userChargers.length > 0;
            
            container.innerHTML = `
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-plug text-evgreen text-2xl"></i>
                        <h2 class="text-2xl font-bold text-white">Register Charging Station</h2>
                    </div>
                    ${hasCharger ? `
                        <div class="bg-orange-500/20 border border-orange-500/40 rounded-xl p-4 mb-6 flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle text-orange-400 text-xl"></i>
                            <div>
                                <p class="text-white font-semibold">You already have a registered charger.</p>
                                <p class="text-gray-300 text-sm">Only one charger is allowed per seller account. If you need to update your charger details, please contact support.</p>
                            </div>
                        </div>
                    ` : `
                        <p class="text-gray-400 mb-6 border-l-3 border-evgreen pl-3">Pin location on map & fill details — all fields are required</p>
                    `}
                    <form id="registerForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-300 text-sm font-medium mb-2"> Station Name *</label>
                                <input type="text" id="stationName" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen focus:ring-2 focus:ring-evgreen/20 transition-all input-focus" placeholder="e.g., GreenCharge Hub" ${hasCharger ? 'disabled' : ''} required>
                                
                                <label class="block text-gray-300 text-sm font-medium mt-4 mb-2"> Charger Type *</label>
                                <select id="chargerType" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-evgreen transition-all input-focus" ${hasCharger ? 'disabled' : ''} required>
                                    <option value="">Select Type</option>
                                    <option value="slow">Slow Charger (3-7 kW)</option>
                                    <option value="fast">Fast Charger (22-50 kW)</option>
                                    <option value="ultra_fast">Ultra-Fast (150-350 kW)</option>
                                </select>
                                
                                <label class="block text-gray-300 text-sm font-medium mt-4 mb-2"> Power Type *</label>
                                <select id="powerType" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-evgreen transition-all input-focus" ${hasCharger ? 'disabled' : ''} required>
                                    <option value="">Select Power Type</option>
                                    <option value="ac">AC Charger</option>
                                    <option value="dc">DC Charger</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-300 text-sm font-medium mb-2"> Connector Type *</label>
                                <select id="connectorType" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-evgreen transition-all input-focus" ${hasCharger ? 'disabled' : ''} required>
                                    <option value="">Select Connector</option>
                                    <option value="type2">Type 2</option>
                                    <option value="ccs2">CCS2</option>
                                    <option value="chademo">CHAdeMO</option>
                                </select>
                                
                                <label class="block text-gray-300 text-sm font-medium mt-4 mb-2"> Power Output (kW) *</label>
                                <input type="number" id="powerOutput" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen transition-all input-focus" placeholder="e.g., 50" step="0.1" ${hasCharger ? 'disabled' : ''} required>
                                
                                <label class="block text-gray-300 text-sm font-medium mt-4 mb-2"> Price Per kWh (LKR) *</label>
                                <input type="number" id="pricePerKwh" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen transition-all input-focus" placeholder="e.g., 65.00" step="0.01" ${hasCharger ? 'disabled' : ''} required>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-300 text-sm font-medium mb-2"> Set Location *</label>
                            <div class="map-container rounded-xl overflow-hidden border border-gray-600" style="pointer-events: ${hasCharger ? 'none' : 'auto'}; opacity: ${hasCharger ? '0.6' : '1'};">
                                <div id="locationMap" style="height: 320px; width: 100%;"></div>
                            </div>
                            <div id="latLngDisplay" class="mt-3 text-sm text-gray-400 bg-black/30 rounded-xl px-4 py-2 inline-flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-evgreen"></i> ${hasCharger ? 'Location locked (already registered)' : 'Click map or drag marker to set location'}
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-300 text-sm font-medium mb-2">🎫 Promo Code (Optional)</label>
                            <input type="text" id="promoCode" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-evgreen transition-all input-focus" placeholder="Enter promo code if you have one" ${hasCharger ? 'disabled' : ''}>
                        </div>
                        
                        <button type="submit" class="w-full bg-evgreen hover:bg-green-400 text-black font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg shadow-evgreen/30 ${hasCharger ? 'opacity-50 cursor-not-allowed' : ''}" ${hasCharger ? 'disabled' : ''}>
                            <i class="fas fa-plus-circle"></i> ${hasCharger ? 'Already Registered' : 'Register Charger'}
                        </button>
                    </form>
                </div>
            `;
            
            // Initialize map only if no charger (or always but with disabled interaction handled above)
            if (!hasCharger) {
                initMap(selectedLocation.lat, selectedLocation.lng, (lat, lng) => {
                    document.getElementById('latLngDisplay').innerHTML = `<i class="fas fa-map-marker-alt text-evgreen mr-1"></i>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                });
            } else {
                // Still show map with existing location if available, or default
                const existingLat = userChargers[0]?.latitude || 6.9271;
                const existingLng = userChargers[0]?.longitude || 79.8612;
                initMap(existingLat, existingLng, null);
                document.getElementById('latLngDisplay').innerHTML = `<i class="fas fa-map-marker-alt text-evgreen mr-1"></i>Charger location (locked)`;
            }
            
            document.getElementById('registerForm')?.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Double-check if user already has a charger (prevent any bypass)
                if (userChargers.length > 0) {
                    showToast("⚠️ You already have a registered charger. Only one charger is allowed per user.", true);
                    return;
                }
                
                const btn = e.target.querySelector('button[type="submit"]');
                const origHtml = btn.innerHTML;
                btn.innerHTML = '<span class="loader"></span> Registering...';
                btn.disabled = true;
                
                const stationName = document.getElementById('stationName')?.value.trim();
                const chargerType = document.getElementById('chargerType')?.value;
                const powerType = document.getElementById('powerType')?.value;
                const connectorType = document.getElementById('connectorType')?.value;
                const powerOutput = document.getElementById('powerOutput')?.value;
                const pricePerKwh = document.getElementById('pricePerKwh')?.value;
                const promoCode = document.getElementById('promoCode')?.value;
                
                if (!stationName || !chargerType || !powerType || !connectorType || !powerOutput || !pricePerKwh) {
                    showToast("Please fill all required fields", true);
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                    return;
                }
                
                const result = await registerCharger({
                    station_name: stationName,
                    latitude: selectedLocation.lat,
                    longitude: selectedLocation.lng,
                    location: `${selectedLocation.lat.toFixed(6)}, ${selectedLocation.lng.toFixed(6)}`,
                    charger_type: chargerType,
                    power_type: powerType,
                    connector_type: connectorType,
                    power_output: parseFloat(powerOutput),
                    price_per_kwh: parseFloat(pricePerKwh),
                    promo_code: promoCode || null
                });
                
                if (result.success) {
                    document.getElementById('registerForm')?.reset();
                    selectedLocation = { lat: 6.9271, lng: 79.8612 };
                    if (currentMap) {
                        currentMap.setView([selectedLocation.lat, selectedLocation.lng], 15);
                        currentMarker.setLatLng([selectedLocation.lat, selectedLocation.lng]);
                    }
                    updateStats();
                    // Update charger count in nav
                    const chargerTab = document.querySelector('.nav-tab[data-tab="chargers"]');
                    if (chargerTab) {
                        chargerTab.innerHTML = `<i class="fas fa-list-ul mr-2"></i>My Chargers (${userChargers.length})`;
                    }
                    // Re-render register view to reflect the new state (disable form)
                    renderRegisterCharger();
                }
                btn.innerHTML = origHtml;
                btn.disabled = false;
            });
        }
        
        function renderMyChargers() {
            const container = document.getElementById('dynamicContent');
            container.innerHTML = `
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-list-ul text-evgreen text-2xl"></i>
                        <h2 class="text-2xl font-bold text-white">My Chargers</h2>
                    </div>
                    <p class="text-gray-400 mb-6 border-l-3 border-evgreen pl-3">Manage your registered charging stations</p>
                    <div id="chargersListContainer" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        ${userChargers.length === 0 ? 
                            '<div class="col-span-2 text-center py-12 text-gray-400"><i class="fas fa-charging-station text-5xl mb-3 opacity-30"></i><p>No chargers added yet. Go to "Register Charger" tab to get started.</p></div>' : 
                            userChargers.map(c => `
                                <div class="bg-black/40 rounded-xl p-5 border border-white/10 hover:border-evgreen/40 transition-all">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-lg bg-evgreen/20 flex items-center justify-center"><i class="fas fa-charging-station text-evgreen"></i></div>
                                        <h3 class="font-bold text-white text-lg">${escapeHtml(c.station_name)}</h3>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-400">📍 Location</span><span class="text-white">${escapeHtml(c.location || `${c.latitude}, ${c.longitude}`)}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">🔌 Type</span><span class="text-white">${escapeHtml(c.charger_type)} / ${escapeHtml(c.power_type)}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">⚡ Power</span><span class="text-white">${c.power_output} kW</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">💰 Price/kWh</span><span class="text-white font-semibold text-evgreen">LKR ${parseFloat(c.price_per_kwh).toLocaleString()}</span></div>
                                    </div>
                                </div>
                            `).join('')
                        }
                    </div>
                </div>
            `;
        }
        
        function renderSubscriptionPlans() {
            const container = document.getElementById('dynamicContent');
            const now = new Date();
            const subEnd = currentSubscription?.end_date ? new Date(currentSubscription.end_date) : null;
            const hoursLeft = subEnd ? (subEnd - now) / 3600000 : 0;
            const isExpiring = hoursLeft > 0 && hoursLeft <= 48;
            const isExpired = hoursLeft <= 0;
            
            container.innerHTML = `
                ${currentSubscription ? `
                <div class="bg-gradient-to-r from-evgreen/10 to-transparent backdrop-blur-sm rounded-2xl p-5 border border-evgreen/30 mb-6">
                    <div class="flex flex-wrap justify-between items-center">
                        <div><i class="fas fa-ticket-alt text-evgreen text-xl mr-2"></i><span class="font-semibold text-white">Current Subscription</span></div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${isExpired ? 'bg-red-500/20 text-red-400' : (isExpiring ? 'bg-orange-500/20 text-orange-400' : 'bg-evgreen/20 text-evgreen')}">${isExpired ? 'Expired' : (isExpiring ? 'Expiring Soon' : 'Active')}</span>
                    </div>
                    <p class="text-white font-bold mt-2">${escapeHtml(currentSubscription.plan_name || 'Active Plan')}</p>
                    <p class="text-gray-400 text-sm">Start: ${formatDateTime(currentSubscription.start_date)} | End: ${formatDateTime(currentSubscription.end_date)}</p>
                    ${isExpiring && !isExpired ? `<p class="text-orange-400 text-sm mt-2"><i class="fas fa-clock"></i> Expires in ${Math.ceil(hoursLeft)} hours</p>` : ''}
                </div>
                ` : ''}
                
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10 mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-gift text-evgreen text-2xl"></i>
                        <h2 class="text-2xl font-bold text-white">Referral Rewards</h2>
                    </div>
                    <div class="bg-black/40 rounded-xl p-4 text-center mb-4">
                        <p class="text-gray-400 text-sm">Your Referral Code</p>
                        <p class="text-2xl font-mono font-bold text-evgreen">${escapeHtml(referralData?.code || 'EVCIRCLE2025')}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center"><p class="text-gray-400 text-xs">Total Earned</p><p class="text-xl font-bold text-white">LKR ${(referralData?.total_earned || 0).toLocaleString()}</p></div>
                        <div class="text-center"><p class="text-gray-400 text-xs">Balance</p><p class="text-xl font-bold text-white">LKR ${(referralData?.balance || 0).toLocaleString()}</p></div>
                    </div>
                    ${referralGoals.map(g => `
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1"><span>${g.type} (${g.required} referrals)</span><span>${g.current}/${g.required}</span></div>
                            <div class="progress-bar h-2"><div class="progress-fill h-full" style="width: ${Math.min(100, (g.current/g.required)*100)}%"></div></div>
                            ${g.completed ? `<button class="mt-2 bg-evgreen/20 hover:bg-evgreen text-evgreen hover:text-black px-4 py-1.5 rounded-lg text-sm transition-all" onclick="window.activateReferralGoal('${g.type}', ${g.rewardDays})"><i class="fas fa-gift"></i> Activate ${g.rewardDays} Days Free</button>` : `<p class="text-xs text-gray-500 mt-1">Need ${g.required - g.current} more → ${g.rewardDays} days free</p>`}
                        </div>
                    `).join('')}
                </div>
                
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-white/10">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-rocket text-evgreen text-2xl"></i>
                        <h2 class="text-2xl font-bold text-white">Subscription Plans</h2>
                    </div>
                    <p class="text-gray-400 mb-6 border-l-3 border-evgreen pl-3">Choose & scale your EV business</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="plansGrid">
                        ${plansData.map((p, idx) => `
                            <div class="bg-black/40 rounded-xl p-4 border-2 cursor-pointer transition-all ${selectedPlanIndex === idx ? 'border-evgreen shadow-lg shadow-evgreen/20' : 'border-white/10 hover:border-evgreen/50'}" onclick="window.selectPlan(${idx})">
                                ${p.popular ? '<div class="text-right"><span class="text-xs bg-gradient-to-r from-orange-500 to-red-500 px-2 py-0.5 rounded-full text-white">🔥 POPULAR</span></div>' : ''}
                                <div class="text-center">
                                    <h3 class="font-bold text-white text-lg mt-2">${escapeHtml(p.name)}</h3>
                                    <p class="text-gray-400 text-xs">${escapeHtml(p.duration)}</p>
                                    <p class="text-2xl font-bold text-evgreen mt-3">${parseFloat(p.price) === 0 ? 'FREE' : `LKR ${parseFloat(p.price).toLocaleString()}`}</p>
                                    ${parseFloat(p.price) > 0 ? `
                                        <div class="flex items-center justify-center gap-3 mt-3">
                                            <button class="w-8 h-8 rounded-full bg-white/10 hover:bg-evgreen/30 text-white" onclick="event.stopPropagation(); window.updateQty(${idx}, -1)">-</button>
                                            <span class="text-white font-bold">${planQuantities[idx]}</span>
                                            <button class="w-8 h-8 rounded-full bg-white/10 hover:bg-evgreen/30 text-white" onclick="event.stopPropagation(); window.updateQty(${idx}, 1)">+</button>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-2">Total: <span class="text-evgreen font-bold">LKR ${(parseFloat(p.price) * planQuantities[idx]).toLocaleString()}</span><br>for ${p.days * planQuantities[idx]} days</p>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <button id="activateSubscriptionBtn" class="w-full bg-evgreen hover:bg-green-400 text-black font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg shadow-evgreen/30">
                        <i class="fas fa-bolt"></i> Activate Subscription
                    </button>
                </div>
            `;
            
            document.getElementById('activateSubscriptionBtn')?.addEventListener('click', async () => {
                const plan = plansData[selectedPlanIndex];
                if (plan) {
                    const qty = planQuantities[selectedPlanIndex];
                    const totalPrice = parseFloat(plan.price) * qty;
                    const totalDays = plan.days * qty;
                    const btn = document.getElementById('activateSubscriptionBtn');
                    const origHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="loader"></span> Activating...';
                    btn.disabled = true;
                    await activateSubscription(plan.id, qty, totalDays, totalPrice);
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
            });
        }
        
        function clearAuthAndLogout() {
            localStorage.clear();
            showToast("Logged out successfully");
            setTimeout(() => window.location.href = "/login", 600);
        }
        
        async function renderDashboard() {
            const firstName = currentUser?.first_name || 'Seller';
            const email = currentUser?.email || 'seller@evcircle.com';
            const initials = firstName.charAt(0).toUpperCase();
            
            document.getElementById("appRoot").innerHTML = `
                <div class="min-h-screen">
                    <nav class="fixed w-full z-50 top-0 bg-black/75 backdrop-blur-xl  border-evgreen/20">
                        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4 md:px-6">
                            <a href="#" class="flex items-center space-x-3 group">
                                <div class="relative">
                                   <img src="https://evcircle.lk/logo.png" alt="EVCircle Logo" class="w-8 h-8 rounded-full group-hover:animate-spin-slow transition-all">
                                </div>
                                <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white to-evgreen bg-clip-text text-transparent">EVCircle Seller</span>
                            </a>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-3 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-evgreen to-green-700 flex items-center justify-center text-black font-bold">${escapeHtml(initials)}</div>
                                    <div class="hidden md:block"><p class="text-white text-sm font-medium">${escapeHtml(firstName)}</p><p class="text-gray-400 text-xs">${escapeHtml(email)}</p></div>
                                </div>
                                <button id="logoutButton" class="text-evgreen border border-evgreen/50 hover:bg-evgreen hover:text-black transition-all duration-300 font-medium rounded-full px-5 py-2 text-sm"><i class="fas fa-sign-out-alt mr-1"></i> Logout</button>
                            </div>
                        </div>
                    </nav>
                    
                    <section class="relative pt-24 pb-12 px-6">
                        <div class="absolute w-[600px] h-[600px] bg-evgreen/15 blur-[140px] top-[-180px] left-[-200px] rounded-full animate-pulse-slow"></div>
                        <div class="absolute w-[500px] h-[500px] bg-evgreen/10 blur-[130px] bottom-[-100px] right-[-150px] rounded-full"></div>
                        
                        <div class="relative z-10 max-w-screen-xl mx-auto">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8" id="statsContainer"></div>
                            
                            <div class="flex flex-wrap gap-3 mb-8  border-white/10 pb-4">
                                <button class="nav-tab px-6 py-2.5 rounded-full font-medium transition-all bg-evgreen text-black shadow-lg shadow-evgreen/30" data-tab="register"><i class="fas fa-plus-circle mr-2"></i>Register Charger</button>
                                <button class="nav-tab px-6 py-2.5 rounded-full font-medium transition-all hover:bg-white/10 text-gray-300" data-tab="chargers"><i class="fas fa-list-ul mr-2"></i>My Chargers (${userChargers.length})</button>
                                <button class="nav-tab px-6 py-2.5 rounded-full font-medium transition-all hover:bg-white/10 text-gray-300" data-tab="subscription"><i class="fas fa-crown mr-2"></i>Subscription & Rewards</button>
                            </div>
                            
                            <div id="dynamicContent"></div>
                        </div>
                    </section>
                    
                    <footer class="border-t border-gray-800/40 py-8 text-center text-gray-500 text-sm relative z-10 bg-black/30 backdrop-blur-sm">
                        <p>© 2025 EVCircle – Electrifying the future. All rights reserved.</p>
                    </footer>
                </div>
            `;
            
            document.getElementById("logoutButton")?.addEventListener("click", clearAuthAndLogout);
            updateStats();
            
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.nav-tab').forEach(t => {
                        t.classList.remove('bg-evgreen', 'text-black', 'shadow-lg', 'shadow-evgreen/30');
                        t.classList.add('hover:bg-white/10', 'text-gray-300');
                    });
                    tab.classList.add('bg-evgreen', 'text-black', 'shadow-lg', 'shadow-evgreen/30');
                    tab.classList.remove('hover:bg-white/10', 'text-gray-300');
                    
                    if (tab.dataset.tab === 'register') renderRegisterCharger();
                    else if (tab.dataset.tab === 'chargers') renderMyChargers();
                    else if (tab.dataset.tab === 'subscription') renderSubscriptionPlans();
                });
            });
            
            renderRegisterCharger();
        }
        
        window.selectPlan = (idx) => { selectedPlanIndex = idx; renderSubscriptionPlans(); };
        window.updateQty = (idx, delta) => { if (parseFloat(plansData[idx].price) !== 0 && planQuantities[idx] + delta >= 1) planQuantities[idx] += delta; renderSubscriptionPlans(); };
        window.activateReferralGoal = (type, days) => activateReferralPackage(type, days);
        
        async function initDashboard() {
            accessToken = localStorage.getItem(STORAGE.TOKEN);
            const userStr = localStorage.getItem(STORAGE.USER);
            if (!accessToken || !userStr) {
                window.location.href = "/login";
                return;
            }
            currentUser = JSON.parse(userStr);
            await Promise.all([fetchPlans(), fetchCurrentSubscription(), fetchReferralData(), fetchUserChargers()]);
            await renderDashboard();
        }
        
        initDashboard();
    </script>
</body>
</html>