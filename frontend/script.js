// script.js - Updated with modern notification system
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation and notifications
    initializeFormValidation();

    // Check session status and update UI
    checkSessionAndUpdateUI();

    // Handle registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate form
            const isValid = validateRegistrationForm();
            if (!isValid) {
                notifications.showToast('error', 'Validation Error', 'Please fix the errors in the form before submitting');
                return;
            }

            notifications.setFormLoading(registerForm, true);
            notifications.showToast('info', 'Processing', 'Creating your account...');

            try {
                const formData = new FormData(registerForm);
                const result = await apiCall('REGISTER', formData, 'POST');

                if (result.success) {
                    notifications.showToast('success', 'Registration Successful', result.message || 'Your account has been created successfully!');
                    registerForm.reset();
                    clearFormValidation(registerForm);

                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    notifications.showToast('error', 'Registration Failed', result.message || 'Failed to create account. Please try again.');
                }
            } catch (error) {
                notifications.showToast('error', 'Network Error', 'Unable to connect to server. Please check your connection.');
            } finally {
                notifications.setFormLoading(registerForm, false);
            }
        });
    }

    // Handle login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate form
            const isValid = validateLoginForm();
            if (!isValid) {
                notifications.showToast('error', 'Validation Error', 'Please enter valid credentials');
                return;
            }

            notifications.setFormLoading(loginForm, true);
            notifications.showToast('info', 'Authenticating', 'Verifying your credentials...');

            try {
                const formData = new FormData(loginForm);
                const result = await apiCall('LOGIN', formData, 'POST');

                if (result.success) {
                    notifications.showToast('success', 'Login Successful', 'Welcome back! Redirecting to dashboard...');

                    // Store real user session data with correct key
                    if (result.user) {
                        localStorage.setItem('bloodlink_user_data', JSON.stringify(result.user));
                        console.log('Stored real user data:', result.user);
                    }

                    // Redirect to monitor page instead of index
                    setTimeout(() => {
                        window.location.href = 'monitor.html';
                    }, 1500);
                } else {
                    notifications.showToast('error', 'Login Failed', result.message || 'Invalid credentials. Please try again.');
                    // Clear password field on failed login
                    loginForm.querySelector('input[name="password"]').value = '';
                }
            } catch (error) {
                notifications.showToast('error', 'Network Error', 'Unable to connect to server. Please check your connection.');
            } finally {
                notifications.setFormLoading(loginForm, false);
            }
        });
    }

    // Handle request form
    const requestForm = document.getElementById('requestForm');
    if (requestForm) {
        requestForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate form
            const isValid = validateRequestForm();
            if (!isValid) {
                notifications.showToast('error', 'Validation Error', 'Please fill in all required fields correctly');
                return;
            }

            notifications.setFormLoading(requestForm, true);
            notifications.showToast('info', 'Submitting Request', 'Creating blood request...');

            try {
                const formData = new FormData(requestForm);
                const result = await apiCall('REQUEST', formData, 'POST');

                if (result.success) {
                    notifications.showToast('success', 'Request Submitted', 'Your blood request has been submitted successfully!');
                    requestForm.reset();
                    clearFormValidation(requestForm);
                    loadRequests();
                } else {
                    notifications.showToast('error', 'Submission Failed', result.message || 'Failed to submit request. Please try again.');
                }
            } catch (error) {
                notifications.showToast('error', 'Network Error', 'Unable to submit request. Please check your connection.');
            } finally {
                notifications.setFormLoading(requestForm, false);
            }
        });
    }

    // Handle search form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await performSearch();
        });
    }

    // Handle contact form if exists
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            notifications.setFormLoading(contactForm, true);
            notifications.showToast('info', 'Sending Message', 'Submitting your message...');

            try {
                // Simulate API call for contact form
                await new Promise(resolve => setTimeout(resolve, 1500));

                notifications.showToast('success', 'Message Sent', 'Thank you for your message. We\'ll get back to you soon!');
                contactForm.reset();
                clearFormValidation(contactForm);
            } catch (error) {
                notifications.showToast('error', 'Send Failed', 'Failed to send message. Please try again.');
            } finally {
                notifications.setFormLoading(contactForm, false);
            }
        });
    }

    // Load pending requests on request page
    if (document.getElementById('requestsList')) {
        loadRequests();
    }

    // Initialize real-time validation
    initializeRealTimeValidation();

    // Initialize page features
    initializePage();
});

// Form validation functions
function validateRegistrationForm() {
    const form = document.getElementById('registerForm');
    let isValid = true;

    // Validate name
    const name = form.querySelector('input[name="name"]');
    if (name) {
        const result = notifications.validateField(name, {
            required: true,
            minLength: 2,
            label: 'Full name'
        });
        if (!result.isValid) isValid = false;
    }

    // Validate email
    const email = form.querySelector('input[name="email"]');
    if (email) {
        const result = notifications.validateField(email, {
            required: true,
            email: true,
            label: 'Email'
        });
        if (!result.isValid) isValid = false;
    }

    // Validate phone
    const phone = form.querySelector('input[name="phone"]');
    if (phone) {
        const result = notifications.validateField(phone, {
            required: true,
            phone: true,
            label: 'Phone number'
        });
        if (!result.isValid) isValid = false;
    }

    // Validate password
    const password = form.querySelector('input[name="password"]');
    if (password) {
        const result = notifications.validateField(password, {
            required: true,
            minLength: 6,
            label: 'Password'
        });
        if (!result.isValid) isValid = false;
    }

    return isValid;
}

function validateLoginForm() {
    const form = document.getElementById('loginForm');
    let isValid = true;

    const email = form.querySelector('input[name="email"]');
    if (email) {
        const result = notifications.validateField(email, {
            required: true,
            email: true,
            label: 'Email'
        });
        if (!result.isValid) isValid = false;
    }

    const password = form.querySelector('input[name="password"]');
    if (password) {
        const result = notifications.validateField(password, {
            required: true,
            label: 'Password'
        });
        if (!result.isValid) isValid = false;
    }

    return isValid;
}

function validateRequestForm() {
    const form = document.getElementById('requestForm');
    let isValid = true;

    // Validate required fields
    const requiredFields = [
        { name: 'blood_group', label: 'Blood group' },
        { name: 'hospital', label: 'Hospital name' },
        { name: 'location', label: 'Location' },
        { name: 'urgency', label: 'Urgency level' },
        { name: 'units_needed', label: 'Units needed' },
        { name: 'required_date', label: 'Required date' },
        { name: 'requester_contact', label: 'Contact information' }
    ];

    requiredFields.forEach(field => {
        const element = form.querySelector(`[name="${field.name}"]`);
        if (element) {
            const result = notifications.validateField(element, {
                required: true,
                label: field.label
            });
            if (!result.isValid) isValid = false;
        }
    });

    return isValid;
}

function initializeRealTimeValidation() {
    // Add real-time validation to all forms
    document.querySelectorAll('input, select, textarea').forEach(field => {
        if (field.name) {
            field.addEventListener('blur', () => {
                validateFieldByName(field);
            });

            field.addEventListener('input', () => {
                // Clear error state on input
                if (field.classList.contains('field-error')) {
                    field.classList.remove('field-error');
                    const feedback = field.parentNode.querySelector('.form-feedback.error');
                    if (feedback) feedback.remove();
                }
            });
        }
    });
}

function validateFieldByName(field) {
    const rules = getValidationRules(field.name);
    if (rules) {
        return notifications.validateField(field, rules);
    }
    return { isValid: true, message: '' };
}

function getValidationRules(fieldName) {
    const rules = {
        'name': { required: true, minLength: 2, label: 'Full name' },
        'email': { required: true, email: true, label: 'Email' },
        'phone': { required: true, phone: true, label: 'Phone number' },
        'password': { required: true, minLength: 6, label: 'Password' },
        'blood_group': { required: true, label: 'Blood group' },
        'hospital': { required: true, label: 'Hospital name' },
        'location': { required: true, label: 'Location' },
        'urgency': { required: true, label: 'Urgency level' },
        'units_needed': { required: true, label: 'Units needed' },
        'required_date': { required: true, label: 'Required date' },
        'requester_contact': { required: true, label: 'Contact information' }
    };

    return rules[fieldName];
}

function clearFormValidation(form) {
    form.querySelectorAll('.field-success, .field-error').forEach(field => {
        field.classList.remove('field-success', 'field-error');
    });

    form.querySelectorAll('.form-feedback').forEach(feedback => {
        feedback.remove();
    });
}

function initializeFormValidation() {
    // Add tooltips to form elements
    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
        notifications.addTooltip(field, 'This field is required');
    });

    // Add specific tooltips
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        notifications.addTooltip(field, 'Enter a valid email address (e.g., user@example.com)');
    });

    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        notifications.addTooltip(field, 'Password must be at least 6 characters long');
    });
}

// Enhanced search functionality
async function performSearch() {
    const searchForm = document.getElementById('searchForm');
    const resultsContainer = document.getElementById('searchResults');

    if (!searchForm || !resultsContainer) return;

    notifications.setFormLoading(searchForm, true);
    notifications.showToast('info', 'Searching', 'Looking for blood donors and inventory...');

    try {
        const formData = new FormData(searchForm);
        const result = await apiCall('SEARCH', formData, 'POST');

        if (result.success) {
            displaySearchResults(result.results, resultsContainer);

            const totalResults = (result.results.donors?.length || 0) + (result.results.inventory?.length || 0);
            notifications.showToast('success', 'Search Complete', `Found ${totalResults} results matching your criteria`);
        } else {
            notifications.showToast('warning', 'No Results', 'No donors or blood units found matching your search criteria');
            resultsContainer.innerHTML = '<div class="text-center py-8 text-gray-500">No results found. Try adjusting your search criteria.</div>';
        }
    } catch (error) {
        notifications.showToast('error', 'Search Failed', 'Unable to perform search. Please try again.');
    } finally {
        notifications.setFormLoading(searchForm, false);
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
    if (results.donors && results.donors.length > 0) {
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
    if (results.inventory && results.inventory.length > 0) {
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
        notifications.showToast('error', 'Loading Error', 'Failed to load blood requests');
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
    // Load user info if logged in
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.name) {
        const userInfo = document.getElementById('userInfo');
        if (userInfo) {
            userInfo.innerHTML = `Welcome, ${user.name} (${user.blood_group})`;
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
}

// Session management functions
async function checkSessionAndUpdateUI() {
    try {
        console.log('Checking session status...');
        const response = await fetch('/backend/session_check.php', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();
        console.log('Session check result:', result);

        if (result.success && result.logged_in && result.user) {
            updateUIForLoggedInUser(result.user);
        } else {
            updateUIForLoggedOutUser();
        }
    } catch (error) {
        console.error('Session check failed:', error);
        updateUIForLoggedOutUser();
    }
}

function updateUIForLoggedInUser(user) {
    console.log('Updating UI for logged in user:', user);

    // Update navigation
    updateNavigation(user);

    // Show user profile section on homepage
    if (window.location.pathname.includes('index.html') || window.location.pathname === '/') {
        showUserDashboard(user);
    }

    // Update page content based on user status
    updatePageContent(user);
}

function updateUIForLoggedOutUser() {
    console.log('Updating UI for logged out user');

    // Show default navigation
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.innerHTML = `
            <div class="flex items-center space-x-4">
                <a href="login.html" class="text-white hover:text-red-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-sign-in-alt mr-1"></i>Login
                </a>
                <a href="register.html" class="bg-white text-blood-600 hover:bg-red-50 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-user-plus mr-1"></i>Register
                </a>
            </div>
        `;
    }

    // Hide user dashboard if present
    const userDashboard = document.getElementById('userDashboard');
    if (userDashboard) {
        userDashboard.style.display = 'none';
    }
}

function updateNavigation(user) {
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.innerHTML = `
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 text-white">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <div class="text-sm">
                        <div class="font-medium">Welcome, ${user.name}</div>
                        <div class="text-red-200 text-xs">${user.blood_group} • ${user.location}</div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="monitor.html" class="text-white hover:text-red-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <button onclick="logout()" class="text-white hover:text-red-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </button>
                </div>
            </div>
        `;
    }
}

function showUserDashboard(user) {
    // Find the hero section to insert user dashboard after it
    const heroSection = document.querySelector('section.relative.bg-gradient-to-br');
    if (!heroSection) return;

    // Remove existing user dashboard if present
    const existingDashboard = document.getElementById('userDashboard');
    if (existingDashboard) {
        existingDashboard.remove();
    }

    // Create user dashboard section
    const userDashboard = document.createElement('section');
    userDashboard.id = 'userDashboard';
    userDashboard.className = 'py-16 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200';

    userDashboard.innerHTML = `
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome back, ${user.name}!</h2>
                <p class="text-lg text-gray-600">Here's your profile and recent activity</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- User Profile Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-blood-500 to-blood-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">${user.name}</h3>
                            <p class="text-gray-600">${user.email}</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600">Blood Group</span>
                                <span class="font-semibold text-blood-600 text-lg">${user.blood_group}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600">Location</span>
                                <span class="font-medium text-gray-900">${user.location}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600">Contact</span>
                                <span class="font-medium text-gray-900">${user.contact}</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-gray-600">User ID</span>
                                <span class="font-mono text-sm text-gray-500">#${user.id}</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <a href="monitor.html" class="w-full bg-gradient-to-r from-blood-600 to-blood-700 text-white font-medium py-3 px-4 rounded-lg hover:from-blood-700 hover:to-blood-800 transition-all duration-200 flex items-center justify-center">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                View Full Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-heart text-white"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Donate Blood</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Ready to save lives? Schedule your next donation.</p>
                            <a href="request.html" class="text-green-600 hover:text-green-700 font-medium">
                                Schedule Donation <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Find Donors</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Search for blood donors in your area.</p>
                            <a href="search.html" class="text-blue-600 hover:text-blue-700 font-medium">
                                Search Now <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-hand-holding-medical text-white"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Request Blood</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Need blood urgently? Submit a request.</p>
                            <a href="request.html" class="text-purple-600 hover:text-purple-700 font-medium">
                                Make Request <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blood-500 to-blood-600 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Monitor Activity</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Track donations and requests in real-time.</p>
                            <a href="monitor.html" class="text-blood-600 hover:text-blood-700 font-medium">
                                View Monitor <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Insert after hero section
    heroSection.parentNode.insertBefore(userDashboard, heroSection.nextSibling);
}

function updatePageContent(user) {
    // Update any user-specific content on other pages
    const welcomeMessages = document.querySelectorAll('.user-welcome');
    welcomeMessages.forEach(element => {
        element.textContent = `Welcome, ${user.name}!`;
    });

    // Update blood group displays
    const bloodGroupDisplays = document.querySelectorAll('.user-blood-group');
    bloodGroupDisplays.forEach(element => {
        element.textContent = user.blood_group;
    });
}

async function logout() {
    try {
        notifications.showToast('info', 'Logging out', 'Ending your session...');

        const response = await fetch('/backend/logout.php', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            notifications.showToast('success', 'Logged out', 'You have been logged out successfully');

            // Clear any stored user data
            localStorage.removeItem('bloodlink_user_data');

            // Update UI
            updateUIForLoggedOutUser();

            // Redirect to home page after delay
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            notifications.showToast('error', 'Logout failed', 'Failed to logout properly');
        }
    } catch (error) {
        console.error('Logout error:', error);
        notifications.showToast('error', 'Network error', 'Unable to logout due to network issues');
    }
}
