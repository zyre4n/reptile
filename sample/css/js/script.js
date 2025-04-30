document.addEventListener("DOMContentLoaded", () => {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById("sidebarToggle")
    const sidebar = document.getElementById("sidebar")
    const contentWrapper = document.querySelector(".content-wrapper")
  
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("show")
        contentWrapper.classList.toggle("sidebar-open")
      })
    }
  
    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", (event) => {
      const isClickInsideSidebar = sidebar && sidebar.contains(event.target)
      const isClickOnToggler = sidebarToggle && sidebarToggle.contains(event.target)
  
      if (
        window.innerWidth <= 768 &&
        !isClickInsideSidebar &&
        !isClickOnToggler &&
        sidebar &&
        sidebar.classList.contains("show")
      ) {
        sidebar.classList.remove("show")
        contentWrapper.classList.remove("sidebar-open")
      }
    })
  
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll(".alert:not(.alert-permanent)")
    alerts.forEach((alert) => {
      setTimeout(() => {
        // Ensure bootstrap is available before using it
        if (typeof bootstrap !== "undefined") {
          const bsAlert = new bootstrap.Alert(alert)
          bsAlert.close()
        } else {
          console.error("Bootstrap is not defined. Ensure it is properly loaded.")
          // Optionally, remove the alert manually if bootstrap is not available
          alert.remove()
        }
      }, 5000)
    })
  
    // Enable tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
  
    // Enable popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))
  })
  