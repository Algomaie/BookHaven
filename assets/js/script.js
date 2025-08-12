/**
 * bookhaven - Main JavaScript File
 * This file contains common functionality used across the website.
 */

$(document).ready(function() {
    
    // Add to cart functionality
    $('.add-to-cart').on('click', function(e) {
        e.preventDefault();
        
        const bookId = $(this).data('book-id');
        const quantity = $('#quantity').length ? $('#quantity').val() : 1;
        
        $.ajax({
            url: '/ajax/add-to-cart.php',
            type: 'POST',
            data: {
                book_id: bookId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#cart-count').text(response.cart_count);
                    showToast('Success!', response.message, 'success');
                    $('#cart-button').addClass('animate__animated animate__headShake');
                    setTimeout(function() {
                        $('#cart-button').removeClass('animate__animated animate__headShake');
                    }, 1000);
                } else {
                    showToast('Error!', response.message, 'error');
                }
            },
            error: function() {
                showToast('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
    
    $('.update-quantity').on('change', function() {
        const cartItemId = $(this).data('item-id');
        const newQuantity = $(this).val();
        const row = $(this).closest('tr');
        
        $.ajax({
            url: '/ajax/update-cart.php',
            type: 'POST',
            data: {
                cart_item_id: cartItemId,
                quantity: newQuantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.find('.item-subtotal').text('$' + response.item_subtotal);
                    $('#cart-total').text('$' + response.cart_total);
                    showToast('Cart Updated', response.message, 'success');
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function() {
                showToast('Error', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
    
    $('.remove-item').on('click', function(e) {
        e.preventDefault();
        
        const cartItemId = $(this).data('item-id');
        const row = $(this).closest('tr');
        
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            $.ajax({
                url: '/ajax/remove-cart-item.php',
                type: 'POST',
                data: {
                    cart_item_id: cartItemId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        row.fadeOut('slow', function() {
                            $(this).remove();
                            $('#cart-count').text(response.cart_count);
                            $('#cart-total').text('$' + response.cart_total);
                            
                            if (response.cart_count == 0) {
                                $('#cart-table').hide();
                                $('#cart-summary').hide();
                                $('#empty-cart-message').removeClass('d-none').addClass('d-block');
                            }
                            
                            showToast('Item Removed', response.message, 'success');
                        });
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function() {
                    showToast('Error', 'Something went wrong. Please try again.', 'error');
                }
            });
        }
    });
    
    $('.rate-book').on('click', function() {
        const bookId = $(this).data('book-id');
        const rating = $(this).data('rating');
        
        $.ajax({
            url: '/ajax/rate-book.php',
            type: 'POST',
            data: {
                book_id: bookId,
                rating: rating
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.book-rating').text(response.new_rating);
                    updateStars(response.new_rating);
                    showToast('Rating Submitted', response.message, 'success');
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function() {
                showToast('Error', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
    
    $('#review-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '/ajax/submit-review.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#review-form')[0].reset();
                    $('#reviews-container').prepend(response.review_html);
                    showToast('Review Submitted', response.message, 'success');
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function() {
                showToast('Error', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
    
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '/ajax/filter-books.php',
            type: 'GET',
            data: formData,
            success: function(response) {
                $('#books-container').html(response);
            },
            error: function() {
                showToast('Error', 'Something went wrong with filtering. Please try again.', 'error');
            }
        });
    });
    
    $('.toggle-password').on('click', function() {
        const passwordField = $($(this).data('toggle'));
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
    
    function showToast(title, message, type) {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3000
            };
            
            switch(type) {
                case 'success':
                    toastr.success(message, title);
                    break;
                case 'error':
                    toastr.error(message, title);
                    break;
                case 'warning':
                    toastr.warning(message, title);
                    break;
                case 'info':
                    toastr.info(message, title);
                    break;
                default:
                    toastr.info(message, title);
            }
        } else {
            alert(title + ': ' + message);
        }
    }
    
    function updateStars(rating) {
        const starsContainer = $('.stars-container');
        starsContainer.empty();
        
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        
        for (let i = 0; i < fullStars; i++) {
            starsContainer.append('<i class="fas fa-star text-warning"></i>');
        }
        
        if (hasHalfStar) {
            starsContainer.append('<i class="fas fa-star-half-alt text-warning"></i>');
        }
        
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        for (let i = 0; i < emptyStars; i++) {
            starsContainer.append('<i class="far fa-star text-warning"></i>');
        }
    }
});