console.log('=== APP STARTED ===');
console.log('AppConfig:', AppConfig);

// ========== DOM Elements ==========
var loginForm = document.getElementById('loginForm');
var registerForm = document.getElementById('registerForm');
var loginDiv = document.getElementById('login-form');
var registerDiv = document.getElementById('register-form');
var showRegister = document.getElementById('showRegister');
var showLogin = document.getElementById('showLogin');
var toast = document.getElementById('toast');
var passwordInput = document.getElementById('registerPassword');
var strengthFill = document.getElementById('strengthFill');
var strengthText = document.getElementById('strengthText');
var toggleSidebar = document.getElementById('toggleSidebar');
var sidebar = document.getElementById('sidebar');

// ========== Sidebar Toggle ==========
if (toggleSidebar && sidebar) {
    toggleSidebar.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        sidebar.classList.toggle('open');
    });
}

// ========== Form Toggle ==========
if (showRegister) {
    showRegister.addEventListener('click', function(e) {
        e.preventDefault();
        loginDiv.classList.remove('active');
        registerDiv.classList.add('active');
        clearErrors();
    });
}

if (showLogin) {
    showLogin.addEventListener('click', function(e) {
        e.preventDefault();
        registerDiv.classList.remove('active');
        loginDiv.classList.add('active');
        clearErrors();
    });
}

// ========== Password Strength ==========
if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        var password = this.value;
        var strength = checkPasswordStrength(password);
        
        if (strengthFill) {
            strengthFill.style.width = strength.percent + '%';
            strengthFill.style.background = strength.color;
        }
        if (strengthText) {
            strengthText.textContent = strength.label;
            strengthText.style.color = strength.color;
        }
    });
}

function checkPasswordStrength(password) {
    var score = 0;
    if (password.length >= 8) score++;
    if (password.match(/[A-Z]/)) score++;
    if (password.match(/[0-9]/)) score++;
    if (password.match(/[^A-Za-z0-9]/)) score++;
    
    var map = {
        0: { label: 'Weak', color: '#d32f2f', percent: 25 },
        1: { label: 'Weak', color: '#d32f2f', percent: 40 },
        2: { label: 'Fair', color: '#f57c00', percent: 60 },
        3: { label: 'Good', color: '#2d8f2d', percent: 80 },
        4: { label: 'Strong', color: '#1a7a1a', percent: 100 }
    };
    return map[score] || map[0];
}

// ========== Registration ==========
if (registerForm) {
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        
        if (!validateRegistration()) return;
        
        var formData = new FormData(registerForm);
        
        try {
            var response = await fetch('register.php', {
                method: 'POST',
                body: formData
            });
            
            var result = await response.json();
            console.log('Registration:', result);
            
            if (result.success) {
                showToast('Registration successful!', 'success');
                registerForm.reset();
                resetStrengthBar();
                
                if (AppConfig.isLoggedIn && AppConfig.userRole === 'admin' && result.user) {
                    addUserToTable(result.user);
                    updateStats();
                }
                
                registerDiv.classList.remove('active');
                loginDiv.classList.add('active');
            } else {
                if (result.errors) {
                    displayErrors(result.errors);
                } else {
                    showToast(result.message || 'Registration failed', 'error');
                }
            }
        } catch (error) {
            showToast('An error occurred', 'error');
            console.error('Registration error:', error);
        }
    });
}

function addUserToTable(user) {
    var tbody = document.getElementById('userTableBody');
    if (!tbody) return;
    
    // Remove loading message
    if (tbody.querySelector('td[colspan]')) {
        tbody.innerHTML = '';
    }
    
    var tr = document.createElement('tr');
    tr.dataset.userId = user.id;
    tr.style.animation = 'highlightRow 2s ease';
    
    var image = user.profile_image || 'default.png';
    tr.innerHTML = `
        <td><img src="uploads/${image}" alt="Profile" class="user-thumb" onerror="this.src='uploads/default.png'"></td>
        <td>${escapeHtml(user.full_name)}</td>
        <td>${escapeHtml(user.email)}</td>
        <td>${escapeHtml(user.gender)}</td>
        <td>${escapeHtml(user.country)}</td>
        <td>${new Date(user.created_at).toLocaleDateString()}</td>
        <td>
            <button class="delete-btn" data-userid="${user.id}" data-image="${user.profile_image || 'default.png'}">
                Delete
            </button>
        </td>
    `;
    
    tbody.insertBefore(tr, tbody.firstChild);
    tr.querySelector('.delete-btn').addEventListener('click', handleDeleteUser);
    showToast('New user: ' + user.full_name, 'info');
}

function updateStats() {
    var totalUsers = document.getElementById('totalUsers');
    var userCountBadge = document.getElementById('userCountBadge');
    if (totalUsers) totalUsers.textContent = parseInt(totalUsers.textContent) + 1;
    if (userCountBadge) userCountBadge.textContent = parseInt(userCountBadge.textContent) + 1;
}

function validateRegistration() {
    var isValid = true;
    var name = document.getElementById('fullName').value.trim();
    var email = document.getElementById('registerEmail').value.trim();
    var password = document.getElementById('registerPassword').value;
    var confirm = document.getElementById('confirmPassword').value;
    var gender = document.querySelector('input[name="gender"]:checked');
    var country = document.getElementById('country').value;
    var privacy = document.getElementById('privacyAgreed').checked;
    var image = document.getElementById('profileImage').files[0];
    
    if (name.length < 3) {
        showFieldError('fullNameError', 'Name must be at least 3 characters');
        isValid = false;
    }
    
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        showFieldError('registerEmailError', 'Please enter a valid email');
        isValid = false;
    }
    
    var strength = checkPasswordStrength(password);
    if (strength.percent < 40) {
        showFieldError('registerPasswordError', 'Password must be at least 8 chars with uppercase, number & special character');
        isValid = false;
    }
    
    if (password !== confirm) {
        showFieldError('confirmPasswordError', 'Passwords do not match');
        isValid = false;
    }
    
    if (!gender) {
        showFieldError('genderError', 'Please select a gender');
        isValid = false;
    }
    
    if (!country) {
        showFieldError('countryError', 'Please select a country');
        isValid = false;
    }
    
    if (!privacy) {
        showFieldError('privacyError', 'You must agree to the Privacy Policy');
        isValid = false;
    }
    
    if (image) {
        var validTypes = ['image/jpeg', 'image/png'];
        if (!validTypes.includes(image.type)) {
            showFieldError('profileImageError', 'Only JPG and PNG images allowed');
            isValid = false;
        }
        if (image.size > 2 * 1024 * 1024) {
            showFieldError('profileImageError', 'Image must be less than 2MB');
            isValid = false;
        }
    }
    
    return isValid;
}

// ========== Login ==========
if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        
        var email = document.getElementById('loginEmail').value.trim();
        var password = document.getElementById('loginPassword').value;
        
        if (!email || !password) {
            showToast('Please fill in all fields', 'error');
            return;
        }
        
        var formData = new FormData(loginForm);
        
        try {
            var response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });
            
            var result = await response.json();
            console.log('Login:', result);
            
            if (result.success) {
                showToast('Login successful!', 'success');
                setTimeout(function() { window.location.reload(); }, 500);
            } else {
                showToast(result.message || 'Invalid credentials', 'error');
            }
        } catch (error) {
            showToast('An error occurred', 'error');
            console.error('Login error:', error);
        }
    });
}

// ========== Logout ==========
document.addEventListener('click', async function(e) {
    if (e.target.id === 'logoutBtn' || e.target.closest('#logoutBtn')) {
        e.preventDefault();
        
        try {
            var response = await fetch('logout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: AppConfig.csrfToken })
            });
            
            var result = await response.json();
            
            if (result.success) {
                showToast('Logged out', 'info');
                setTimeout(function() { window.location.reload(); }, 500);
            }
        } catch (error) {
            showToast('Logout failed', 'error');
            console.error('Logout error:', error);
        }
    }
});

// ========== LOAD USERS - MAIN FUNCTION ==========
function loadUsers() {
    console.log('🔄 loadUsers() CALLED');
    
    var tbody = document.getElementById('userTableBody');
    if (!tbody) {
        console.error('❌ Table body NOT FOUND!');
        return;
    }
    console.log('✅ Table body FOUND');
    
    // Show loading
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#888;"><i class="fas fa-spinner fa-spin"></i> Loading users...</td></tr>';
    
    // AJAX call
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log('📡 Response received');
            
            try {
                var data = JSON.parse(this.responseText);
                console.log('📡 Data:', data);
                
                if (data.success) {
                    var users = data.users;
                    console.log('✅ Users count:', users.length);
                    
                    // Clear loading
                    tbody.innerHTML = '';
                    
                    if (users.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#888;">No users found</td></tr>';
                        return;
                    }
                    
                    // Add each user to table
                    for (var i = 0; i < users.length; i++) {
                        var user = users[i];
                        var image = user.profile_image || 'default.png';
                        
                        var row = document.createElement('tr');
                        row.dataset.userId = user.id;
                        row.innerHTML = `
                            <td><img src="uploads/${image}" class="user-thumb" onerror="this.src='uploads/default.png'"></td>
                            <td>${escapeHtml(user.full_name)}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>${escapeHtml(user.gender)}</td>
                            <td>${escapeHtml(user.country)}</td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                            <td><button class="delete-btn" data-userid="${user.id}" data-image="${image}">Delete</button></td>
                        `;
                        tbody.appendChild(row);
                    }
                    
                    // Update stats
                    if (document.getElementById('totalUsers')) {
                        document.getElementById('totalUsers').textContent = data.total || 0;
                    }
                    if (document.getElementById('userCountBadge')) {
                        document.getElementById('userCountBadge').textContent = data.total || 0;
                    }
                    if (document.getElementById('newUsersToday')) {
                        document.getElementById('newUsersToday').textContent = data.today || 0;
                    }
                    if (document.getElementById('activeUsers')) {
                        document.getElementById('activeUsers').textContent = data.total || 0;
                    }
                    
                    // Add delete handlers
                    document.querySelectorAll('.delete-btn').forEach(function(btn) {
                        btn.addEventListener('click', handleDeleteUser);
                    });
                    
                    console.log('✅ Table rendered with ' + users.length + ' users');
                    showToast('Loaded ' + users.length + ' users', 'info');
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#e74c3c;">Error: ' + data.message + '</td></tr>';
                }
            } catch(e) {
                console.error('❌ Parse error:', e);
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#e74c3c;">Error parsing data</td></tr>';
            }
        }
    };
    xhttp.open('GET', 'get_users.php', true);
    xhttp.send();
}

// ========== DELETE USER ==========
function handleDeleteUser(e) {
    var userId = e.target.dataset.userid;
    var image = e.target.dataset.image;
    
    if (!confirm('Delete this user?')) return;
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var data = JSON.parse(this.responseText);
                if (data.success) {
                    showToast('User deleted', 'success');
                    var row = document.querySelector('tr[data-user-id="' + userId + '"]');
                    if (row) {
                        row.style.opacity = '0';
                        setTimeout(function() { row.remove(); }, 300);
                    }
                    
                    // Update stats
                    ['totalUsers', 'userCountBadge', 'activeUsers'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) {
                            el.textContent = parseInt(el.textContent) - 1;
                        }
                    });
                } else {
                    showToast(data.message || 'Delete failed', 'error');
                }
            } catch(e) {
                showToast('Error deleting user', 'error');
            }
        }
    };
    xhttp.open('POST', 'delete_user.php', true);
    xhttp.setRequestHeader('Content-Type', 'application/json');
    xhttp.send(JSON.stringify({
        user_id: userId,
        image: image,
        csrf_token: AppConfig.csrfToken
    }));
}

// ========== SEARCH ==========
function setupSearch() {
    var searchInput = document.getElementById('searchUsers');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            var term = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#userTableBody tr');
            
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
        console.log('✅ Search setup');
    }
}

// ========== REFRESH ==========
function setupRefresh() {
    var refreshBtn = document.getElementById('refreshUsers');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            showToast('Refreshing...', 'info');
            loadUsers();
        });
        console.log('✅ Refresh button setup');
    }
}

// ========== NAVIGATION ==========
function setupNav() {
    var navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(function(l) { l.classList.remove('active'); });
            this.classList.add('active');
            
            var page = this.dataset.page;
            var pageTitle = document.getElementById('pageTitle');
            
            document.querySelectorAll('.page-content').forEach(function(p) {
                p.classList.remove('active');
            });
            
            if (page === 'dashboard' || page === 'users') {
                var dashboard = document.getElementById('admin-dashboard');
                if (dashboard) {
                    dashboard.classList.add('active');
                    if (page === 'users') {
                        loadUsers();
                    }
                }
                if (pageTitle) {
                    pageTitle.textContent = page === 'dashboard' ? 'Dashboard' : 'Users Management';
                }
            } else if (page === 'profile') {
                var profile = document.getElementById('user-dashboard');
                if (profile) profile.classList.add('active');
                if (pageTitle) pageTitle.textContent = 'Profile';
            } else if (page === 'settings') {
                showToast('Settings coming soon!', 'info');
                var dashboard = document.getElementById('admin-dashboard');
                if (dashboard) dashboard.classList.add('active');
                if (pageTitle) pageTitle.textContent = 'Dashboard';
            }
        });
    });
    console.log('✅ Navigation setup');
}

// ========== UTILITY FUNCTIONS ==========
function showFieldError(elementId, message) {
    var el = document.getElementById(elementId);
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
    }
}

function clearErrors() {
    document.querySelectorAll('.error').forEach(function(el) {
        el.textContent = '';
        el.style.display = 'none';
    });
}

function displayErrors(errors) {
    for (var field in errors) {
        var errorId = field + 'Error';
        showFieldError(errorId, errors[field]);
    }
}

function showToast(message, type) {
    type = type || 'info';
    if (!toast) return;
    toast.textContent = message;
    toast.className = type;
    toast.style.display = 'block';
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(function() {
        toast.style.display = 'none';
    }, 4000);
}

function resetStrengthBar() {
    if (strengthFill) {
        strengthFill.style.width = '0%';
        strengthFill.style.background = '#e8f0e8';
    }
    if (strengthText) {
        strengthText.textContent = 'Weak';
        strengthText.style.color = '#4a7a4a';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== CLOCK ==========
function updateClock() {
    var clock = document.getElementById('headerTime');
    if (clock) {
        var now = new Date();
        clock.textContent = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
    }
}
setInterval(updateClock, 1000);
updateClock();

// ========== INITIALIZE ==========
console.log('=== INITIALIZING APPLICATION ===');

// Check if admin and load users
if (AppConfig.isLoggedIn && AppConfig.userRole === 'admin') {
    console.log('✅ Admin detected - loading users...');
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM ready - loading users...');
            setTimeout(loadUsers, 500);
            setupSearch();
            setupRefresh();
            setupNav();
        });
    } else {
        console.log('📄 DOM already ready - loading users...');
        setTimeout(loadUsers, 500);
        setupSearch();
        setupRefresh();
        setupNav();
    }
} else {
    console.log('❌ Not admin or not logged in');
}

clearErrors();
console.log('=== APP READY ===');