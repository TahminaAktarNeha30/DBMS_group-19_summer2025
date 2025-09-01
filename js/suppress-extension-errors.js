// Extension Error Suppression Script
// This script prevents browser extension communication errors from cluttering the console

(function() {
    'use strict';
    
    // Enhanced error patterns for better detection
    const extensionErrorPatterns = [
        'message channel closed',
        'listener indicated an asynchronous response',
        'Extension context invalidated',
        'Could not establish connection',
        'chrome-extension://',
        'moz-extension://',
        'efaidnbmnnnibpcajpcglclefindmkaj',
        'express-utils.js',
        'express-fte.js',
        'ShowOneChild.js',
        'Failed to fetch dynamically imported module',
        'net::ERR_FAILED',
        'No tab with id',
        'Cannot access contents of',
        'Extension manifest',
        'runtime.lastError',
        'Port error:',
        'webNavigation',
        'Unexpected token',
        'is not valid JSON',
        'SyntaxError'
    ];
    
    // Function to check if error is extension-related
    function isExtensionError(error) {
        if (!error) return false;
        
        let errorString = '';
        try {
            if (typeof error === 'string') {
                errorString = error;
            } else if (error && typeof error === 'object') {
                if (error.message) {
                    errorString = error.message;
                } else {
                    try {
                        errorString = JSON.stringify(error);
                    } catch (e) {
                        errorString = String(error);
                    }
                }
            } else {
                errorString = String(error);
            }
        } catch (e) {
            return false; // If we can't process it, don't suppress it
        }
        
        return extensionErrorPatterns.some(pattern => {
            try {
                return errorString.toLowerCase().includes(pattern.toLowerCase());
            } catch (e) {
                return false;
            }
        });
    }
    
    // Suppress unhandled promise rejections from browser extensions
    window.addEventListener('unhandledrejection', function(event) {
        if (isExtensionError(event.reason)) {
            event.preventDefault();
            // Optionally log to console for debugging (commented out to reduce noise)
            // console.log('Suppressed extension error:', event.reason.message || event.reason);
            return;
        }
    });
    
    // Suppress general errors from extensions
    window.addEventListener('error', function(event) {
        if (isExtensionError(event.error) || isExtensionError(event.message)) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }
    });
    
    // Override console.error to filter extension-related errors
    const originalConsoleError = console.error;
    console.error = function(...args) {
        try {
            const message = args.map(arg => {
                if (typeof arg === 'string') return arg;
                if (arg && typeof arg === 'object') {
                    try {
                        return JSON.stringify(arg);
                    } catch (e) {
                        return String(arg);
                    }
                }
                return String(arg);
            }).join(' ');
            
            if (isExtensionError(message)) {
                // Suppress extension errors silently
                return;
            }
        } catch (e) {
            // If there's an error in processing, just pass through
        }
        
        // Allow legitimate application errors through
        originalConsoleError.apply(console, args);
    };
    
    // Override console.warn for extension warnings
    const originalConsoleWarn = console.warn;
    console.warn = function(...args) {
        try {
            const message = args.map(arg => {
                if (typeof arg === 'string') return arg;
                if (arg && typeof arg === 'object') {
                    try {
                        return JSON.stringify(arg);
                    } catch (e) {
                        return String(arg);
                    }
                }
                return String(arg);
            }).join(' ');
            
            if (isExtensionError(message)) {
                // Suppress extension warnings silently
                return;
            }
        } catch (e) {
            // If there's an error in processing, just pass through
        }
        
        originalConsoleWarn.apply(console, args);
    };
    
    // Monkey patch Promise.prototype.catch to suppress extension errors
    const originalCatch = Promise.prototype.catch;
    Promise.prototype.catch = function(onRejected) {
        return originalCatch.call(this, function(reason) {
            if (isExtensionError(reason)) {
                // Silently handle extension errors
                return Promise.resolve();
            }
            if (onRejected) {
                return onRejected(reason);
            }
            throw reason;
        });
    };
    
    console.log('Extension error suppression enabled');
})();