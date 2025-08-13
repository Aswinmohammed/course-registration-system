// Authentication and session management
class AuthManager {
  constructor() {
    this.user = null
    this.init()
  }

  init() {
    // Load user from localStorage
    const userData = localStorage.getItem("user")
    if (userData) {
      this.user = JSON.parse(userData)
    }

    // Validate session on page load
    this.validateSession()
  }

  async validateSession() {
    try {
      const response = await fetch("../../backend/controllers/auth.php?action=validate")
      const data = await response.json()

      if (data.success) {
        this.user = data.user
        localStorage.setItem("user", JSON.stringify(data.user))
        this.updateUI()
      } else {
        this.logout()
      }
    } catch (error) {
      console.error("Session validation error:", error)
      this.logout()
    }
  }

  async logout() {
    try {
      await fetch("../../backend/controllers/auth.php?action=logout", {
        method: "POST",
      })
    } catch (error) {
      console.error("Logout error:", error)
    }

    this.user = null
    localStorage.removeItem("user")
    window.location.href = "login.html"
  }

  requireAuth() {
    if (!this.user) {
      window.location.href = "login.html"
      return false
    }
    return true
  }

  requireRole(role) {
    if (!this.requireAuth()) return false

    if (this.user.role !== role) {
      this.showAccessDenied()
      return false
    }
    return true
  }

  hasRole(role) {
    return this.user && this.user.role === role
  }

  showAccessDenied() {
    alert("Access denied. You do not have permission to view this page.")
    if (this.user.role === "admin") {
      window.location.href = "index.html"
    } else {
      window.location.href = "register.html"
    }
  }

  updateUI() {
    if (!this.user) return

    // Update navigation based on role
    this.updateNavigation()

    // Update user info display
    this.updateUserInfo()
  }

  updateNavigation() {
    const nav = document.querySelector("nav ul")
    if (!nav) return

    // Clear existing navigation
    nav.innerHTML = ""

    if (this.user.role === "admin") {
      // Admin navigation
      nav.innerHTML = `
                <li><a href="index.html">Home</a></li>
                <li><a href="view_enrollments.html">View Enrollments</a></li>
                <li><a href="admin.html">Admin Panel</a></li>
                <li><a href="#" onclick="auth.logout()">Logout</a></li>
            `
    } else if (this.user.role === "student") {
      // Student navigation
      nav.innerHTML = `
                <li><a href="register.html">Register for Course</a></li>
                <li><a href="drop.html">Drop Course</a></li>
                <li><a href="#" onclick="auth.logout()">Logout</a></li>
            `
    }

    // Set active link
    this.setActiveNavLink()
  }

  setActiveNavLink() {
    const currentPage = window.location.pathname.split("/").pop()
    const navLinks = document.querySelectorAll("nav a")

    navLinks.forEach((link) => {
      link.classList.remove("active")
      if (link.getAttribute("href") === currentPage) {
        link.classList.add("active")
      }
    })
  }

  updateUserInfo() {
    // Do nothing or just remove the .user-info if it exists
    const header = document.querySelector("header")
    if (header) {
      const existingUserInfo = header.querySelector(".user-info")
      if (existingUserInfo) existingUserInfo.remove()
    }
  }

  getCurrentUser() {
    return this.user
  }

  isAdmin() {
    return this.hasRole("admin")
  }

  isStudent() {
    return this.hasRole("student")
  }
}

// Create global auth instance
const auth = new AuthManager()

// Add CSS for user info
const style = document.createElement("style")
style.textContent = `
    .welcome-popup {
  position: fixed;
  top: 2.5rem;
  right: 5.2rem;
  background: linear-gradient(90deg, #f8fafc 60%, #e3f0ff 100%);
  border-radius: 1.2rem;
  box-shadow: 0 4px 18px rgba(80,120,200,0.13), 0 1.5px 6px rgba(80,120,200,0.09);
  padding: 1.1rem 2.2rem 1.1rem 1.7rem;
  color: #222e3a;
  font-size: 1.08rem;
  font-family: 'Segoe UI', Arial, sans-serif;
  border: 1.5px solid #e0e6ed;
  z-index: 9999;
  min-width: 240px;
  animation: fadeInUp 0.5s;
}
.welcome-popup .role-badge {
  background: linear-gradient(90deg, #e0e7ff 60%, #c7f9cc 100%);
  color: #256029;
  display: inline-block;
  padding: 0.22em 1.1em;
  border-radius: 1em;
  font-size: 0.99em;
  font-weight: 600;
  letter-spacing: 0.04em;
  box-shadow: 0 1px 4px rgba(80,120,200,0.07);
  margin-top: 0.5em;
  border: 1px solid #b6e4d6;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px);}
  to { opacity: 1; transform: translateY(0);}
`
document.head.appendChild(style)

// Show welcome popup
function showWelcomePopup(user) {
  // Remove existing popup if any
  const existing = document.querySelector('.welcome-popup');
  if (existing) existing.remove();

  const popup = document.createElement('div');
  popup.className = 'welcome-popup';
  popup.innerHTML = `
    <div>
      <p>Welcome, <strong>${user.role === "admin" ? user.full_name || user.username : user.name}</strong></p>
      <span class="role-badge">Role: ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>
    </div>
  `;
  document.body.appendChild(popup);

  // Auto-hide after 3 seconds
  setTimeout(() => popup.remove(), 3000);
}

// Call this after successful login or page load
if (auth.user) showWelcomePopup(auth.user)
