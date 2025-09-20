// script.js - Updated for Docker environment
document.addEventListener('DOMContentLoaded', function() {
    // Handle registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(registerForm);

            const result = await apiCall('REGISTER', formData, 'POST');
            const messageEl = document.getElementById('registerMessage');

            if (result.success) {
                messageEl.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                registerForm.reset();
            } else {
                messageEl.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
            }
        });
    }

    // Handle login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(loginForm);

            const result = await apiCall('LOGIN', formData, 'POST');
            const messageEl = document.getElementById('loginMessage');

            if (result.success) {
                messageEl.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                // Store user session data
                if (result.user) {
                    localStorage.setItem('user', JSON.stringify(result.user));
                }
                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1000);
            } else {
                messageEl.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
            }
        });
    }

    // Handle request form
    const requestForm = document.getElementById('requestForm');
    if (requestForm) {
        requestForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(requestForm);

            const result = await apiCall('REQUEST', formData, 'POST');
            const messageEl = document.getElementById('requestMessage');

            if (result.success) {
                messageEl.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                requestForm.reset();
                loadRequests();
            } else {
                messageEl.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
            }
        });
    }

    // Load pending requests on request page
    if (document.getElementById('requestsList')) {
        loadRequests();
    }

    function loadRequests() {
        fetch('request.php') // GET for listing
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('requestsList');
                list.innerHTML = data.map(req => `<div>${req.blood_group} - ${req.status}</div>`).join('');
            });
    }

    // Search for blood/donors
    async function searchBlood() {
        const searchForm = document.getElementById('searchForm');
        if (!searchForm) return;

        const formData = new FormData(searchForm);
        const result = await apiCall('SEARCH', formData, 'POST');
        const resultsContainer = document.getElementById('searchResults');

        if (resultsContainer && result.success) {
            displaySearchResults(result.results, resultsContainer);
        }
    }

    // Display search results
    function displaySearchResults(results, container) {
        container.innerHTML = '';

        if (results.total_matches === 0) {
            container.innerHTML = '<p class="no-results">No matches found.</p>';
            return;
        }

        // Display donors
        if (results.donors.length > 0) {
            const donorsSection = document.createElement('div');
            donorsSection.innerHTML = `<h3>Available Donors (${results.donors.length})</h3>`;

            results.donors.forEach(donor => {
                const donorItem = document.createElement('div');
                donorItem.className = 'result-item donor-item';
                donorItem.innerHTML = `
                    <h4>${donor.name}</h4>
                    <p><strong>Blood Group:</strong> ${donor.blood_group}</p>
                    <p><strong>Location:</strong> ${donor.location}</p>
                    <p><strong>Contact:</strong> ${donor.contact}</p>
                    <p><strong>Status:</strong> ${donor.donation_status}</p>
                    <span class="badge ${donor.eligible_to_donate ? 'badge-success' : 'badge-warning'}">
                        ${donor.eligible_to_donate ? 'Eligible' : 'Not Eligible'}
                    </span>
                `;
                donorsSection.appendChild(donorItem);
            });

            container.appendChild(donorsSection);
        }

        // Display inventory
        if (results.inventory.length > 0) {
            const inventorySection = document.createElement('div');
            inventorySection.innerHTML = `<h3>Available Blood Units (${results.inventory.length})</h3>`;

            results.inventory.forEach(unit => {
                const unitItem = document.createElement('div');
                unitItem.className = 'result-item inventory-item';
                unitItem.innerHTML = `
                    <h4>${unit.blood_group} Blood Unit</h4>
                    <p><strong>Location:</strong> ${unit.location}</p>
                    <p><strong>Expiry Date:</strong> ${unit.formatted_expiry}</p>
                    <p><strong>Days to Expiry:</strong> ${unit.days_to_expiry} days</p>
                    <span class="badge badge-info">${unit.status}</span>
                `;
                inventorySection.appendChild(unitItem);
            });

            container.appendChild(inventorySection);
        }
    }

    // Add some creative animations
    const cards = document.querySelectorAll('.feature-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'scale(1.05)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'scale(1)';
        });
    });
});

// Load blood requests
async function loadRequests() {
    try {
        const result = await apiCall('REQUEST', null, 'GET');
        const requestsList = document.getElementById('requestsList');

        if (requestsList && result.success) {
            requestsList.innerHTML = '';
            result.requests.forEach(request => {
                const urgencyClass = getUrgencyClass(request.urgency);
                const requestItem = document.createElement('div');
                requestItem.className = `request-item ${urgencyClass}`;
                requestItem.innerHTML = `
                    <h4>${request.blood_group} Blood Needed</h4>
                    <p><strong>Hospital:</strong> ${request.hospital}</p>
                    <p><strong>Location:</strong> ${request.location}</p>
                    <p><strong>Urgency:</strong> ${request.urgency}</p>
                    <p><strong>Units Needed:</strong> ${request.units_needed}</p>
                    <p><strong>Required Date:</strong> ${request.formatted_date}</p>
                    <p><strong>Contact:</strong> ${request.requester_contact}</p>
                    ${request.notes ? `<p><strong>Notes:</strong> ${request.notes}</p>` : ''}
                `;
                requestsList.appendChild(requestItem);
            });
        }
    } catch (error) {
        console.error('Error loading requests:', error);
    }
}

// Helper functions
function getUrgencyClass(urgency) {
    const classes = {
        'Critical': 'urgency-critical',
        'High': 'urgency-high',
        'Medium': 'urgency-medium',
        'Low': 'urgency-low'
    };
    return classes[urgency] || 'urgency-low';
}

// Initialize page
function initializePage() {
    // Load requests on request page
    if (document.getElementById('requestsList')) {
        loadRequests();
    }

    // Setup search functionality
    const searchButton = document.getElementById('searchButton');
    if (searchButton) {
        searchButton.addEventListener('click', searchBlood);
    }

    // Load user info if logged in
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.name) {
        const userInfo = document.getElementById('userInfo');
        if (userInfo) {
            userInfo.innerHTML = `Welcome, ${user.name} (${user.blood_group})`;
        }
    }
}

// Call initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initializePage);
