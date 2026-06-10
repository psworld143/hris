// Dark Mode JavaScript Enhancements

document.addEventListener('DOMContentLoaded', function() {
    // Check for system preference
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Initialize dark mode based on localStorage or system preference
    function initializeDarkMode() {
        const savedMode = localStorage.getItem('darkMode');
        const isDark = savedMode !== null ? savedMode === 'true' : prefersDark.matches;
        
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Update Alpine.js data
        if (window.Alpine) {
            Alpine.store('darkMode', isDark);
        }
    }
    
    // Toggle dark mode
    function toggleDarkMode() {
        const isDark = document.documentElement.classList.contains('dark');
        const newMode = !isDark;
        
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', newMode.toString());
        
        // Update Alpine.js data
        if (window.Alpine) {
            Alpine.store('darkMode', newMode);
        }
        
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('darkModeChanged', { detail: { isDark: newMode } }));
    }
    
    // Listen for system preference changes
    prefersDark.addEventListener('change', function(e) {
        if (localStorage.getItem('darkMode') === null) {
            if (e.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });
    
    // Initialize on page load
    initializeDarkMode();
    
    // Add toggle functionality to buttons with data-dark-toggle attribute
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-dark-toggle]')) {
            e.preventDefault();
            toggleDarkMode();
        }
    });
    
    // Add keyboard shortcut (Ctrl/Cmd + D)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            toggleDarkMode();
        }
    });
    
    // Add smooth transitions for page elements
    function addSmoothTransitions() {
        const elements = document.querySelectorAll('*');
        elements.forEach(element => {
            element.style.transition = 'background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease';
        });
    }
    
    // Add transitions after page load
    setTimeout(addSmoothTransitions, 100);
    
    // Update meta theme color for mobile browsers
    function updateThemeColor() {
        const isDark = document.documentElement.classList.contains('dark');
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', isDark ? '#0f172a' : '#ffffff');
        }
    }
    
    // Update theme color on dark mode change
    window.addEventListener('darkModeChanged', updateThemeColor);
    updateThemeColor();
    
    // Add loading animation for dark mode toggle
    function addToggleAnimation() {
        const toggles = document.querySelectorAll('[data-dark-toggle]');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
    }
    
    addToggleAnimation();
});

// Utility functions for dark mode
window.DarkMode = {
    // Check if dark mode is active
    isDark: function() {
        return document.documentElement.classList.contains('dark');
    },
    
    // Toggle dark mode
    toggle: function() {
        const isDark = this.isDark();
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', (!isDark).toString());
        window.dispatchEvent(new CustomEvent('darkModeChanged', { detail: { isDark: !isDark } }));
    },
    
    // Set dark mode
    setDark: function(enabled) {
        if (enabled) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        localStorage.setItem('darkMode', enabled.toString());
        window.dispatchEvent(new CustomEvent('darkModeChanged', { detail: { isDark: enabled } }));
    },
    
    // Get current mode
    getMode: function() {
        return this.isDark() ? 'dark' : 'light';
    }
}; 