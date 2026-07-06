<?php
require_once 'config/database.php';

$countries = getCountries();
$csrf_token = generateCSRFToken();

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;
$userData = null;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gexton Academy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if (!$isLoggedIn): ?>
        <!-- ========== AUTH PAGE ========== -->
        <div class="auth-page">
            <div class="auth-container">
                <div class="auth-brand">
                    <i class="fas fa-shield-alt"></i>
                    <h1>Gexton Academy</h1>
                    <p>Green Authentication System</p>
                </div>
                
                <div id="auth-forms">
                    <!-- Login Form -->
                    <div id="login-form" class="form-container active">
                        <h2>Welcome Back</h2>
                        <form id="loginForm" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" name="email" id="loginEmail" placeholder="Enter your email" required>
                                <div class="error" id="loginEmailError"></div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Password</label>
                                <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                                <div class="error" id="loginPasswordError"></div>
                            </div>
                            <button type="submit"><i class="fas fa-sign-in-alt"></i> Sign In</button>
                            <p>Don't have an account? <a href="#" id="showRegister">Register here</a></p>
                        </form>
                    </div>

                    <!-- Register Form -->
                    <div id="register-form" class="form-container">
                        <h2>Create Account</h2>
                        <form id="registerForm" enctype="multipart/form-data" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Full Name</label>
                                    <input type="text" name="full_name" id="fullName" placeholder="Enter your full name" required>
                                    <div class="error" id="fullNameError"></div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email Address</label>
                                    <input type="email" name="email" id="registerEmail" placeholder="Enter your email" required>
                                    <div class="error" id="registerEmailError"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-lock"></i> Password</label>
                                    <input type="password" name="password" id="registerPassword" placeholder="Create a password" required>
                                    <div class="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill" style="width:0%"></div>
                                        </div>
                                        <span id="strengthText">Weak</span>
                                    </div>
                                    <div class="error" id="registerPasswordError"></div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-check-circle"></i> Confirm Password</label>
                                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm your password" required>
                                    <div class="error" id="confirmPasswordError"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="gender" value="male"> Male</label>
                                        <label><input type="radio" name="gender" value="female"> Female</label>
                                        <label><input type="radio" name="gender" value="other"> Other</label>
                                    </div>
                                    <div class="error" id="genderError"></div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-globe"></i> Country</label>
                                    <select name="country" id="country">
                                        <option value="">Select your country</option>
                                        <?php foreach($countries as $country): ?>
                                            <option value="<?= $country ?>"><?= $country ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="error" id="countryError"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Profile Image</label>
                                    <input type="file" name="profile_image" id="profileImage" accept=".jpg,.jpeg,.png">
                                    <small>JPEG or PNG, max 2MB</small>
                                    <div class="error" id="profileImageError"></div>
                                </div>

                                <div class="form-group checkbox-group">
                                    <label>
                                        <input type="checkbox" name="privacy_agreed" id="privacyAgreed">
                                        I agree to the <a href="#">Privacy Policy</a>
                                    </label>
                                    <div class="error" id="privacyError"></div>
                                </div>
                            </div>

                            <button type="submit"><i class="fas fa-user-plus"></i> Register</button>
                            <p>Already have an account? <a href="#" id="showLogin">Sign in here</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- ========== DASHBOARD WITH SIDEBAR ========== -->
        <div class="dashboard-wrapper">
            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-brand">
                    <i class="fas fa-shield-alt"></i>
                    <span>Gexton Academy</span>
                </div>
                
                <div class="sidebar-user">
                    <img src="uploads/<?= $userData['profile_image'] ?? 'default.png' ?>" 
                         alt="Profile" class="sidebar-avatar"
                         onerror="this.src='uploads/default.png'">
                    <div class="sidebar-user-info">
                        <h4><?= htmlspecialchars($userData['full_name']) ?></h4>
                        <span><?= htmlspecialchars($userData['role']) ?></span>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    <?php if ($userRole === 'admin'): ?>
                        <a href="#" class="nav-link active" data-page="dashboard">
                            <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
                        </a>
                        <a href="#" class="nav-link" data-page="users">
                            <i class="fas fa-users"></i> <span>Users</span>
                            <span class="badge" id="userCountBadge">0</span>
                        </a>
                        <a href="#" class="nav-link" data-page="settings">
                            <i class="fas fa-cog"></i> <span>Settings</span>
                        </a>
                    <?php else: ?>
                        <a href="#" class="nav-link active" data-page="profile">
                            <i class="fas fa-user"></i> <span>Profile</span>
                        </a>
                        <a href="#" class="nav-link" data-page="settings">
                            <i class="fas fa-cog"></i> <span>Settings</span>
                        </a>
                    <?php endif; ?>
                </nav>

                <div class="sidebar-footer">
                    <button id="logoutBtn" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <header class="main-header">
                    <div class="header-left">
                        <button class="toggle-sidebar" id="toggleSidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 id="pageTitle"><?= $userRole === 'admin' ? 'Dashboard' : 'Profile' ?></h2>
                    </div>
                    <div class="header-right">
                        <span class="header-time" id="headerTime"></span>
                    </div>
                </header>

                <div class="content-area">
                    <?php if ($userRole === 'admin'): ?>
                        <!-- Admin Dashboard -->
                        <div id="admin-dashboard" class="page-content active">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    <div class="stat-info">
                                        <h3>Total Users</h3>
                                        <p id="totalUsers">0</p>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                                    <div class="stat-info">
                                        <h3>New Today</h3>
                                        <p id="newUsersToday">0</p>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                                    <div class="stat-info">
                                        <h3>Active Users</h3>
                                        <p id="activeUsers">0</p>
                                    </div>
                                </div>
                            </div>

                            <div class="table-wrapper">
                                <div class="table-header">
                                    <h3><i class="fas fa-users"></i> All Users</h3>
                                    <div class="table-actions">
                                        <input type="text" id="searchUsers" placeholder="Search users...">
                                        <button id="refreshUsers" style="margin-left:10px;padding:8px 15px;background:#1a7a1a;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            <i class="fas fa-sync"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div style="overflow-x:auto;">
                                    <table id="userTable">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Gender</th>
                                                <th>Country</th>
                                                <th>Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="userTableBody">
                                            <tr>
                                                <td colspan="7" style="text-align:center;padding:30px;color:#888;">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading users...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- User Profile -->
                        <div id="user-dashboard" class="page-content active">
                            <div class="profile-card-modern">
                                <div class="profile-header">
                                    <img src="uploads/<?= $userData['profile_image'] ?? 'default.png' ?>" 
                                         alt="Profile" class="profile-avatar-large"
                                         onerror="this.src='uploads/default.png'">
                                    <div class="profile-header-info">
                                        <h2><?= htmlspecialchars($userData['full_name']) ?></h2>
                                        <span class="profile-role"><?= htmlspecialchars($userData['role']) ?></span>
                                        <div class="profile-actions">
                                            <button class="btn-primary"><i class="fas fa-edit"></i> Edit Profile</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-details-grid">
                                    <div class="detail-item">
                                        <i class="fas fa-envelope"></i>
                                        <div>
                                            <label>Email</label>
                                            <p><?= htmlspecialchars($userData['email']) ?></p>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-venus-mars"></i>
                                        <div>
                                            <label>Gender</label>
                                            <p><?= htmlspecialchars($userData['gender']) ?></p>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-globe"></i>
                                        <div>
                                            <label>Country</label>
                                            <p><?= htmlspecialchars($userData['country']) ?></p>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <div>
                                            <label>Member Since</label>
                                            <p><?= date('M d, Y', strtotime($userData['created_at'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    <?php endif; ?>

    <!-- Toast Notification -->
    <div id="toast" style="display:none;"></div>

    <!-- ====== IMPORTANT: AppConfig MUST be defined BEFORE app.js ====== -->
    <script>
        const AppConfig = {
            csrfToken: '<?= $csrf_token ?>',
            isLoggedIn: <?= json_encode($isLoggedIn) ?>,
            userRole: '<?= $userRole ?>',
            userId: '<?= $_SESSION['user_id'] ?? '' ?>'
        };
        console.log('✅ AppConfig defined:', AppConfig);
    </script>
    
    <!-- Load app.js AFTER AppConfig -->
    <script src="js/app.js"></script>
</body>
</html>