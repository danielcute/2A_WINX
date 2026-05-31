<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php?route=signin");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Wardrobes - Sinta</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .wardrobe-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .wardrobe-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .wardrobe-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            color: #2C2820;
            margin-bottom: 10px;
        }

        .wardrobe-header p {
            color: #666;
            font-size: 1.1rem;
            margin: 5px 0;
        }

        .event-info {
            background: #f5f0e8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #8A7650;
        }

        .event-info h3 {
            margin: 0 0 10px 0;
            color: #8A7650;
            font-size: 1.1rem;
        }

        .event-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .event-detail {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .event-detail-label {
            font-weight: 600;
            color: #2C2820;
            min-width: 120px;
        }

        .event-detail-value {
            color: #666;
        }

        .controls-section {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }

        .category-filter {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 16px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #666;
        }

        .category-btn:hover,
        .category-btn.active {
            border-color: #8A7650;
            background: #8A7650;
            color: white;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #8A7650;
        }

        .wardrobes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .wardrobe-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .wardrobe-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .wardrobe-card.selected {
            border-color: #8A7650;
            background: #f5f0e8;
        }

        .wardrobe-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #8A7650 0%, #B8A78F 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .wardrobe-content {
            padding: 20px;
        }

        .wardrobe-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2C2820;
            margin-bottom: 8px;
        }

        .wardrobe-category {
            display: inline-block;
            background: #e8f4f8;
            color: #006085;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .wardrobe-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 12px;
            line-height: 1.4;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wardrobe-specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
        }

        .spec-item i {
            color: #8A7650;
            min-width: 15px;
        }

        .wardrobe-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #8A7650;
            margin-bottom: 12px;
        }

        .wardrobe-actions {
            display: flex;
            gap: 8px;
        }

        .btn-select {
            flex: 1;
            padding: 10px;
            background: #8A7650;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-select:hover {
            background: #6B5A3E;
        }

        .btn-select.selected {
            background: #6B5A3E;
        }

        .btn-remove {
            padding: 10px 15px;
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-remove:hover {
            background: #e0e0e0;
        }

        .selected-items {
            margin-top: 40px;
            padding: 25px;
            background: #f5f0e8;
            border-radius: 8px;
            border: 2px solid #8A7650;
        }

        .selected-items h3 {
            margin-top: 0;
            color: #8A7650;
            font-size: 1.3rem;
        }

        .selected-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .selected-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: white;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #8A7650;
        }

        .selected-item-info {
            flex: 1;
        }

        .selected-item-name {
            font-weight: 600;
            color: #2C2820;
            margin-bottom: 4px;
        }

        .selected-item-details {
            font-size: 0.9rem;
            color: #666;
        }

        .selected-item-price {
            font-weight: 700;
            color: #8A7650;
            font-size: 1.1rem;
            margin: 0 15px;
            min-width: 80px;
            text-align: right;
        }

        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            margin-top: 15px;
        }

        .total-label {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2C2820;
        }

        .total-price {
            font-weight: 700;
            font-size: 1.5rem;
            color: #8A7650;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #8A7650;
            color: white;
        }

        .btn-primary:hover {
            background: #6B5A3E;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .wardrobe-header h1 {
                font-size: 1.8rem;
            }

            .wardrobes-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }

            .controls-section {
                flex-direction: column;
            }

            .search-box {
                min-width: auto;
            }

            .event-details {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/layouts/navbar.php'; ?>

    <div class="wardrobe-container">
        <div class="wardrobe-header">
            <h1>Select Your <em>Wardrobe</em></h1>
            <p>Choose from our extensive collection of rental wardrobes</p>
        </div>

        <!-- Event Information -->
        <div class="event-info">
            <h3><i class="fas fa-calendar-alt"></i> Event Details</h3>
            <div class="event-details">
                <div class="event-detail">
                    <span class="event-detail-label">Event:</span>
                    <span class="event-detail-value"><?php echo htmlspecialchars($plan['event_name']); ?></span>
                </div>
                <div class="event-detail">
                    <span class="event-detail-label">Date:</span>
                    <span class="event-detail-value"><?php echo date('F d, Y', strtotime($plan['event_date'])); ?></span>
                </div>
                <div class="event-detail">
                    <span class="event-detail-label">Guests:</span>
                    <span class="event-detail-value"><?php echo $plan['guest_count']; ?> people</span>
                </div>
                <div class="event-detail">
                    <span class="event-detail-label">Venue:</span>
                    <span class="event-detail-value"><?php echo htmlspecialchars($plan['venue']); ?></span>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-section">
            <div class="category-filter" id="categoryFilter">
                <button class="category-btn active" data-category="">All Categories</button>
                <?php foreach ($categories as $category): ?>
                    <button class="category-btn" data-category="<?php echo htmlspecialchars($category); ?>">
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="search-box">
                <input type="text" id="searchBox" placeholder="Search wardrobes...">
            </div>
        </div>

        <!-- Wardrobes Grid -->
        <div id="wardrobesGrid" class="wardrobes-grid">
            <!-- Loaded via JavaScript -->
        </div>

        <!-- Selected Items -->
        <div class="selected-items">
            <h3><i class="fas fa-check-circle"></i> Your Selection</h3>
            <ul class="selected-list" id="selectedList">
                <li style="padding: 15px; text-align: center; color: #999;">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                    No wardrobes selected yet
                </li>
            </ul>
            <div class="total-section" id="totalSection" style="display: none;">
                <span class="total-label">Total Rental Cost:</span>
                <span class="total-price" id="totalPrice">₱0.00</span>
            </div>

            <div class="action-buttons">
                <a href="<?php echo APP_URL; ?>/plan-details?id=<?php echo $plan_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-primary" id="proceedBtn" onclick="proceedToCheckout()" style="display: none;">
                    <i class="fas fa-check"></i> Proceed to Checkout
                </button>
            </div>
        </div>
    </div>

    <?php include VIEW_PATH . '/layouts/footer.php'; ?>

    <script>
        const planId = <?php echo $plan_id; ?>;
        const apiUrl = '<?php echo APP_URL; ?>/api-wardrobe-selections.php';
        let selectedWardrobes = {};

        // Load initial wardrobes
        loadWardrobes();

        // Category filter
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loadWardrobes(this.dataset.category);
            });
        });

        // Search
        document.getElementById('searchBox').addEventListener('input', function() {
            const category = document.querySelector('.category-btn.active').dataset.category;
            loadWardrobes(category, this.value);
        });

        async function loadWardrobes(category = '', search = '') {
            try {
                const params = new URLSearchParams();
                if (category) params.append('category', category);
                if (search) params.append('search', search);

                const response = await fetch('<?php echo APP_URL; ?>/app/controllers/WardrobeSelectionController.php?action=getByCategory&' + params);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new TypeError("Expected JSON but received " + contentType);
                }
                const data = await response.json();
                if (data.success) { displayWardrobes(data.wardrobes); }
            } catch (error) {
                console.error('Error loading wardrobes:', error);
            }
        }

        function displayWardrobes(wardrobes) {
            const grid = document.getElementById('wardrobesGrid');

            if (wardrobes.length === 0) {
                grid.innerHTML = '<div class="empty-state" style="grid-column: 1/-1;"><div class="empty-state-icon"><i class="fas fa-inbox"></i></div><p>No wardrobes found</p></div>';
                return;
            }

            grid.innerHTML = wardrobes.map(w => `
                <div class="wardrobe-card ${selectedWardrobes[w.wardrobe_id] ? 'selected' : ''}">
                    <div class="wardrobe-image">
                        <i class="fas fa-${w.category.toLowerCase().includes('wedding') ? 'ring' : 'tuxedo'}"></i>
                    </div>
                    <div class="wardrobe-content">
                        <div class="wardrobe-name">${w.name}</div>
                        <span class="wardrobe-category">${w.category}</span>
                        <p class="wardrobe-description">${w.description || ''}</p>
                        <div class="wardrobe-specs">
                            <div class="spec-item">
                                <i class="fas fa-cube"></i>
                                <span>${w.availability_count} available</span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-calendar"></i>
                                <span>${w.rental_duration_days} day(s)</span>
                            </div>
                        </div>
                        <div style="font-size: 0.85rem; color: #666; margin-bottom: 12px;">
                            <i class="fas fa-ruler"></i> ${w.sizes_available}
                        </div>
                        <div class="wardrobe-price">₱${parseFloat(w.rental_price).toFixed(2)}</div>
                        <div class="wardrobe-actions">
                            <button class="btn-select ${selectedWardrobes[w.wardrobe_id] ? 'selected' : ''}" 
                                    onclick="toggleWardrobe(${w.wardrobe_id}, '${w.name}', ${w.rental_price})">
                                ${selectedWardrobes[w.wardrobe_id] ? '✓ Selected' : '+ Select'}
                            </button>
                            ${selectedWardrobes[w.wardrobe_id] ? `<button class="btn-remove" onclick="removeWardrobe(${w.wardrobe_id})"><i class="fas fa-trash"></i></button>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function toggleWardrobe(wardrobeId, name, price) {
            if (selectedWardrobes[wardrobeId]) {
                removeWardrobe(wardrobeId);
            } else {
                selectedWardrobes[wardrobeId] = {
                    name: name,
                    price: price,
                    quantity: 1
                };
                updateSelection();
            }
        }

        function removeWardrobe(wardrobeId) {
            delete selectedWardrobes[wardrobeId];
            updateSelection();
        }

        function updateSelection() {
            const list = document.getElementById('selectedList');
            const totalSection = document.getElementById('totalSection');
            const proceedBtn = document.getElementById('proceedBtn');

            if (Object.keys(selectedWardrobes).length === 0) {
                list.innerHTML = '<li style="padding: 15px; text-align: center; color: #999;"><i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>No wardrobes selected yet</li>';
                totalSection.style.display = 'none';
                proceedBtn.style.display = 'none';
            } else {
                let total = 0;
                list.innerHTML = Object.entries(selectedWardrobes).map(([id, wardrobe]) => {
                    total += wardrobe.price;
                    return `
                        <li class="selected-item">
                            <div class="selected-item-info">
                                <div class="selected-item-name">${wardrobe.name}</div>
                                <div class="selected-item-details">Quantity: ${wardrobe.quantity}</div>
                            </div>
                            <div class="selected-item-price">₱${wardrobe.price.toFixed(2)}</div>
                            <button class="btn-remove" onclick="removeWardrobe(${id})" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </li>
                    `;
                }).join('');

                document.getElementById('totalPrice').innerText = '₱' + total.toFixed(2);
                totalSection.style.display = 'flex';
                proceedBtn.style.display = 'inline-flex';
            }

            // Reload grid to update button states
            const category = document.querySelector('.category-btn.active').dataset.category;
            const search = document.getElementById('searchBox').value;
            loadWardrobes(category, search);
        }

        async function proceedToCheckout() {
            if (Object.keys(selectedWardrobes).length === 0) {
                alert('Please select at least one wardrobe');
                return;
            }

            try {
                // Save all selections
                for (const [wardrobeId, wardrobe] of Object.entries(selectedWardrobes)) {
                    const formData = new FormData();
                    formData.append('action', 'save');
                    formData.append('plan_id', planId);
                    formData.append('wardrobe_id', wardrobeId);
                    formData.append('quantity', wardrobe.quantity);
                    formData.append('subtotal', wardrobe.price);

                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    if (!data.success) {
                        alert('Error saving wardrobe selection');
                        return;
                    }
                }

                // Redirect to checkout
                window.location.href = '<?php echo APP_URL; ?>/checkout?plan_id=' + planId;
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }
    </script>
</body>
</html>
