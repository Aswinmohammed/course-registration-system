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
    // Update header with user info
    const header = document.querySelector("header")
    if (header && this.user) {
      const userInfo = document.createElement("div")
      userInfo.className = "user-info"
      userInfo.innerHTML = `
                <p>Welcome, ${this.user.role === "admin" ? this.user.full_name || this.user.username : this.user.name}</p>
                <p>Role: ${this.user.role.charAt(0).toUpperCase() + this.user.role.slice(1)}</p>
            `

      // Remove existing user info
      const existingUserInfo = header.querySelector(".user-info")
      if (existingUserInfo) {
        existingUserInfo.remove()
      }

      header.appendChild(userInfo)
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
    .user-info {
        position: absolute;
        top: 1rem;
        right: 2rem;
        text-align: right;
        color: white;
        font-size: 0.9rem;
    }
    
    .user-info p {
        margin: 0.25rem 0;
        opacity: 0.9;
    }
    
    header {
        position: relative;
    }
    
    @media (max-width: 768px) {
        .user-info {
            position: static;
            text-align: center;
            margin-top: 1rem;
        }
    }
`
document.head.appendChild(style)
