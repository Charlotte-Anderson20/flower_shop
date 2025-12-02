<?php
session_start();
require 'includes/db.php';

// Only assign session variables if $customer is set and is an array
if (isset($customer) && is_array($customer)) {
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_image'] = $customer['customer_image'];
}

$customer_id = $_SESSION['customer_id'] ?? null;

// Get all categories for filter dropdown
$categories_result = $con->query("SELECT DISTINCT category FROM accessories ORDER BY category");
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories Collection</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary:rgb(240, 164, 194);
            --primary-dark:rgb(196, 44, 107);
            --secondary:rgb(201, 55, 126);
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 60px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1592078615290-033ee584e267?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            opacity: 0.15;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        /* Search and Filters */
        .filters-section {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            position: relative;
            top: -30px;
            z-index: 10;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            margin-bottom: 0;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray);
        }

        .filter-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        .filter-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Accessories Grid */
        .accessories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .accessory-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .accessory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .card-image {
            position: relative;
            padding-top: 100%; /* Square aspect ratio */
            overflow: hidden;
        }

        .card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .accessory-card:hover .card-image img {
            transform: scale(1.05);
        }

        .card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }

        .card-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .card-description {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
            flex: 1;
        }

        .card-category {
            display: inline-block;
            background-color: var(--light-gray);
            color: var(--gray);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .card-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }

        .card-stock {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
        }

        .stock-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .in-stock {
            background-color: var(--success);
        }

        .low-stock {
            background-color: var(--warning);
        }

        .out-of-stock {
            background-color: var(--danger);
        }

        .add-to-cart {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-to-cart:hover {
            background-color: var(--primary-dark);
            transform: scale(1.1);
        }

        /* No results */
        .no-results {
            text-align: center;
            padding: 60px 0;
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 3rem;
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--gray);
        }

        .toast {
    visibility: hidden;
    min-width: 250px;
    margin-left: -125px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: var(--border-radius);
    padding: 16px;
    position: fixed;
    z-index: 1000;
    left: 50%;
    bottom: 30px;
    font-size: 17px;
    opacity: 0;
    transition: opacity 0.5s, visibility 0.5s;
}

.toast.show {
    visibility: visible;
    opacity: 1;
}

.toast.success {
    background-color: var(--success);
}

.toast.error {
    background-color: var(--danger);
}

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 80px 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .accessories-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        /* Layout: sidebar + grid */
.content-layout {
    display: flex;
    gap: 30px;
    align-items: flex-start;
}

 .filters-section {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        width: 280px;
    }

    .filter-title {
        font-size: 1.5rem;
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
    }

    .filter-subtitle {
        font-size: 1rem;
        margin: 15px 0 10px;
        color: #555;
        font-weight: 500;
    }

    .filter-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border 0.3s;
    }

    .filter-input:focus {
        border-color: #8e44ad;
        outline: none;
    }

    .search-container {
        position: relative;
        margin-bottom: 15px;
    }

    .search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-option {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-size: 0.95rem;
        color: #555;
        position: relative;
        padding-left: 30px;
    }

    .filter-option input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .checkmark {
        position: absolute;
        left: 0;
        top: 2px;
        height: 18px;
        width: 18px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: white;
    }

    .filter-option:hover .checkmark {
        background-color: #f5f5f5;
    }

    .filter-option input:checked ~ .checkmark {
        background-color: #8e44ad;
        border-color: #8e44ad;
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .filter-option input:checked ~ .checkmark:after {
        display: block;
    }

    .filter-option .checkmark:after {
        left: 6px;
        top: 2px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .price-range {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .price-input {
        position: relative;
        flex: 1;
    }

    .price-input .currency {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #777;
        font-size: 0.8rem;
    }

    .price-separator {
        color: #777;
    }

    .range-slider {
        margin-top: 15px;
    }

    .slider {
        width: 100%;
        height: 5px;
        -webkit-appearance: none;
        background: #ddd;
        border-radius: 5px;
        outline: none;
        margin: 10px 0;
    }

    .slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        background: #8e44ad;
        border-radius: 50%;
        cursor: pointer;
    }

    .filter-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px;
        border-radius: 8px;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary {
        background-color: #8e44ad;
        color: white;
    }

    .btn-primary:hover {
        background-color: #7d3c98;
    }

    .btn-secondary {
        background-color: white;
        color: #8e44ad;
        border: 1px solid #8e44ad;
    }

    .btn-secondary:hover {
        background-color: #f9f0ff;
    }
/* Grid takes the remaining space */
.accessories-grid {
    flex: 1;
}

.card-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background-color: var(--accent);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    z-index: 1;
}

    </style>
</head>
<body>

<?php include 'header.php' ?>
    <!-- Hero Section -->
    <section class="hero animate__animated animate__fadeIn">
        <div class="hero-content">
            <h1>Premium Accessories Collection</h1>
            <p>Discover our carefully curated selection of high-quality accessories to complement your style</p>
        </div>
    </section>

   <div class="container content-layout">
    <aside class="filters-section">
        <h3 class="filter-title">Filter Products</h3>

        <!-- Search -->
        <div class="search-container">
            <input type="text" id="searchBox" placeholder="Search accessories..." class="filter-input">
            <i class="fas fa-search search-icon"></i>
        </div>

        <!-- Category Filter -->
        <div class="filter-group">
            <h4 class="filter-subtitle">Category</h4>
            <div class="checkbox-group">
                <?php foreach ($categories as $category): ?>
                <label class="filter-option">
                    <input type="checkbox" class="filter-category" value="<?= htmlspecialchars($category) ?>">
                    <span class="checkmark"></span>
                    <?= htmlspecialchars($category) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Price Filter -->
        <div class="filter-group">
            <h4 class="filter-subtitle">Price Range</h4>
            <div class="price-range">
                <div class="price-input">
                    <input type="number" id="minPrice" placeholder="Min" class="filter-input">
                    <span class="currency">ks</span>
                </div>
                <div class="price-separator">-</div>
                <div class="price-input">
                    <input type="number" id="maxPrice" placeholder="Max" class="filter-input">
                    <span class="currency">ks</span>
                </div>
            </div>
            <div class="range-slider">
                <input type="range" min="0" max="100000" value="0" class="slider" id="minRange">
                <input type="range" min="0" max="100000" value="100000" class="slider" id="maxRange">
            </div>
        </div>

       

        <div class="filter-actions">
            <button id="resetFilters" class="btn btn-secondary">Reset All</button>
        </div>
    </aside>

    <section class="accessories-grid" id="accessoriesGrid">
        <!-- Content will be loaded dynamically via JavaScript -->
    </section>
</div>

<?php include 'includes/footer.php' ?>
    
<div id="toast" class="toast"></div>

<script>
// Utility function for debouncing
function debounce(func, wait, immediate) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Main function to load accessories with filters
async function loadAccessories() {
    const search = document.getElementById('searchBox').value;
    const categories = [...document.querySelectorAll('.filter-category:checked')].map(c => c.value);
    const stocks = [...document.querySelectorAll('.filter-stock:checked')].map(s => s.value);
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;

    try {
        const response = await fetch('filter_accessories.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                search: search,
                categories: JSON.stringify(categories),
                stocks: JSON.stringify(stocks),
                minPrice: minPrice,
                maxPrice: maxPrice
            })
        });
        
        const html = await response.text();
        document.getElementById('accessoriesGrid').innerHTML = html;
        
        // Reinitialize add-to-cart buttons
        initializeAddToCartButtons();
    } catch (error) {
        console.error('Error loading accessories:', error);
        showToast('Failed to load accessories', 'error');
    }
}

// Initialize add to cart buttons
function initializeAddToCartButtons() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async function() {
            const button = this;
            const accessory = {
                id: button.dataset.id,
                name: button.dataset.name,
                price: button.dataset.price,
                image: button.dataset.image,
                type: 'accessory'
            };

            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;

            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(accessory)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Update cart count
                    document.querySelectorAll('.cart-count').forEach(el => {
                        el.textContent = data.cart_count;
                        el.classList.add('pulse');
                        setTimeout(() => el.classList.remove('pulse'), 500);
                    });

                    // Button feedback
                    button.classList.add('added');
                    button.innerHTML = '<i class="fas fa-check"></i> Added!';

                    // Show toast
                    showToast(`${accessory.name} added to cart!`, 'success');

                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('added');
                        button.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to add to cart');
                }
            } catch (err) {
                console.error('Error:', err);
                button.innerHTML = originalHTML;
                button.disabled = false;
                showToast(err.message, 'error');
            }
        });
    });
}

// Toast notification function
function showToast(message, type) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast show ' + type;
    
    setTimeout(() => {
        toast.className = toast.className.replace('show', '');
    }, 3000);
}

// Reset all filters
function resetFilters() {
    document.getElementById('searchBox').value = '';
    document.querySelectorAll('.filter-category:checked, .filter-stock:checked').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('minRange').value = 0;
    document.getElementById('maxRange').value = 100000;
    loadAccessories();
}

// Sync range sliders with price inputs
function setupPriceRangeSync() {
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const minRange = document.getElementById('minRange');
    const maxRange = document.getElementById('maxRange');

    minRange.addEventListener('input', function() {
        minPriceInput.value = this.value;
        loadAccessories();
    });

    maxRange.addEventListener('input', function() {
        maxPriceInput.value = this.value;
        loadAccessories();
    });

    minPriceInput.addEventListener('input', debounce(function() {
        minRange.value = this.value || 0;
        loadAccessories();
    }, 500));

    maxPriceInput.addEventListener('input', debounce(function() {
        maxRange.value = this.value || 100000;
        loadAccessories();
    }, 500));
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for dynamic filtering
    document.getElementById('searchBox').addEventListener('input', debounce(loadAccessories, 300));
    document.querySelectorAll('.filter-category, .filter-stock').forEach(checkbox => {
        checkbox.addEventListener('change', loadAccessories);
    });
    setupPriceRangeSync();
    document.getElementById('resetFilters').addEventListener('click', resetFilters);

    // Initial load
    loadAccessories();
});
</script>
</body>
</html>