// clear_demo_data.js - Script to completely clear all demo data
(function() {
    console.log('🧹 Clearing all demo data and cached user data...');

    // Clear all possible localStorage keys that might contain demo data
    const keysToRemove = [
        'user',
        'bloodlink_user_data',
        'bloodlink_user',
        'demo_user',
        'session_data',
        'current_user'
    ];

    keysToRemove.forEach(key => {
        const data = localStorage.getItem(key);
        if (data) {
            try {
                const parsed = JSON.parse(data);
                if (parsed.email === 'demo@bloodlink.com' ||
                    parsed.name === 'Demo User' ||
                    key.includes('demo')) {
                    console.log(`🗑️ Removing demo data from key: ${key}`);
                    localStorage.removeItem(key);
                }
            } catch (e) {
                // If not JSON, remove if key suggests demo data
                if (key.includes('demo')) {
                    localStorage.removeItem(key);
                }
            }
        }
    });

    // Clear all localStorage entirely to be safe
    console.log('🧼 Clearing entire localStorage to ensure no demo data remains');
    localStorage.clear();

    console.log('✅ Demo data cleanup complete!');
})();

