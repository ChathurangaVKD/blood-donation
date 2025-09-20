// config.js - Frontend configuration for Docker environment
const CONFIG = {
    // API base URL - automatically detects if running in Docker or locally
    API_BASE_URL: window.location.origin + '/backend',

    // API endpoints
    ENDPOINTS: {
        REGISTER: '/register.php',
        LOGIN: '/login.php',
        REQUEST: '/request.php',
        SEARCH: '/search.php',
        INVENTORY: '/inventory.php',
        DONATIONS: '/donations.php',
        ADMIN: '/admin.php'
    },

    // Blood groups
    BLOOD_GROUPS: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],

    // Urgency levels
    URGENCY_LEVELS: ['Low', 'Medium', 'High', 'Critical'],

    // Application settings
    APP_NAME: 'Blood Donation System',
    VERSION: '1.0.0',

    // Validation rules
    VALIDATION: {
        MIN_AGE: 18,
        MAX_AGE: 65,
        MIN_PASSWORD_LENGTH: 8,
        DAYS_BETWEEN_DONATIONS: 90
    }
};

// Helper function to get full API URL
function getApiUrl(endpoint) {
    return CONFIG.API_BASE_URL + CONFIG.ENDPOINTS[endpoint];
}

// Helper function to make API calls
async function apiCall(endpoint, data = null, method = 'GET') {
    const url = getApiUrl(endpoint);
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    };

    if (data && method !== 'GET') {
        if (data instanceof FormData) {
            delete options.headers['Content-Type']; // Let browser set content-type for FormData
            options.body = data;
        } else {
            options.body = new URLSearchParams(data);
        }
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API call failed:', error);
        return { success: false, message: 'Network error occurred' };
    }
}
