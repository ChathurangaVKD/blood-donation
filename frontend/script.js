// script.js - Updated with modern notification system
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation and notifications
    initializeFormValidation();

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

                    // Store user session data
                    if (result.user) {
                        localStorage.setItem('user', JSON.stringify(result.user));
                    }

                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = 'index.html';
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
