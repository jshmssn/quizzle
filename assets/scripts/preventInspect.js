// Disable right-click context menu
document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
});

// Disable developer tools shortcuts
document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey && e.shiftKey && e.keyCode == 73) || // Prevent Ctrl+Shift+I
        (e.ctrlKey && e.shiftKey && e.keyCode == 74) || // Prevent Ctrl+Shift+J
        (e.ctrlKey && e.keyCode == 85) ||              // Prevent Ctrl+U
        (e.keyCode == 123)) {                          // Prevent F12
        e.preventDefault();
        return false;
    }
});