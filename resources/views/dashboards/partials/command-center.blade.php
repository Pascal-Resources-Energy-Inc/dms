<header class="topbar">
    <div class="welcome">
        <h1>{{ $heading }}</h1>
        <p>{{ $subheading }}</p>
        <div class="db-status">
            @foreach ($dashboard['connectedDatabases'] as $database)
                <span class="db-pill">{{ $database['name'] }}: {{ $database['status'] }}</span>
            @endforeach
        </div>
    </div>
    <div class="filters">
        <div class="filter">
            <span class="nav-icon">L</span>
            <div>
                <small>Territory</small>
                <strong>{{ $dashboard['territory'] }}</strong>
            </div>
        </div>
        <div class="filter">
            <span class="nav-icon">C</span>
            <div>
                <small>Period</small>
                <strong>{{ $dashboard['periodLabel'] }}</strong>
            </div>
        </div>
        <div class="profile">
            <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <small>{{ auth()->user()->roleName() }}</small>
            </div>
        </div>
    </div>
</header>

<section class="kpi-grid">
    @foreach ($dashboard['kpis'] as $kpi)
        <article class="kpi card border-0">
            <div class="kpi-head">
                <span class="icon tone-{{ $kpi['tone'] }}">{{ strtoupper(substr($kpi['icon'], 0, 1)) }}</span>
                <div>
                    <p class="kpi-value">{{ $kpi['value'] }}</p>
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                </div>
            </div>
            <div class="trend-up">{{ $kpi['trend'] }}</div>
            <div class="kpi-sub">{{ $kpi['sub'] }}</div>
        </article>
    @endforeach
</section>

<section class="dashboard-grid">
    <article class="panel card border-0">
        <h2 class="panel-title"><span class="section-no">1</span>Purchases vs Sales Trend (MTD) - Quantity</h2>
        @include('dashboards.partials.trend-chart', ['trend' => $dashboard['trend']])
        <div class="two-col">
            <div>
                <p class="small-muted">Total Dealer Purchases</p>
                <p class="kpi-value">{{ number_format($dashboard['sales']['units']) }}</p>
                <p class="trend-up">+15.2% vs previous period</p>
            </div>
            <div>
                <p class="small-muted">Total Refills Purchased</p>
                <p class="kpi-value">{{ number_format($dashboard['sales']['refills']) }}</p>
                <p class="trend-up">+18.6% vs previous period</p>
            </div>
        </div>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title"><span class="section-no">2</span>Stove Customer Growth vs Refills Trend</h2>
        @include('dashboards.partials.trend-chart', ['trend' => $dashboard['trend']])
        <div class="two-col">
            <div>
                <p class="small-muted">Dealer Purchase Value</p>
                <p class="kpi-value">P{{ number_format($dashboard['sales']['value'], 2) }}</p>
                <p class="trend-up">Cash rate: {{ $dashboard['sales']['cash_rate'] }}%</p>
            </div>
            <div>
                <p class="small-muted">Refill Sales Value</p>
                <p class="kpi-value">P{{ number_format($dashboard['sales']['refill_value'], 2) }}</p>
                <p class="trend-up">Avg refill/dealer: {{ $dashboard['sales']['average_refills_per_dealer'] }}</p>
            </div>
        </div>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title">Refill Growth Analysis (MTD)</h2>
        <div class="growth-box">
            <div>
                <strong>+18.4%</strong>
                <div class="kpi-label">Refill Growth (Units)</div>
            </div>
            <div>
                <strong>+18.6%</strong>
                <div class="kpi-label">Refill Sales Growth</div>
            </div>
            <p class="trend-note">Live values are pulled from `admin_crms` and `admin_crms2`; growth percentages can be replaced by historical targets when those tables are added.</p>
        </div>
    </article>
</section>

<section class="two-col">
    <article class="panel card border-0">
        <h2 class="panel-title">Monthly Average Refills per Dealer</h2>
        <p class="kpi-value">{{ $dashboard['sales']['average_refills_per_dealer'] }}</p>
        <p class="trend-up">Active dealers: {{ number_format($dashboard['dealers']['active']) }}</p>
        <div class="chart-lines" style="height: 120px;">
            @foreach ($dashboard['trend']->take(6) as $day)
                <div class="bar-pair">
                    <span class="bar refill" style="height: {{ min(100, max(8, $day['refills'] * 12)) }}%;"></span>
                </div>
            @endforeach
        </div>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title">Top Performing Dealers (MTD)</h2>
        <div class="table-wrap">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Dealer</th>
                        <th>Outlet Type</th>
                        <th>Units</th>
                        <th>Sales Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dashboard['topDealers'] as $dealer)
                        <tr>
                            <td>{{ $dealer->dealer }}</td>
                            <td>{{ $dealer->outlet_type }}</td>
                            <td>{{ number_format($dealer->units) }}</td>
                            <td>P{{ number_format($dealer->sales_value, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No dealer sales records found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="two-col">
    <article class="panel card border-0">
        <h2 class="panel-title">Inventory Summary</h2>
        <div class="mini-grid">
            <div class="mini-panel card border-0"><span class="small-muted">Total Stock</span><strong>{{ number_format($dashboard['inventory']['total_stock']) }}</strong></div>
            <div class="mini-panel card border-0"><span class="small-muted">Low Stock</span><strong>{{ $dashboard['inventory']['low_stock_count'] }}</strong></div>
            <div class="mini-panel card border-0"><span class="small-muted">Out of Stock</span><strong>{{ $dashboard['inventory']['out_of_stock_count'] }}</strong></div>
            <div class="mini-panel card border-0"><span class="small-muted">Cover Days</span><strong>{{ $dashboard['inventory']['cover_days'] }}</strong></div>
        </div>
        <div class="table-wrap" style="margin-top: 12px;">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Item</th>
                        <th>Inventory</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dashboard['inventory']['low_stock_items'] as $item)
                        <tr>
                            <td>{{ $item['sku'] }}</td>
                            <td>{{ $item['item'] }}</td>
                            <td>{{ number_format($item['qty']) }}</td>
                            <td><span class="trend-up">Reorder</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No low-stock items found in inventory transfers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title">{{ $rolePanelTitle }}</h2>
        <div class="mini-grid">
            @foreach (\App\User::roles() as $role => $label)
                <div class="mini-panel card border-0">
                    <span class="small-muted">{{ $label }}</span>
                    <strong>{{ $roleCounts[$role] ?? 0 }}</strong>
                </div>
            @endforeach
        </div>
        <div class="table-wrap" style="margin-top: 12px;">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Territory</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $person)
                        <tr>
                            <td>{{ $person->name }}</td>
                            <td>{{ $person->email }}</td>
                            <td>{{ $person->roleName() }}</td>
                            <td>{{ $person->territory ?: 'Unassigned' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="mini-grid" style="margin-top: 12px;">
    <article class="panel card border-0">
        <h2 class="panel-title">Channel Productivity Insights</h2>
        <ul class="insight-list">
            @foreach ($dashboard['dealers']['by_type'] as $type => $count)
                <li><span class="dot info">i</span><span>{{ $type }}: {{ $count }} outlets</span><span></span></li>
            @endforeach
        </ul>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title">Alerts & Notifications</h2>
        <ul class="alert-list">
            @foreach ($dashboard['alerts'] as $alert)
                <li><span class="dot {{ $alert['tone'] }}">!</span><span>{{ $alert['text'] }}</span><span class="small-muted">{{ $alert['time'] }}</span></li>
            @endforeach
        </ul>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title">Tactical Pricing Overview</h2>
        <div class="mini-grid">
            @foreach ($dashboard['pricing'] as $pricing)
                <div class="mini-panel card border-0"><span class="small-muted">{{ $pricing['label'] }}</span><strong>{{ $pricing['value'] }}</strong></div>
            @endforeach
        </div>
    </article>

    <article class="panel card border-0">
        <h2 class="panel-title">TimePay & Collections</h2>
        <div class="mini-grid">
            <div class="mini-panel card border-0"><span class="small-muted">Active Employees</span><strong>{{ number_format($dashboard['timepay']['active_employees']) }}</strong></div>
            <div class="mini-panel card border-0"><span class="small-muted">Attendance Today</span><strong>{{ number_format($dashboard['timepay']['attendance_today']) }}</strong></div>
            <div class="mini-panel card border-0"><span class="small-muted">Monthly Target</span><strong>P{{ number_format($dashboard['timepay']['monthly_target'], 2) }}</strong></div>
            <div class="mini-panel card border-0"><span class="small-muted">Collection Efficiency</span><strong>{{ $dashboard['sales']['cash_rate'] }}%</strong></div>
        </div>
    </article>
</section>

