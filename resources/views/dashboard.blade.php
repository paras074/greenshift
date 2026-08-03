@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

    <div class="gs-page-topbar">
        <div class="gs-page-topbar-left">
            <h2>Dashboard</h2>
            <p>Welcome back, <span>{{ Auth::user()->name ?? 'User' }}</span>. Here's what's happening.</p>
        </div>
        <div class="gs-page-topbar-actions">
            <div class="gs-dash-toggle">
                <button class="gs-toggle-btn gs-toggle-btn--active" id="toggleDaily" onclick="toggleactivity('1', 'yes')">Daily</button>
                <button class="gs-toggle-btn" id="toggleWeekly"  onclick="toggleactivity('2', 'yes')">Weekly</button>
            </div>
        </div>
    </div>

    <div class="gs-dash-wrap" id="gs-wrap">
		@include('dashboard.part')
    </div>{{-- /gs-dash-wrap --}}


    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        var css      = getComputedStyle(document.documentElement);
        var primary  = css.getPropertyValue('--primary-color').trim() || '#3D9082';
        var navy     = css.getPropertyValue('--secondary-color').trim() || '#000E49';
        var borderC  = css.getPropertyValue('--border-color').trim() || '#e4e8f0';
        var textSec  = css.getPropertyValue('--text-secondary').trim() || '#6b7a99';

        Chart.defaults.font.family = 'Montserrat, sans-serif';
        Chart.defaults.color       = textSec;
        var chartInstance = null;
        var doughnutChartInstance = null; // Global instance

        /* DOUGHNUT CHART */
        var dealLabels = ['New', 'Active', 'Req. Analysis', 'Final / Won', 'Lost'];
        var dealData   = [4, 18, 9, 11, 5];
        var dealColors = ['#2563eb', '#3D9082', '#f59e0b', '#16a34a', '#dc2626'];


        window.toggleactivity = function(type, toggle) {
            const wrap = document.getElementById('gs-wrap');

            if (toggle === 'yes') {
                wrap.innerHTML = `
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>`;
            }

            fetch(`/dashboard/stats/${type}`)
                .then(response => response.json())
                .then(data => {
                    if (toggle === 'yes') {
                        wrap.innerHTML = data.html;
                    }                    
                    const labels = data.graphData.labels;
                    const values = data.graphData.values;
                    updateChart(labels, values, type);

                    // 4. Update Doughnut Chart & Legend
                    const dLabels = data.secondGraphData.labels;
                    const dValues = data.secondGraphData.values;
                    const dColors = data.secondGraphData.colors;
                    updateDoughnut(dLabels, dValues, dColors);

                })
                .catch(error => {
                    console.error('Error:', error);
                    if (toggle === 'yes') {
                        wrap.innerHTML = '<p class="text-danger text-center">Failed to load data. Please try again.</p>';
                    }
                });
        };

        window.updateChart = function(labels, values, type) {
            const ctx = document.getElementById('chartLeads').getContext('2d');
            
            // If chart already exists, destroy it before creating a new one to prevent overlap
            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Leads',
                        data: values,
                        backgroundColor: values.map((v, i) => i === values.length - 1 ? primary : 'rgba(61,144,130,.18)'),
                        borderColor: values.map((v, i) => i === values.length - 1 ? primary : 'rgba(61,144,130,.35)'),
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: navy, padding: 10, cornerRadius: 8,
                            callbacks: { label: function(ctx) { return ' ' + ctx.parsed.y + ' leads'; } }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, 
                        ticks: { 
                            font: { size: 10, weight: '600' }, 
                            callback: function(value, index, ticks) {
                                let label = this.getLabelForValue(value);
                                
                                // ONLY for Daily (type 1), strip the month
                                if (type == '1') {
                                    return label.split(' ')[0]; // Returns "13" from "13 Apr"
                                }
                                
                                // For Weekly (type 2), return the full label "23 Feb - 01 Mar"
                                return label;
                            }
                        } 
                        },
                        y: { grid: { color: borderC }, border: { display: false, dash: [4,4] }, ticks: { stepSize: 2, font: { size: 11 } }, beginAtZero: true }
                    }
                }
            });
        }

        window.updateDoughnut = function(labels, values, colors) {
            const chartElement = document.getElementById('chartDeals');
            if (!chartElement) {
                return; 
            }
            
            const ctx = chartElement.getContext('2d');
            const legend = document.getElementById('doughnut-legend');

            // 1. Clear existing Legend
            legend.innerHTML = '';

            // 2. Destroy old chart to prevent flickering
            if (doughnutChartInstance) {
                doughnutChartInstance.destroy();
            }

            // 3. Create New Chart
            doughnutChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: navy, padding: 10, cornerRadius: 8,
                            callbacks: { label: function(ctx) { return ' ' + ctx.label + ': ' + ctx.parsed + ' leads'; } }
                        }
                    }
                }
            });

            // 4. Update the Legend HTML
            labels.forEach((label, i) => {
                var item = document.createElement('div');
                item.className = 'gs-legend-item';
                item.innerHTML = `
                    <span class="gs-legend-dot" style="background:${colors[i]}"></span>
                    ${label} <strong style="color:var(--text-primary);margin-left:2px;">(${values[i]})</strong>
                `;
                legend.appendChild(item);
            });
        }

        /* DAILY / WEEKLY TOGGLE */
        document.getElementById('toggleDaily').addEventListener('click', function() {
            this.classList.add('gs-toggle-btn--active');
            document.getElementById('toggleWeekly').classList.remove('gs-toggle-btn--active');
        });
        document.getElementById('toggleWeekly').addEventListener('click', function() {
            this.classList.add('gs-toggle-btn--active');
            document.getElementById('toggleDaily').classList.remove('gs-toggle-btn--active');
        });

        document.addEventListener('DOMContentLoaded', function() {
            toggleactivity('1', 'no');
        });

    </script>

@endsection