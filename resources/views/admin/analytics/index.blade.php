@extends('admin.layouts.app')

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid" id="gaDashboardRoot"
                data-endpoint="{{ route('admin.analytics.data') }}"
                data-connect-url="{{ $connectUrl ?? '' }}"
                data-default-preset="{{ $defaultPreset ?? '30' }}">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="dashboard-header-title">
                                <h5 class="mb-0">Google Analytics (GA4)</h5>
                                <p class="mb-0 text-muted">Analytics dashboard with date range filters</p>
                            </div>

                            <div class="d-flex flex-wrap align-items-end gap-2">
                                <div>
                                    <label class="form-label mb-1">Date Range</label>
                                    <select id="gaPreset" class="form-select form-select-sm" style="min-width: 170px;">
                                        <option value="7">Last 7 Days</option>
                                        <option value="30" selected>Last 30 Days</option>
                                        <option value="90">Last 90 Days</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>

                                <div id="gaCustomRange" class="d-none">
                                    <label class="form-label mb-1">Custom</label>
                                    <div class="d-flex gap-2">
                                        <input id="gaStartDate" type="date" class="form-control form-control-sm" />
                                        <input id="gaEndDate" type="date" class="form-control form-control-sm" />
                                    </div>
                                </div>

                                <button id="gaApply" class="ct_custom_btn1">Apply</button>
                                <a href="{{ $connectUrl ?? '#' }}" class=" ga-btn-outline ct_custom_btn1">Connect GA4</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div id="gaAlert" class="alert alert-danger d-none mb-0"></div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted">New Users</div>
                                        <div id="gaNewUsers" class="h3 mb-0 placeholder-glow"><span class="placeholder col-6"></span></div>
                                    </div>
                                    <div style="color: #f16919;"><i class='bx bx-user fs-2'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted">Total/Active Users</div>
                                        <div id="gaActiveUsers" class="h3 mb-0 placeholder-glow"><span class="placeholder col-6"></span></div>
                                    </div>
                                    <div class="text-success"><i class='bx bx-pulse fs-2'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted">Sessions</div>
                                        <div id="gaSessions" class="h3 mb-0 placeholder-glow"><span class="placeholder col-6"></span></div>
                                    </div>
                                    <div class="text-warning"><i class='bx bx-timer fs-2'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted">Page Views</div>
                                        <div id="gaPageViews" class="h3 mb-0 placeholder-glow"><span class="placeholder col-6"></span></div>
                                    </div>
                                    <div class="text-info"><i class='bx bx-bar-chart-alt-2 fs-2'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted">Real-time Users</div>
                                        <div id="gaRealtimeUsers" class="h3 mb-0 placeholder-glow"><span class="placeholder col-6"></span></div>
                                    </div>
                                    <div class="text-primary"><i class='bx bx-radar fs-2'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Metrics -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted">Average Session Duration</div>
                                <div id="gaAvgSessionDuration" class="h4 mb-0 placeholder-glow"><span class="placeholder col-7"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-muted">Bounce Rate</div>
                                <div id="gaBounceRate" class="h4 mb-0 placeholder-glow"><span class="placeholder col-5"></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">Daily Visitors</h6>
                                    <small id="gaRangeLabel" class="text-muted"></small>
                                </div>
                                <div class="mt-3" style="min-height: 320px;">
                                    <canvas id="gaDailyVisitorsChart" height="120"></canvas>
                                    <div id="gaDailyVisitorsEmpty" class="text-center text-muted d-none py-5">No data available for this range.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-0">Traffic Sources</h6>
                                <div class="mt-3" style="min-height: 320px;">
                                    <canvas id="gaTrafficSourcesChart" height="120"></canvas>
                                    <div id="gaTrafficSourcesEmpty" class="text-center text-muted d-none py-5">No traffic source data.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-0">Devices</h6>
                                <div class="mt-3" style="min-height: 320px;">
                                    <canvas id="gaDevicesChart" height="120"></canvas>
                                    <div id="gaDevicesEmpty" class="text-center text-muted d-none py-5">No device data.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-0">Top Visited Pages</h6>
                                <div class="mt-3" style="min-height: 320px;">
                                    <canvas id="gaTopPagesChart" height="120"></canvas>
                                    <div id="gaTopPagesEmpty" class="text-center text-muted d-none py-5">No top pages data.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tables -->
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Top Pages (Table)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Page</th>
                                                <th class="text-end">Views</th>
                                                <th class="text-end">Active Users</th>
                                            </tr>
                                        </thead>
                                        <tbody id="gaTopPagesTable">
                                            <tr>
                                                <td colspan="3" class="text-muted">Loading…</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Countries / Browsers</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Country</th>
                                                        <th class="text-end">Active Users</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="gaCountriesTable">
                                                    <tr>
                                                        <td colspan="2" class="text-muted">Loading…</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Browser</th>
                                                        <th class="text-end">Active Users</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="gaBrowsersTable">
                                                    <tr>
                                                        <td colspan="2" class="text-muted">Loading…</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function() {
        const root = document.getElementById('gaDashboardRoot');
        const presetEl = document.getElementById('gaPreset');
        const customWrap = document.getElementById('gaCustomRange');
        const startEl = document.getElementById('gaStartDate');
        const endEl = document.getElementById('gaEndDate');
        const applyBtn = document.getElementById('gaApply');
        const alertEl = document.getElementById('gaAlert');
        const rangeLabelEl = document.getElementById('gaRangeLabel');

        const endpoint = (root?.dataset?.endpoint || '').trim();
        const connectUrl = (root?.dataset?.connectUrl || '').trim();

        presetEl.value = root?.dataset?.defaultPreset || '30';

        let charts = {
            daily: null,
            sources: null,
            devices: null,
            topPages: null
        };

        function formatNumber(n) {
            if (n === null || n === undefined) return '0';
            return new Intl.NumberFormat().format(n);
        }

        function secondsToHms(seconds) {
            const s = Math.max(0, Math.round(Number(seconds || 0)));
            const m = Math.floor(s / 60);
            const r = s % 60;
            return `${m}m ${r}s`;
        }

        function showAlert(message, showConnect) {
            alertEl.classList.remove('d-none');
            alertEl.innerHTML = showConnect && connectUrl ?
                `${message} <a class="alert-link" href="${connectUrl}">Connect GA4</a>` :
                message;
        }

        function hideAlert() {
            alertEl.classList.add('d-none');
            alertEl.textContent = '';
        }

        function setLoading() {
            const ids = ['gaNewUsers', 'gaActiveUsers', 'gaSessions', 'gaPageViews', 'gaAvgSessionDuration', 'gaBounceRate', 'gaRealtimeUsers'];
            for (const id of ids) {
                const el = document.getElementById(id);
                el.classList.add('placeholder-glow');
                el.innerHTML = '<span class="placeholder col-6"></span>';
            }

            document.getElementById('gaTopPagesTable').innerHTML = '<tr><td colspan="3" class="text-muted">Loading…</td></tr>';
            document.getElementById('gaCountriesTable').innerHTML = '<tr><td colspan="2" class="text-muted">Loading…</td></tr>';
            document.getElementById('gaBrowsersTable').innerHTML = '<tr><td colspan="2" class="text-muted">Loading…</td></tr>';
        }

        function destroyChart(key) {
            if (charts[key]) {
                charts[key].destroy();
                charts[key] = null;
            }
        }

        function renderCharts(data) {
            // Empty states
            const dailyEmpty = document.getElementById('gaDailyVisitorsEmpty');
            const sourcesEmpty = document.getElementById('gaTrafficSourcesEmpty');
            const devicesEmpty = document.getElementById('gaDevicesEmpty');
            const topPagesEmpty = document.getElementById('gaTopPagesEmpty');

            // Daily visitors (Line)
            destroyChart('daily');
            const daily = data?.daily_visitors || {
                labels: [],
                values: []
            };
            dailyEmpty.classList.toggle('d-none', daily.labels.length > 0);
            if (daily.labels.length > 0) {
                charts.daily = new Chart(document.getElementById('gaDailyVisitorsChart'), {
                    type: 'line',
                    data: {
                        labels: daily.labels,
                        datasets: [{
                            label: 'Active Users',
                            data: daily.values,
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Traffic sources (Pie)
            destroyChart('sources');
            const sources = data?.traffic_sources || [];
            sourcesEmpty.classList.toggle('d-none', sources.length > 0);
            if (sources.length > 0) {
                charts.sources = new Chart(document.getElementById('gaTrafficSourcesChart'), {
                    type: 'pie',
                    data: {
                        labels: sources.map(x => x.source),
                        datasets: [{
                            data: sources.map(x => x.sessions),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Devices (Doughnut)
            destroyChart('devices');
            const devices = data?.devices || [];
            devicesEmpty.classList.toggle('d-none', devices.length > 0);
            if (devices.length > 0) {
                charts.devices = new Chart(document.getElementById('gaDevicesChart'), {
                    type: 'doughnut',
                    data: {
                        labels: devices.map(x => x.device),
                        datasets: [{
                            data: devices.map(x => x.active_users),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Top pages (Bar)
            destroyChart('topPages');
            const topPages = data?.top_pages || [];
            topPagesEmpty.classList.toggle('d-none', topPages.length > 0);
            if (topPages.length > 0) {
                charts.topPages = new Chart(document.getElementById('gaTopPagesChart'), {
                    type: 'bar',
                    data: {
                        labels: topPages.map(x => x.path || x.title || '(unknown)'),
                        datasets: [{
                            label: 'Views',
                            data: topPages.map(x => x.views),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        function renderTables(data) {
            const topPages = data?.top_pages || [];
            const countries = data?.countries || [];
            const browsers = data?.browsers || [];

            document.getElementById('gaTopPagesTable').innerHTML = topPages.length ?
                topPages.map(p => `
                    <tr>
                        <td>
                            <div class="fw-semibold">${(p.title || 'Untitled')}</div>
                            <div class="text-muted small text-truncate" style="max-width: 340px;">${(p.path || '')}</div>
                        </td>
                        <td class="text-end">${formatNumber(p.views)}</td>
                        <td class="text-end">${formatNumber(p.active_users)}</td>
                    </tr>
                `).join('') :
                '<tr><td colspan="3" class="text-muted">No data.</td></tr>';

            document.getElementById('gaCountriesTable').innerHTML = countries.length ?
                countries.map(c => `
                    <tr>
                        <td>${c.country}</td>
                        <td class="text-end">${formatNumber(c.active_users)}</td>
                    </tr>
                `).join('') :
                '<tr><td colspan="2" class="text-muted">No data.</td></tr>';

            document.getElementById('gaBrowsersTable').innerHTML = browsers.length ?
                browsers.map(b => `
                    <tr>
                        <td>${b.browser}</td>
                        <td class="text-end">${formatNumber(b.active_users)}</td>
                    </tr>
                `).join('') :
                '<tr><td colspan="2" class="text-muted">No data.</td></tr>';
        }

        function renderSummary(data) {
            const summary = data?.summary || {};

            const newUsers = document.getElementById('gaNewUsers');
            const activeUsers = document.getElementById('gaActiveUsers');
            const sessions = document.getElementById('gaSessions');
            const pageViews = document.getElementById('gaPageViews');
            const avgDur = document.getElementById('gaAvgSessionDuration');
            const bounce = document.getElementById('gaBounceRate');
            const realtime = document.getElementById('gaRealtimeUsers');

            newUsers.classList.remove('placeholder-glow');
            activeUsers.classList.remove('placeholder-glow');
            sessions.classList.remove('placeholder-glow');
            pageViews.classList.remove('placeholder-glow');
            avgDur.classList.remove('placeholder-glow');
            bounce.classList.remove('placeholder-glow');
            realtime.classList.remove('placeholder-glow');

            newUsers.textContent = formatNumber(summary.new_users || 0);
            activeUsers.textContent = formatNumber(summary.active_users || 0);
            sessions.textContent = formatNumber(summary.sessions || 0);
            pageViews.textContent = formatNumber(summary.page_views || 0);
            avgDur.textContent = secondsToHms(summary.avg_session_duration || 0);
            bounce.textContent = `${Number(summary.bounce_rate || 0).toFixed(1)}%`;
            realtime.textContent = formatNumber(data?.realtime_users || 0);
        }

        async function fetchData() {
            hideAlert();
            setLoading();

            const preset = presetEl.value;
            const params = new URLSearchParams({
                preset
            });

            if (preset === 'custom') {
                if (!startEl.value || !endEl.value) {
                    showAlert('Please select a custom start and end date.');
                    return;
                }
                params.set('start_date', startEl.value);
                params.set('end_date', endEl.value);
            }

            try {
                if (!endpoint) {
                    showAlert('Analytics endpoint is not configured for this page.', false);
                    return;
                }

                const res = await fetch(`${endpoint}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const json = await res.json();
                if (!res.ok || !json.ok) {
                    showAlert('Unable to load analytics data right now. Please try again.', true);
                    return;
                }

                const range = json.range || {};
                rangeLabelEl.textContent = `${range.start_date} to ${range.end_date}`;

                renderSummary(json.data);
                renderCharts(json.data);
                renderTables(json.data);
            } catch (e) {
                showAlert('Unable to reach the analytics service. Please check your connection and try again.', true);
            }
        }

        function onPresetChange() {
            const isCustom = presetEl.value === 'custom';
            customWrap.classList.toggle('d-none', !isCustom);
        }

        presetEl.addEventListener('change', onPresetChange);
        applyBtn.addEventListener('click', fetchData);

        onPresetChange();
        fetchData();
    })();
</script>
@endsection
