/**
 * BookHaven Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popover
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Add fade-in animation to certain elements
    document.querySelectorAll('.fade-in-element').forEach(function(element) {
        element.classList.add('fade-in');
    });
    
    // Automatically close alerts after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });
    
    // Handle book search form submission
    const searchForm = document.querySelector('form[action="/book/search"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            const searchInput = this.querySelector('input[name="query"]');
            if (!searchInput.value.trim()) {
                event.preventDefault();
                searchInput.classList.add('is-invalid');
            }
        });
    }
    
    // Add to cart animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Only animate if not disabled
            if (!this.disabled) {
                this.innerHTML = '<i class="fas fa-check"></i> Added';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
                
                // Reset after 2 seconds
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-cart-plus me-1"></i> Add to Cart';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-primary');
                }, 2000);
            }
        });
    });
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('input[type="number"][name="quantity"]');
    quantityInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const min = parseInt(this.getAttribute('min')) || 1;
            const max = parseInt(this.getAttribute('max')) || 100;
            
            if (this.value < min) {
                this.value = min;
            } else if (this.value > max) {
                this.value = max;
            }
        });
    });
    
    // Image preview for book upload/edit form
    const imageInput = document.getElementById('image-upload');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.style.display = 'block';
                    imagePreview.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Star rating functionality for review form
    const ratingSelect = document.getElementById('rating');
    const ratingStars = document.querySelectorAll('.rating-star');
    
    if (ratingSelect && ratingStars.length > 0) {
        // Update stars based on select value
        ratingSelect.addEventListener('change', function() {
            const rating = parseInt(this.value);
            
            ratingStars.forEach(function(star, index) {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        });
        
        // Set rating when clicking on stars
        ratingStars.forEach(function(star, index) {
            star.addEventListener('click', function() {
                const rating = index + 1;
                ratingSelect.value = rating;
                
                // Trigger change event to update stars
                const event = new Event('change');
                ratingSelect.dispatchEvent(event);
            });
        });
    }
    
    // Back to top button functionality
    const backToTopButton = document.getElementById('back-to-top');
    
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Form validation for checkout
    const checkoutForm = document.getElementById('checkoutForm');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Get all required inputs
            const requiredInputs = this.querySelectorAll('[required]');
            
            requiredInputs.forEach(function(input) {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                
                // Scroll to first invalid input
                const firstInvalidInput = this.querySelector('.is-invalid');
                if (firstInvalidInput) {
                    firstInvalidInput.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstInvalidInput.focus();
                }
            }
        });
    }
});

/**
 * Format a number as currency
 * 
 * @param {number} amount Amount to format
 * @param {string} currencyCode Currency code (default: USD)
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount, currencyCode = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currencyCode
    }).format(amount);
}

/**
 * Show confirmation dialog
 * 
 * @param {string} message Message to display
 * @returns {boolean} True if confirmed, false otherwise
 */
function confirmAction(message) {
    return confirm(message);
}

/**
 * Validate email address format
 * 
 * @param {string} email Email to validate
 * @returns {boolean} True if valid, false otherwise
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}