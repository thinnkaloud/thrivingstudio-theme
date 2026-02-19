<?php
/**
 * Performance Dashboard for Thriving Studio Theme
 * Provides real-time performance monitoring and optimization insights
 */

/**
 * Add performance dashboard to admin menu
 */
function add_performance_dashboard_menu() {
    add_menu_page(
        'Performance Dashboard',
        'Performance',
        'manage_options',
        'performance-dashboard',
        'render_performance_dashboard',
        'dashicons-performance',
        30
    );
}
add_action('admin_menu', 'add_performance_dashboard_menu');

/**
 * Render the performance dashboard
 */
function render_performance_dashboard() {
    $web_vitals = get_option('thrivingstudio_web_vitals', []);
    $performance_data = analyze_performance_data($web_vitals);
    
    ?>
    <div class="wrap">
        <h1>Performance Dashboard</h1>
        
        <!-- Core Web Vitals Summary -->
        <div class="card">
            <h2>Core Web Vitals</h2>
            <div class="web-vitals-grid">
                <div class="metric-card <?php echo $performance_data['lcp_status']; ?>">
                    <h3>LCP (Largest Contentful Paint)</h3>
                    <div class="metric-value"><?php echo $performance_data['lcp_avg']; ?>ms</div>
                    <div class="metric-status"><?php echo $performance_data['lcp_status_text']; ?></div>
                </div>
                
                <div class="metric-card <?php echo $performance_data['fid_status']; ?>">
                    <h3>FID (First Input Delay)</h3>
                    <div class="metric-value"><?php echo $performance_data['fid_avg']; ?>ms</div>
                    <div class="metric-status"><?php echo $performance_data['fid_status_text']; ?></div>
                </div>
                
                <div class="metric-card <?php echo $performance_data['cls_status']; ?>">
                    <h3>CLS (Cumulative Layout Shift)</h3>
                    <div class="metric-value"><?php echo $performance_data['cls_avg']; ?></div>
                    <div class="metric-status"><?php echo $performance_data['cls_status_text']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Performance Recommendations -->
        <div class="card">
            <h2>Optimization Recommendations</h2>
            <div class="recommendations">
                <?php foreach ($performance_data['recommendations'] as $recommendation): ?>
                    <div class="recommendation-item">
                        <span class="priority-<?php echo $recommendation['priority']; ?>"><?php echo $recommendation['priority']; ?></span>
                        <p><?php echo $recommendation['text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Recent Performance Data -->
        <div class="card">
            <h2>Recent Performance Data</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Page</th>
                        <th>LCP</th>
                        <th>FID</th>
                        <th>CLS</th>
                        <th>TTFB</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($web_vitals, -10) as $vital): ?>
                        <tr>
                            <td><?php echo date('M j, Y H:i', strtotime($vital['timestamp'])); ?></td>
                            <td><?php echo esc_html($vital['page']); ?></td>
                            <td><?php echo $vital['metric'] === 'LCP' ? $vital['value'] . 'ms' : '-'; ?></td>
                            <td><?php echo $vital['metric'] === 'FID' ? $vital['value'] . 'ms' : '-'; ?></td>
                            <td><?php echo $vital['metric'] === 'CLS' ? $vital['value'] : '-'; ?></td>
                            <td><?php echo $vital['metric'] === 'TTFB' ? $vital['value'] . 'ms' : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Performance Actions -->
        <div class="card">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <button class="button button-primary" onclick="clearPerformanceCache()">Clear Performance Cache</button>
                <button class="button" onclick="exportPerformanceData()">Export Data</button>
                <button class="button" onclick="runPerformanceTest()">Run Performance Test</button>
            </div>
        </div>
    </div>
    
    <style>
        .web-vitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .metric-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #ddd;
        }
        
        .metric-card.good { border-color: #46b450; background: #f7fcf7; }
        .metric-card.needs-improvement { border-color: #ffb900; background: #fffbf0; }
        .metric-card.poor { border-color: #dc3232; background: #fef7f7; }
        
        .metric-value {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .metric-status {
            font-size: 0.9em;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .recommendations {
            margin: 20px 0;
        }
        
        .recommendation-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .priority-high { color: #dc3232; font-weight: bold; }
        .priority-medium { color: #ffb900; font-weight: bold; }
        .priority-low { color: #46b450; font-weight: bold; }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
    </style>
    
    <script>
        function clearPerformanceCache() {
            if (confirm('Clear all performance cache data?')) {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=clear_performance_cache'
                }).then(() => location.reload());
            }
        }
        
        function exportPerformanceData() {
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=export_performance_data'
            }).then(response => response.blob())
              .then(blob => {
                  const url = window.URL.createObjectURL(blob);
                  const a = document.createElement('a');
                  a.href = url;
                  a.download = 'performance-data.json';
                  a.click();
              });
        }
        
        function runPerformanceTest() {
            alert('Performance test initiated. Check the console for results.');
            // Implementation for performance testing
        }
    </script>
    <?php
}

/**
 * Analyze performance data and generate recommendations
 */
function analyze_performance_data($web_vitals) {
    $lcp_values = array_filter($web_vitals, function($v) { return $v['metric'] === 'LCP'; });
    $fid_values = array_filter($web_vitals, function($v) { return $v['metric'] === 'FID'; });
    $cls_values = array_filter($web_vitals, function($v) { return $v['metric'] === 'CLS'; });
    
    $lcp_avg = !empty($lcp_values) ? array_sum(array_column($lcp_values, 'value')) / count($lcp_values) : 0;
    $fid_avg = !empty($fid_values) ? array_sum(array_column($fid_values, 'value')) / count($fid_values) : 0;
    $cls_avg = !empty($cls_values) ? array_sum(array_column($cls_values, 'value')) / count($cls_values) : 0;
    
    // Determine status
    $lcp_status = $lcp_avg <= 2500 ? 'good' : ($lcp_avg <= 4000 ? 'needs-improvement' : 'poor');
    $fid_status = $fid_avg <= 100 ? 'good' : ($fid_avg <= 300 ? 'needs-improvement' : 'poor');
    $cls_status = $cls_avg <= 0.1 ? 'good' : ($cls_avg <= 0.25 ? 'needs-improvement' : 'poor');
    
    // Generate recommendations
    $recommendations = [];
    
    if ($lcp_avg > 2500) {
        $recommendations[] = [
            'priority' => 'high',
            'text' => 'Optimize Largest Contentful Paint by implementing critical CSS and image optimization.'
        ];
    }
    
    if ($fid_avg > 100) {
        $recommendations[] = [
            'priority' => 'high',
            'text' => 'Reduce First Input Delay by minimizing JavaScript execution time.'
        ];
    }
    
    if ($cls_avg > 0.1) {
        $recommendations[] = [
            'priority' => 'medium',
            'text' => 'Fix Cumulative Layout Shift by setting proper image dimensions and avoiding dynamic content insertion.'
        ];
    }
    
    return [
        'lcp_avg' => round($lcp_avg),
        'fid_avg' => round($fid_avg),
        'cls_avg' => round($cls_avg, 3),
        'lcp_status' => $lcp_status,
        'fid_status' => $fid_status,
        'cls_status' => $cls_status,
        'lcp_status_text' => ucfirst($lcp_status),
        'fid_status_text' => ucfirst($fid_status),
        'cls_status_text' => ucfirst($cls_status),
        'recommendations' => $recommendations
    ];
}

/**
 * Handle AJAX requests for performance dashboard
 */
function handle_performance_ajax() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_performance_cache':
            delete_option('thrivingstudio_web_vitals');
            wp_send_json_success('Cache cleared');
            break;
            
        case 'export_performance_data':
            $data = get_option('thrivingstudio_web_vitals', []);
            wp_send_json($data);
            break;
    }
}
add_action('wp_ajax_clear_performance_cache', 'handle_performance_ajax');
add_action('wp_ajax_export_performance_data', 'handle_performance_ajax'); 