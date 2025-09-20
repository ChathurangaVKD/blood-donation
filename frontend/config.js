// University Demo Configuration - FIXED VERSION
const CONFIG = {
    API_BASE_URL: 'http://localhost:8081',
    ENDPOINTS: {
        REGISTER: '/register.php',
        LOGIN: '/login.php',
        REQUEST: '/request.php',
        SEARCH: '/search.php',
        INVENTORY: '/inventory.php',
        DONATIONS: '/donations.php',
        ADMIN: '/admin.php'
    },
    BLOOD_GROUPS: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    URGENCY_LEVELS: ['Low', 'Medium', 'High', 'Critical'],
    APP_NAME: 'Blood Donation System (University Demo)',
    VERSION: '1.0.0-university-fixed'
};

// Simple API function for university project - COMPLETELY FIXED
async function apiCall(endpoint, data = null, method = 'GET') {
    let url = CONFIG.API_BASE_URL + CONFIG.ENDPOINTS[endpoint];
    const options = {
        method: method,
        mode: 'cors'
    };

    console.log('🚀 FIXED API CALL DEBUG - Starting apiCall function');
    console.log('  - endpoint:', endpoint);
    console.log('  - data received:', data);
    console.log('  - method:', method);
    console.log('  - initial URL:', url);

    // Handle query parameters for GET requests - FIXED VERSION
    if (data && method === 'GET') {
        console.log('🔧 Processing GET request with data...');
        const params = new URLSearchParams();

        console.log('📝 Building query parameters from data object:');
        for (const [key, value] of Object.entries(data)) {
            console.log(`  - Processing parameter: ${key} = "${value}" (type: ${typeof value})`);

            // Include ALL values, even empty strings - this was the bug!
            if (value !== null && value !== undefined) {
                params.append(key, value);
                console.log(`    ✅ Added to URLSearchParams: ${key}=${value}`);
            } else {
                console.log(`    ❌ Skipped (null/undefined): ${key}`);
            }
        }

        // Generate query string
        const queryString = params.toString();
        console.log('🔗 Generated query string:', queryString);

        if (queryString) {
            url += '?' + queryString;
            console.log('🎯 Final URL with query params:', url);
        } else {
            console.log('⚠️ WARNING: No query string generated - all params were null/undefined!');
        }

        console.log('🔍 Final GET request URL:', url);
        console.log('🎯 URLSearchParams object contents:', Object.fromEntries(params));
    } else if (data && method !== 'GET') {
        console.log('🔧 Processing non-GET request...');
        if (data instanceof FormData) {
            options.body = data;
            console.log('📝 Using FormData directly for POST request');
        } else {
            options.headers = {'Content-Type': 'application/x-www-form-urlencoded'};
            options.body = new URLSearchParams(data);
            console.log('📝 Converting data to URLSearchParams for POST request');
        }
    } else {
        console.log('🔧 No data provided or not a GET request');
    }

    try {
        console.log('📡 Making fetch request to:', url);
        console.log('📋 Request options:', {
            method: options.method,
            body: options.body instanceof FormData ? 'FormData object' : options.body,
            headers: options.headers || 'No custom headers'
        });

        const response = await fetch(url, options);

        console.log('📥 Response status:', response.status, response.statusText);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('📥 API response received:', result);
        return result;
    } catch (error) {
        console.error('❌ API Error:', error);
        return { success: false, message: 'Connection error: ' + error.message };
    }
}

// Helper function to get API URL
function getApiUrl(endpoint) {
    return CONFIG.API_BASE_URL + CONFIG.ENDPOINTS[endpoint];
}

console.log('✅ FIXED config_fixed.js loaded successfully');
console.log('🔧 Fixed apiCall function is now available');
