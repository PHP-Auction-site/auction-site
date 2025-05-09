// Client-side validation and functionality for Auction Site

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Countdown timer for auctions
    const countdownElements = document.querySelectorAll('.countdown');
    countdownElements.forEach(element => {
        const endTime = new Date(element.dataset.endTime).getTime();
        
        const updateCountdown = () => {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                element.innerHTML = 'Auction Ended';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            element.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        };
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });

    // Image preview for item creation
    const imageInput = document.querySelector('#item-image');
    const imagePreview = document.querySelector('#image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Bid amount validation
    const bidForm = document.querySelector('#bid-form');
    const currentPrice = document.querySelector('#current-price');
    const bidAmount = document.querySelector('#bid-amount');
    
    if (bidForm && currentPrice && bidAmount) {
        const minBid = parseFloat(currentPrice.dataset.value) + 1;
        bidAmount.min = minBid;
        
        bidForm.addEventListener('submit', function(event) {
            const amount = parseFloat(bidAmount.value);
            if (amount < minBid) {
                event.preventDefault();
                alert(`Minimum bid amount is ${minBid}`);
            }
        });
    }

    // Category filter
    const categoryButtons = document.querySelectorAll('.category-filter button');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            window.location.href = `index.php?category=${categoryId}`;
        });
    });
});

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format dates to local timezone
function formatDate(dateString) {
    return new Date(dateString).toLocaleString();
}

// Countdown timer for auctions
function updateCountdown(endTime, elementId) {
    const end = new Date(endTime).getTime();
    
    const timer = setInterval(() => {
        const now = new Date().getTime();
        const distance = end - now;

        if (distance < 0) {
            clearInterval(timer);
            document.getElementById(elementId).innerHTML = 'Auction Ended';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById(elementId).innerHTML = 
            `${days}d ${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
}

// Handle bid form submission
document.querySelectorAll('.bid-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update UI with new bid
                const itemId = form.dataset.itemId;
                document.querySelector(`#current-price-${itemId}`).textContent = 
                    formatCurrency(result.new_price);
                form.reset();
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success mt-2';
                alert.textContent = result.message;
                form.appendChild(alert);
                setTimeout(() => alert.remove(), 3000);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger mt-2';
            alert.textContent = error.message;
            form.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        }
    });
});

// Image preview for item creation/edit
const imageInput = document.querySelector('#item-image');
if (imageInput) {
    imageInput.addEventListener('change', (e) => {
        const preview = document.querySelector('#image-preview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

// Initialize tooltips
const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

// Handle flash messages auto-hide
document.querySelectorAll('.alert').forEach(alert => {
    if (!alert.classList.contains('alert-permanent')) {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    }
});

// Handle responsive navigation
document.addEventListener('DOMContentLoaded', () => {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', () => {
            navbarCollapse.classList.toggle('show');
        });
    }
}); 