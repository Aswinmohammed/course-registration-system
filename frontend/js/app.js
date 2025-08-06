// Course Registration System - Main JavaScript File

// Global variables
let currentModal = null

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  initializeApp()
})

function initializeApp() {
  // Initialize modal functionality
  initializeModal()

  // Add global event listeners
  addGlobalEventListeners()

  // Initialize tooltips and other UI enhancements
  initializeUIEnhancements()
}

// Modal functionality
function initializeModal() {
  const modal = document.getElementById("messageModal")
  const closeBtn = document.querySelector(".close")

  if (closeBtn) {
    closeBtn.addEventListener("click", closeModal)
  }

  // Close modal when clicking outside of it
  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      closeModal()
    }
  })

  // Close modal with Escape key
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal && modal.style.display === "block") {
      closeModal()
    }
  })
}

function showMessage(message, type = "info") {
  const modal = document.getElementById("messageModal")
  const messageElement = document.getElementById("modalMessage")

  if (!modal || !messageElement) {
    // Fallback to alert if modal is not available
    alert(message)
    return
  }

  // Set message content
  messageElement.textContent = message

  // Add type-specific styling
  messageElement.className = `message-${type}`

  // Show modal
  modal.style.display = "block"
  currentModal = modal

  // Auto-close success messages after 3 seconds
  if (type === "success") {
    setTimeout(() => {
      closeModal()
    }, 3000)
  }
}

function closeModal() {
  const modal = document.getElementById("messageModal")
  if (modal) {
    modal.style.display = "none"
    currentModal = null
  }
}

// Global event listeners
function addGlobalEventListeners() {
  // Add loading states to forms
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const submitBtn = form.querySelector('button[type="submit"]')
      if (submitBtn) {
        const originalText = submitBtn.textContent
        submitBtn.textContent = "Processing..."
        submitBtn.disabled = true

        // Re-enable button after 5 seconds (fallback)
        setTimeout(() => {
          submitBtn.textContent = originalText
          submitBtn.disabled = false
        }, 5000)
      }
    })
  })

  // Add hover effects to cards
  const cards = document.querySelectorAll(".course-card, .stat-card")
  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)"
    })
  })
}

// UI Enhancements
function initializeUIEnhancements() {
  // Add loading spinners
  addLoadingSpinners()

  // Initialize tooltips
  initializeTooltips()

  // Add smooth scrolling
  addSmoothScrolling()
}

function addLoadingSpinners() {
  const style = document.createElement("style")
  style.textContent = `
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .message-success {
            color: #28a745;
            font-weight: bold;
        }
        
        .message-error {
            color: #dc3545;
            font-weight: bold;
        }
        
        .message-info {
            color: #17a2b8;
            font-weight: bold;
        }
    `
  document.head.appendChild(style)
}

function initializeTooltips() {
  // Add tooltips to elements with title attributes
  const elementsWithTitles = document.querySelectorAll("[title]")
  elementsWithTitles.forEach((element) => {
    element.addEventListener("mouseenter", showTooltip)
    element.addEventListener("mouseleave", hideTooltip)
  })
}

function showTooltip(event) {
  const element = event.target
  const title = element.getAttribute("title")

  if (!title) return

  // Create tooltip element
  const tooltip = document.createElement("div")
  tooltip.className = "tooltip"
  tooltip.textContent = title
  tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `

  document.body.appendChild(tooltip)

  // Position tooltip
  const rect = element.getBoundingClientRect()
  tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px"
  tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px"

  // Store reference for cleanup
  element._tooltip = tooltip

  // Remove title to prevent default tooltip
  element._originalTitle = title
  element.removeAttribute("title")
}

function hideTooltip(event) {
  const element = event.target

  if (element._tooltip) {
    document.body.removeChild(element._tooltip)
    element._tooltip = null
  }

  // Restore original title
  if (element._originalTitle) {
    element.setAttribute("title", element._originalTitle)
    element._originalTitle = null
  }
}

function addSmoothScrolling() {
  // Add smooth scrolling to anchor links
  const anchorLinks = document.querySelectorAll('a[href^="#"]')
  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()
      const targetId = this.getAttribute("href").substring(1)
      const targetElement = document.getElementById(targetId)

      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
}

// Utility functions
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

function formatNumber(number) {
  return new Intl.NumberFormat().format(number)
}

function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// API helper functions
async function apiRequest(url, options = {}) {
  try {
    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json",
        ...options.headers,
      },
      ...options,
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    return await response.json()
  } catch (error) {
    console.error("API request failed:", error)
    throw error
  }
}

// Form validation helpers
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function validateRequired(value) {
  return value !== null && value !== undefined && value.toString().trim() !== ""
}

function validateForm(formElement) {
  const requiredFields = formElement.querySelectorAll("[required]")
  let isValid = true

  requiredFields.forEach((field) => {
    if (!validateRequired(field.value)) {
      field.classList.add("error")
      isValid = false
    } else {
      field.classList.remove("error")
    }

    // Special validation for email fields
    if (field.type === "email" && field.value && !validateEmail(field.value)) {
      field.classList.add("error")
      isValid = false
    }
  })

  return isValid
}

// Local storage helpers
function saveToLocalStorage(key, data) {
  try {
    localStorage.setItem(key, JSON.stringify(data))
  } catch (error) {
    console.error("Failed to save to localStorage:", error)
  }
}

function loadFromLocalStorage(key) {
  try {
    const data = localStorage.getItem(key)
    return data ? JSON.parse(data) : null
  } catch (error) {
    console.error("Failed to load from localStorage:", error)
    return null
  }
}

// Export functions for use in other scripts
window.CourseRegistrationApp = {
  showMessage,
  closeModal,
  formatDate,
  formatNumber,
  debounce,
  apiRequest,
  validateForm,
  saveToLocalStorage,
  loadFromLocalStorage,
}
