<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\views\admin\service-health.php

require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">System Health Monitor</h1>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Microservice Health Status</h6>
                    <button id="refreshHealth" class="btn btn-sm btn-primary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body" id="healthStatusContainer">
                    <?php echo ServiceAvailabilityMiddleware::generateHealthReport($healthResults); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Service Dependencies</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Dependencies</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Patient Service</td>
                                    <td>None</td>
                                </tr>
                                <tr>
                                    <td>Prescription Service</td>
                                    <td>Patient Service, Auth Service</td>
                                </tr>
                                <tr>
                                    <td>Appointment Service</td>
                                    <td>Patient Service, Auth Service</td>
                                </tr>
                                <tr>
                                    <td>Auth Service</td>
                                    <td>None</td>
                                </tr>
                                <tr>
                                    <td>Report Service</td>
                                    <td>All Services</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Health Check History</h6>
                </div>
                <div class="card-body">
                    <div id="healthHistoryChart"></div>
                    <div class="mt-3 small text-muted">
                        <p>This chart shows the response times of services over time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize health history data
    const healthHistory = {
        timestamps: [],
        services: {}
    };
    
    // Add the current health check data
    const currentCheck = <?php echo json_encode($healthResults); ?>;
    updateHealthHistory(currentCheck);
    
    // Set up refresh button
    document.getElementById('refreshHealth').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        
        fetch('/admin/refresh-service-health', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('healthStatusContainer').innerHTML = 
                '<div class="alert alert-success mb-4">Health check completed at ' + 
                new Date().toLocaleTimeString() + '</div>' +
                generateHealthReportHtml(data);
            
            updateHealthHistory(data);
            
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        })
        .catch(error => {
            console.error('Error refreshing health status:', error);
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            
            document.getElementById('healthStatusContainer').innerHTML += 
                '<div class="alert alert-danger">Failed to check services: ' + error.message + '</div>';
        });
    });
    
    // Function to update health history
    function updateHealthHistory(data) {
        const timestamp = new Date().toLocaleTimeString();
        healthHistory.timestamps.push(timestamp);
        
        // Keep only the last 10 checks
        if (healthHistory.timestamps.length > 10) {
            healthHistory.timestamps.shift();
        }
        
        // Update each service's response times
        Object.entries(data.services).forEach(([key, service]) => {
            if (!healthHistory.services[key]) {
                healthHistory.services[key] = [];
            }
            
            const responseTime = service.responseTime || null;
            healthHistory.services[key].push(responseTime);
            
            // Keep only the last 10 data points
            if (healthHistory.services[key].length > 10) {
                healthHistory.services[key].shift();
            }
        });
        
        updateHealthChart();
    }
    
    // Generate HTML for health report
    function generateHealthReportHtml(results) {
        let html = '<div class="service-health-report">';
        html += '<h5>Service Health Status</h5>';
        html += '<table class="table table-sm table-striped">';
        html += '<thead><tr><th>Service</th><th>Status</th><th>Response Time</th></tr></thead><tbody>';
        
        Object.values(results.services).forEach(service => {
            const statusClass = service.available ? 'text-success' : (service.critical ? 'text-danger' : 'text-warning');
            const statusIcon = service.available ? 'check-circle' : 'exclamation-triangle';
            
            html += '<tr>';
            html += '<td>' + service.name + '</td>';
            html += '<td><span class="' + statusClass + '"><i class="fas fa-' + statusIcon + '"></i> ' + 
                   (service.available ? 'Available' : 'Unavailable') + '</span></td>';
            html += '<td>' + (service.responseTime ? service.responseTime + 'ms' : 'N/A') + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        html += '</div>';
        
        return html;
    }
    
    // Update the health history chart
    function updateHealthChart() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }
        
        // Destroy existing chart if it exists
        if (window.healthChart) {
            window.healthChart.destroy();
        }
        
        // Prepare datasets
        const datasets = [];
        const colors = {
            'patient': 'rgba(78, 115, 223, 1)',
            'prescription': 'rgba(28, 200, 138, 1)',
            'auth': 'rgba(246, 194, 62, 1)',
            'appointment': 'rgba(54, 185, 204, 1)',
            'report': 'rgba(231, 74, 59, 1)'
        };
        
        Object.entries(healthHistory.services).forEach(([key, data]) => {
            datasets.push({
                label: key,
                data: data,
                backgroundColor: colors[key] || 'rgba(133, 135, 150, 0.2)',
                borderColor: colors[key] || 'rgba(133, 135, 150, 1)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: colors[key] || 'rgba(133, 135, 150, 1)',
                pointBorderColor: '#fff',
                pointHoverRadius: 5,
                tension: 0.3
            });
        });
        
        // Create the chart
        const ctx = document.getElementById('healthHistoryChart');
        window.healthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: healthHistory.timestamps,
                datasets: datasets
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Response Time (ms)'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y !== null ? context.parsed.y + 'ms' : 'N/A';
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>