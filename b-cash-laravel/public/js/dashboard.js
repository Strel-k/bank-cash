// Fix for the WS(element) function in app.js
// Add defensive checks for dataset properties
if (typeof window.WS === 'function') {
    const originalWS = window.WS;
    window.WS = function(element) {
        if (!element || !element.dataset) {
            console.warn('Invalid element passed to WS function');
            return null;
        }
        return originalWS(element);
    };
}

// Make sure dataset properties exist before accessing them
document.addEventListener('click', function(event) {
    const element = event.target;
    if (element && element.dataset && typeof element.dataset.action !== 'undefined') {
        // Handle the action
        handleDataAction(element.dataset.action, element);
    }
});

function handleDataAction(action, element) {
    switch (action) {
        case 'open-modal':
            if (element.dataset.target) {
                openModal(element.dataset.target);
            }
            break;
        case 'close-modal':
            if (element.dataset.target) {
                closeModal(element.dataset.target);
            }
            break;
        // Add other action handlers as needed
    }
}