<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/plus-jakarta-sans.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
</head>

<body>

    <div class="wrapper">
        <!-- ===== SIDEBAR ===== -->
        @include('admin.layouts.inc.sidebar')

        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay"></div>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="main-content">
            <!-- TOP NAVBAR -->
            @include('admin.layouts.inc.navbar')

            <!-- PAGE CONTENT -->
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap Toast for Welcome Message -->
    @include('admin.layouts.inc.welcome')

    <!-- Local Scripts -->
    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>

    <!-- Dashboard specific charts -->
    <script>
        $(document).ready(function() {
            // Check if chart contexts exist
            if (document.getElementById('revenueChart')) {
                var ctxArea = document.getElementById('revenueChart').getContext('2d');

                // Create a gradient for the area chart
                var gradient = ctxArea.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); // primary color with opacity
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0.05)'); // fade out

                var revenueChart = new Chart(ctxArea, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Revenue',
                            data: [28000, 32000, 27000, 38000, 43000, 48000],
                            backgroundColor: gradient,
                            borderColor: '#4f46e5', // var(--primary)
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4, // Smooth curve
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#4f46e5',
                            pointHoverBackgroundColor: '#4f46e5',
                            pointHoverBorderColor: '#ffffff',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 10,
                                titleFont: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 13
                                },
                                bodyFont: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 14,
                                    weight: 'bold'
                                },
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    },
                                    color: '#64748b'
                                }
                            },
                            y: {
                                grid: {
                                    borderDash: [4, 4],
                                    color: '#e2e8f0',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    },
                                    color: '#64748b',
                                    callback: function(value) {
                                        return '$' + value / 1000 + 'k';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            if (document.getElementById('statusChart')) {
                var ctxDoughnut = document.getElementById('statusChart').getContext('2d');
                var statusChart = new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress', 'On Hold'],
                        datasets: [{
                            data: [45, 35, 20],
                            backgroundColor: [
                                '#22c55e', // success
                                '#4f46e5', // primary
                                '#f59e0b' // warning
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        family: "'Plus Jakarta Sans', sans-serif",
                                        size: 12
                                    },
                                    color: '#64748b'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                bodyFont: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
