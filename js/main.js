// Global initialization for the dashboard/home page
document.addEventListener('DOMContentLoaded', function() {
    const el = (id) => document.getElementById(id);

    // If dashboard elements exist, populate analytics
    if (el('kpiPackages')) {
        loadKPIs();
        renderCharts();
    }

    // Navbar interactions
    const navToggle = el('navToggle');
    const navMenu = el('navMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            const isOpen = navMenu.classList.toggle('show');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Dropdown toggles
        navMenu.querySelectorAll('.nav-item.dropdown > .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const parent = link.parentElement;
                const isShown = parent.classList.contains('show');
                // Close others
                navMenu.querySelectorAll('.nav-item.dropdown').forEach(item => item.classList.remove('show'));
                // Toggle this one
                if (!isShown) parent.classList.add('show');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && e.target !== navToggle) {
                navMenu.querySelectorAll('.nav-item.dropdown').forEach(item => item.classList.remove('show'));
            }
        });
    }

    function safeFetchJson(url) {
        return fetch(url)
            .then(async response => {
                const text = await response.text();
                try { return JSON.parse(text); } catch { return []; }
            })
            .catch(() => []);
    }

    async function loadKPIs() {
        const [packagesResp, batchesResp, marketsResp, storageResp] = await Promise.all([
            fetch('php/get_packages.php').then(r => r.json()).catch(() => ({ success: false, data: [] })),
            safeFetchJson('php/get_batches.php'),
            safeFetchJson('php/get_markets.php'),
            safeFetchJson('php/get_storage_facilities.php')
        ]);

        const packages = Array.isArray(packagesResp.data) ? packagesResp.data : (Array.isArray(packagesResp) ? packagesResp : []);
        const batches = Array.isArray(batchesResp) ? batchesResp : [];
        const markets = Array.isArray(marketsResp) ? marketsResp : [];
        const storage = Array.isArray(storageResp) ? storageResp : [];

        el('kpiPackages').textContent = String(packages.length);
        el('kpiBatches').textContent = String(batches.length);
        el('kpiMarkets').textContent = String(markets.length);
        el('kpiStorage').textContent = String(storage.length);
    }

    async function renderCharts() {
        const [packagesResp, harvestsResp, sensorResp, spoilageResp] = await Promise.all([
            fetch('php/get_packages.php').then(r => r.json()).catch(() => ({ success: false, data: [] })),
            safeFetchJson('php/get_harvest_records.php'),
            safeFetchJson('php/get_sensor_data.php'),
            safeFetchJson('php/get_spoilage_records.php')
        ]);

        const packages = Array.isArray(packagesResp.data) ? packagesResp.data : (Array.isArray(packagesResp) ? packagesResp : []);
        const harvests = Array.isArray(harvestsResp) ? harvestsResp : [];
        const sensor = Array.isArray(sensorResp) ? sensorResp : [];
        const spoilage = Array.isArray(spoilageResp) ? spoilageResp : [];

        // Packages by Type (bar)
        const packagesByType = packages.reduce((acc, p) => {
            const type = (p.type || 'Unknown');
            acc[type] = (acc[type] || 0) + 1;
            return acc;
        }, {});
        new Chart(el('chartPackagesByType'), {
            type: 'bar',
            data: {
                labels: Object.keys(packagesByType),
                datasets: [{
                    label: 'Count',
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    data: Object.values(packagesByType)
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // Harvest Quantity Over Time (line)
        const harvestPoints = harvests
            .map(h => ({ x: new Date(h.harvest_time || h.sowing_date || Date.now()), y: Number(h.quantity) || 0 }))
            .sort((a, b) => a.x - b.x);
        new Chart(el('chartHarvestOverTime'), {
            type: 'line',
            data: { datasets: [{ label: 'Quantity', borderColor: 'rgb(75, 192, 192)', data: harvestPoints }] },
            options: { responsive: true, parsing: false, scales: { x: { type: 'time', time: { unit: 'day' } } } }
        });

        // Sensor Temperature (Last 24h) (line)
        const now = Date.now();
        const tempPoints = sensor
            .filter(s => (new Date(s.reading_timestamp)).getTime() >= (now - 24*60*60*1000))
            .map(s => ({ x: new Date(s.reading_timestamp), y: Number(s.temperature) || Number(s.temp) || 0 }))
            .sort((a, b) => a.x - b.x);
        new Chart(el('chartTemperature'), {
            type: 'line',
            data: { datasets: [{ label: '°C', borderColor: 'rgb(255, 99, 132)', data: tempPoints }] },
            options: { responsive: true, parsing: false, scales: { x: { type: 'time', time: { unit: 'hour' } } } }
        });

        // Spoilage by Reason (pie)
        const spoilageByReason = spoilage.reduce((acc, s) => {
            const reason = (s.reason || 'Unknown');
            acc[reason] = (acc[reason] || 0) + 1;
            return acc;
        }, {});
        new Chart(el('chartSpoilageReason'), {
            type: 'pie',
            data: {
                labels: Object.keys(spoilageByReason),
                datasets: [{
                    label: 'Records',
                    data: Object.values(spoilageByReason),
                    backgroundColor: ['#60a5fa','#34d399','#f472b6','#fbbf24','#c084fc','#fca5a5']
                }]
            },
            options: { responsive: true }
        });
    }
});


