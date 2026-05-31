<?php
/**
 * Admin Database Health Widget
 * Shows database connectivity status for mobile and desktop
 */
?>

<style>
    .db-health-widget {
        margin: 20px 0;
        padding: 15px 20px;
        background: white;
        border-radius: 8px;
        border-left: 4px solid #8A7650;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .db-health-widget.loading {
        background: #f5f5f5;
        border-left-color: #ccc;
    }

    .db-health-widget.online {
        border-left-color: #4CAF50;
        background: #f1f8f4;
    }

    .db-health-widget.offline {
        border-left-color: #f44336;
        background: #fef1f0;
    }

    .db-health-widget.degraded {
        border-left-color: #ff9800;
        background: #fff8f3;
    }

    .db-health-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .db-health-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .db-health-status .status-badge {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .db-health-widget.online .status-badge {
        background: #4CAF50;
    }

    .db-health-widget.offline .status-badge {
        background: #f44336;
    }

    .db-health-widget.degraded .status-badge {
        background: #ff9800;
    }

    .db-health-widget.loading .status-badge {
        background: #999;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(76, 175, 80, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
        }
    }

    .db-health-widget.offline .status-badge,
    .db-health-widget.degraded .status-badge {
        animation: none;
    }

    .db-health-detail {
        font-size: 0.85rem;
        color: #666;
    }

    .db-health-tables {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(0,0,0,0.1);
    }

    .db-health-tables h4 {
        margin: 0 0 8px 0;
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .db-health-table-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 8px;
    }

    .db-health-table-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px;
        background: rgba(255,255,255,0.5);
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .db-health-table-item .icon {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 0.65rem;
        font-weight: bold;
        color: white;
    }

    .db-health-table-item.healthy .icon {
        background: #4CAF50;
    }

    .db-health-table-item.error .icon {
        background: #f44336;
    }

    .db-health-refresh {
        margin-top: 12px;
        font-size: 0.8rem;
        color: #999;
    }

    .db-health-refresh-btn {
        background: none;
        border: none;
        color: #8A7650;
        cursor: pointer;
        font-weight: 600;
        padding: 0;
        text-decoration: underline;
    }

    .db-health-refresh-btn:hover {
        color: #6B5A3E;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .db-health-widget {
            margin: 15px 0;
            padding: 12px 15px;
        }

        .db-health-table-list {
            grid-template-columns: 1fr;
        }

        .db-health-header {
            gap: 8px;
        }

        .db-health-status {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 480px) {
        .db-health-widget {
            padding: 10px 12px;
        }

        .db-health-status {
            font-size: 0.85rem;
        }

        .db-health-detail {
            font-size: 0.8rem;
        }

        .db-health-tables h4 {
            font-size: 0.8rem;
        }

        .db-health-table-item {
            font-size: 0.8rem;
            padding: 6px;
        }
    }
</style>

<div id="dbHealthWidget" class="db-health-widget loading">
    <div class="db-health-header">
        <div class="db-health-status">
            <span class="status-badge"></span>
            <span id="dbHealthStatus">Checking database...</span>
        </div>
        <div class="db-health-detail" id="dbHealthDetail"></div>
    </div>
    <div id="dbHealthDetails" style="display: none;"></div>
    <div class="db-health-refresh">
        Last check: <span id="dbLastCheck">--:--</span>
        <button class="db-health-refresh-btn" onclick="refreshDbHealth()">Refresh</button>
    </div>
</div>

<script>
    // Cache for health check results
    let dbHealthCache = {
        data: null,
        timestamp: null,
        cacheInterval: 60000 // 60 seconds
    };

    function refreshDbHealth() {
        // Force refresh by clearing cache
        dbHealthCache.timestamp = null;
        checkDbHealth();
    }

    function checkDbHealth() {
        // Check cache
        if (dbHealthCache.timestamp && (Date.now() - dbHealthCache.timestamp) < dbHealthCache.cacheInterval) {
            displayDbHealth(dbHealthCache.data);
            return;
        }

        const widget = document.getElementById('dbHealthWidget');
        widget.classList.remove('online', 'offline', 'degraded');
        widget.classList.add('loading');

        fetch('<?php echo BASE_URL ?? '/'; ?>/api-db-health-check.php')
            .then(response => response.json())
            .then(data => {
                dbHealthCache.data = data;
                dbHealthCache.timestamp = Date.now();
                displayDbHealth(data);
            })
            .catch(error => {
                console.error('Health check error:', error);
                widget.classList.remove('loading');
                widget.classList.add('offline');
                document.getElementById('dbHealthStatus').textContent = 'Database Offline';
                document.getElementById('dbHealthDetail').textContent = 'Unable to connect';
                document.getElementById('dbLastCheck').textContent = new Date().toLocaleTimeString();
            });
    }

    function displayDbHealth(data) {
        const widget = document.getElementById('dbHealthWidget');
        const statusEl = document.getElementById('dbHealthStatus');
        const detailEl = document.getElementById('dbHealthDetail');
        const detailsEl = document.getElementById('dbHealthDetails');

        widget.classList.remove('loading');

        if (!data.success) {
            widget.classList.add('offline');
            statusEl.textContent = 'Database Offline';
            detailEl.textContent = data.message || 'Connection failed';
            detailsEl.style.display = 'none';
        } else {
            widget.classList.add(data.status);
            statusEl.textContent = data.status === 'online' ? 'Database Online' : 'Database Degraded';
            
            const stats = data.statistics || {};
            detailEl.innerHTML = `<strong>${stats.total_records || 0}</strong> records • <strong>${stats.total_size_mb || 0}</strong> MB`;

            if (data.tables) {
                let tablesHtml = '<div class="db-health-tables"><h4>Table Status</h4><div class="db-health-table-list">';
                
                Object.entries(data.tables).forEach(([tableName, tableData]) => {
                    const status = tableData.accessible ? 'healthy' : 'error';
                    const icon = tableData.accessible ? '✓' : '✕';
                    tablesHtml += `<div class="db-health-table-item ${status}">
                        <div class="icon">${icon}</div>
                        <div>
                            <strong style="display: block;">${tableData.name}</strong>
                            <small>${tableData.records || 0} records</small>
                        </div>
                    </div>`;
                });
                
                tablesHtml += '</div></div>';
                detailsEl.innerHTML = tablesHtml;
                detailsEl.style.display = 'block';
            }
        }

        document.getElementById('dbLastCheck').textContent = new Date().toLocaleTimeString();
    }

    // Check on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkDbHealth();
        // Auto-refresh every 60 seconds
        setInterval(checkDbHealth, 60000);
    });
</script>
