/**
 * Location Wise Products Dashboard JavaScript
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        initDashboardCharts();
    });

    /**
     * Initialize all dashboard charts
     */
    function initDashboardCharts() {
        // Get data from the localized script
        const data = window.mulopimfwc_DashboardData;

        if (!data) {
            console.error('Dashboard data not available');
            return;
        }

        initProductsChart();
        initStockChart();
        initNewProductsChart();
        initInvestmentChart();
        initOrdersChart();
        initRevenueChart();
    }

    function getLocationLabels() {
        const data = window.mulopimfwc_DashboardData || {};

        if (Array.isArray(data.locationLabels) && data.locationLabels.length) {
            return data.locationLabels;
        }

        if (data.locationColors && typeof data.locationColors === 'object') {
            return Object.keys(data.locationColors);
        }

        return [];
    }

    function getRandomValues(count, min, max) {
        if (!count) {
            return [];
        }

        const safeMin = typeof min === 'number' ? min : 0;
        const safeMax = typeof max === 'number' ? max : safeMin;

        return Array.from({ length: count }, () => (
            Math.floor(Math.random() * (safeMax - safeMin + 1)) + safeMin
        ));
    }

    /**
    * Add small offsets to duplicate values to prevent overlapping in charts
    */
    function addValueOffsets(values) {
        const valueMap = {};
        const offsetValues = [...values];

        // Group indices by value
        values.forEach((value, index) => {
            if (!valueMap[value]) {
                valueMap[value] = [];
            }
            valueMap[value].push(index);
        });

        // Add tiny offsets to duplicate values
        Object.keys(valueMap).forEach(value => {
            const indices = valueMap[value];
            if (indices.length > 1) {
                // Multiple items with same value - add small offsets
                indices.forEach((index, offsetIndex) => {
                    // Add very small offset (0.0001 * offsetIndex) to ensure different angles
                    offsetValues[index] = parseFloat(value) + (offsetIndex * 0.0001);
                });
            }
        });

        return offsetValues;
    }

    function getValuesForLabels(source, labels) {
        if (!source) {
            return labels.map(() => 0);
        }

        return labels.map(label => {
            const value = source[label];
            return value !== undefined ? value : 0;
        });
    }

    function hexToRgba(color, alpha) {
        if (!color || typeof color !== 'string') {
            return color;
        }

        if (color.startsWith('rgba')) {
            return color;
        }

        if (color.startsWith('rgb')) {
            return color.replace('rgb(', 'rgba(').replace(')', `, ${alpha})`);
        }

        if (color[0] !== '#') {
            return color;
        }

        let hex = color.slice(1);
        if (hex.length === 3) {
            hex = hex.split('').map(char => char + char).join('');
        }

        if (hex.length !== 6) {
            return color;
        }

        const num = parseInt(hex, 16);
        const r = (num >> 16) & 255;
        const g = (num >> 8) & 255;
        const b = num & 255;

        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    /**
     * Initialize Products by Location Chart
     */
    function initProductsChart() {
        const ctx = document.getElementById('locationProductsChart');
        if (!ctx) return;

        const labels = getLocationLabels();
        if (!labels.length) {
            return;
        }

        const originalValues = getValuesForLabels(mulopimfwc_DashboardData.productCounts, labels);
        const values = addValueOffsets(originalValues);
        const bgColors = labels.map(label => mulopimfwc_DashboardData.locationColors[label] || 'rgba(37, 99, 235, 0.7)');
        const borderColors = labels.map(label => mulopimfwc_DashboardData.locationBorderColors[label] || 'rgba(37, 99, 235, 1)');
        const previousColors = borderColors.map(color => hexToRgba(color, 0.35));

        window.locationProductsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: bgColors,
                    borderColor: '#f8f9fa',
                    borderWidth: 2,
                    // Store original values in dataset metadata
                    originalValues: originalValues
                }, {
                    data: [],
                    backgroundColor: previousColors,
                    borderColor: '#f8f9fa',
                    borderWidth: 1,
                    hoverOffset: 0,
                    weight: 0.6,
                    originalValues: [],
                    label: mulopimfwc_DashboardData.i18n.previousPeriod || 'Previous period',
                    hidden: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '45%',
                plugins: {
                    legend: { display: false },
                    title: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                // Use original value for display, not the offset value
                                const origValues = context.dataset.originalValues || originalValues;
                                const value = origValues[context.dataIndex];
                                const total = origValues.reduce((sum, v) => sum + v, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                const datasetLabel = context.dataset.label ? `${context.dataset.label} - ` : '';
                                return `${datasetLabel}${context.label}: ${value} (${percentage}%)`;
                            }
                        },
                        bodyFont: { size: 14 },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 6
                    },
                    datalabels: false
                },
                layout: {
                    padding: { top: 40, bottom: 40, left: 120, right: 120 }
                }
            },
            plugins: [{
                afterDraw: function (chart) {
                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;
                    const radius = Math.min(
                        (chartArea.right - chartArea.left) / 2,
                        (chartArea.bottom - chartArea.top) / 2
                    );

                    const origValues = chart.data.datasets[0].originalValues || originalValues;
                    const total = origValues.reduce((sum, value) => sum + value, 0);
                    const percentages = origValues.map(v => total > 0 ? ((v / total) * 100).toFixed(1) : '0.0');
                    const datasetColors = chart.data.datasets[0].backgroundColor || bgColors;
                    const chartData = chart.data.datasets[0].data;

                    // Calculate initial label positions
                    const labelPositions = [];
                    chartData.forEach((value, index) => {
                        const meta = chart.getDatasetMeta(0);
                        const arc = meta.data[index];
                        const angle = (arc.startAngle + arc.endAngle) / 2;

                        const lineLength = 30;
                        const x2 = centerX + Math.cos(angle) * (radius + lineLength);
                        const y2 = centerY + Math.sin(angle) * (radius + lineLength);
                        const horizontalLength = 20;
                        const x3 = x2 + (Math.cos(angle) > 0 ? horizontalLength : -horizontalLength);

                        labelPositions.push({
                            index,
                            angle,
                            x1: centerX + Math.cos(angle) * radius,
                            y1: centerY + Math.sin(angle) * radius,
                            x2, y2, x3,
                            y3: y2,
                            label: `${chart.data.labels[index]}: ${percentages[index]}%`,
                            color: datasetColors[index],
                            side: Math.cos(angle) > 0 ? 'right' : 'left'
                        });
                    });

                    // Adjust overlapping labels
                    const leftLabels = labelPositions.filter(p => p.side === 'left').sort((a, b) => a.y3 - b.y3);
                    const rightLabels = labelPositions.filter(p => p.side === 'right').sort((a, b) => a.y3 - b.y3);
                    const minSpacing = 20;

                    [leftLabels, rightLabels].forEach(labels => {
                        for (let i = 1; i < labels.length; i++) {
                            if (labels[i].y3 - labels[i - 1].y3 < minSpacing) {
                                labels[i].y3 = labels[i - 1].y3 + minSpacing;
                                labels[i].y2 = labels[i].y3;
                            }
                        }
                    });

                    // Draw all labels
                    [...leftLabels, ...rightLabels].forEach(pos => {
                        ctx.beginPath();
                        ctx.strokeStyle = pos.color;
                        ctx.lineWidth = 1;
                        ctx.moveTo(pos.x1, pos.y1);
                        ctx.lineTo(pos.x2, pos.y2);
                        ctx.lineTo(pos.x3, pos.y3);
                        ctx.stroke();

                        ctx.fillStyle = '#333';
                        ctx.font = '12px Arial, sans-serif';
                        ctx.textAlign = pos.side === 'right' ? 'left' : 'right';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(pos.label, pos.x3 + (pos.side === 'right' ? 5 : -5), pos.y3);
                    });
                }
            }]
        });
    }

    /**
     * Initialize Stock Levels by Location Chart
     */
    function initStockChart() {
        const ctx = document.getElementById('locationStockChart');

        if (!ctx) return;

        const labels = getLocationLabels();
        if (!labels.length) {
            return;
        }

        const values = getValuesForLabels(mulopimfwc_DashboardData.stockLevels, labels);

        // Create gradient - vertical from top to bottom
        const canvas = ctx;
        const chartCtx = canvas.getContext('2d');

        const gradient = chartCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(255, 77, 77, 0.6)'); // Red at top
        gradient.addColorStop(1, 'rgba(255, 242, 242, 0)'); // Transparent at bottom

        const borderColor = '#ff4d4d';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: mulopimfwc_DashboardData.i18n.totalStock,
                    data: values,
                    backgroundColor: gradient,
                    borderColor: borderColor,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#ff4d4d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#ff4d4d',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#666'
                        },
                        title: {
                            display: true,
                            text: 'Stock Level',
                            font: {
                                size: 12
                            },
                            color: '#666'
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#333'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 11
                            },
                            color: '#666',
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 6,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    /**
     * Initialize New Products Chart
     */
    function initNewProductsChart() {
        const ctx = document.getElementById('newProductsChart');

        if (!ctx) return;

        const values = mulopimfwc_DashboardData.dateLabels.map(() => Math.floor(Math.random() * 100));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: mulopimfwc_DashboardData.dateLabels,
                datasets: [{
                    label: mulopimfwc_DashboardData.i18n.newProducts,
                    data: values,
                    fill: {
                        target: 'origin',
                        above: 'rgba(37, 99, 235, 0.08)'
                    },
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#2563eb',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            padding: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            display: false
                        },
                        grid: {
                            color: '#f3f4f6',
                            drawTicks: false
                        },
                        title: {
                            display: true,
                            text: 'Number of Products',
                            color: '#374151',
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            padding: {
                                bottom: 10
                            }
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            padding: 10,
                            stepSize: 1,
                            precision: 0,
                            callback: function (value) {
                                // Only show whole numbers
                                if (Math.floor(value) === value) {
                                    return value;
                                }
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        callbacks: {
                            label: function (context) {
                                const count = context.raw;
                                const label = mulopimfwc_DashboardData.i18n.newProducts || 'Products';
                                return label + ': ' + count + (count === 1 ? ' product' : ' products');
                            }
                        }
                    }
                }
            }
        });
    }


    /**
  * Initialize the monthly investment chart
  */
    function initInvestmentChart() {
        const ctx = document.getElementById('investment-30day');
        if (!ctx) return;

        const currency_code = mulopimfwc_DashboardData.currency_code;

        // Get currency symbol
        const currencySymbol = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency_code,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(0).replace(/\d/g, '').trim();

        const values = mulopimfwc_DashboardData.monthlyInvestmentLabels.map(() => Math.floor(Math.random() * 10000));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: mulopimfwc_DashboardData.monthlyInvestmentLabels,
                datasets: [{
                    label: mulopimfwc_DashboardData.i18n.investment,
                    data: values,
                    fill: {
                        target: 'origin',
                        above: 'rgba(37, 99, 235, 0.08)'
                    },
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#2563eb',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            padding: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            display: false,
                            dash: [5, 5]
                        },
                        grid: {
                            color: '#f3f4f6',
                            drawTicks: false
                        },
                        title: {
                            display: true,
                            text: `Investment (${currencySymbol})`,
                            color: '#374151',
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            padding: {
                                bottom: 10
                            }
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            padding: 10,
                            callback: function (value) {
                                // Format with k for thousands, M for millions
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        callbacks: {
                            label: function (context) {
                                return mulopimfwc_DashboardData.i18n.investment + ': ' +
                                    new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: currency_code,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize Orders by Location Chart
     */
    function initOrdersChart() {
        const ctx = document.getElementById('ordersByLocationChart');

        if (!ctx) return;

        const labels = getLocationLabels();
        if (!labels.length) {
            return;
        }

        const values = getRandomValues(labels.length, 0, 100);
        const bgColors = labels.map(label => mulopimfwc_DashboardData.locationColors[label] || 'rgba(153, 102, 255, 0.7)');
        const borderColors = labels.map(label => mulopimfwc_DashboardData.locationBorderColors[label] || 'rgba(153, 102, 255, 1)');

        // Calculate percentages
        const total = values.reduce((a, b) => a + b, 0);
        const percentages = values.map(v => total > 0 ? ((v / total) * 100).toFixed(1) : '0.0');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: bgColors,
                    borderColor: '#f8f9fa',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: false // Hide default legend
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.raw;
                                const percentage = percentages[context.dataIndex];
                                return `${context.label}: ${value} ${mulopimfwc_DashboardData.i18n.orders} (${percentage}%)`;
                            }
                        },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 6
                    }
                },
                layout: {
                    padding: {
                        top: 40,
                        bottom: 40,
                        left: 120,
                        right: 120
                    }
                }
            },
            plugins: [{
                // Custom plugin to draw labels with lines
                afterDraw: function (chart) {
                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;
                    const radius = Math.min(
                        (chartArea.right - chartArea.left) / 2,
                        (chartArea.bottom - chartArea.top) / 2
                    );

                    chart.data.datasets[0].data.forEach((value, index) => {
                        const meta = chart.getDatasetMeta(0);
                        const arc = meta.data[index];
                        const angle = (arc.startAngle + arc.endAngle) / 2;

                        // Line start point (at the edge of the doughnut)
                        const x1 = centerX + Math.cos(angle) * radius;
                        const y1 = centerY + Math.sin(angle) * radius;

                        // Line end point (extended outward)
                        const lineLength = 30;
                        const x2 = centerX + Math.cos(angle) * (radius + lineLength);
                        const y2 = centerY + Math.sin(angle) * (radius + lineLength);

                        // Horizontal line extension
                        const horizontalLength = 20;
                        const x3 = x2 + (Math.cos(angle) > 0 ? horizontalLength : -horizontalLength);
                        const y3 = y2;

                        // Draw the line
                        ctx.beginPath();
                        ctx.strokeStyle = bgColors[index];
                        ctx.lineWidth = 1;
                        ctx.moveTo(x1, y1);
                        ctx.lineTo(x2, y2);
                        ctx.lineTo(x3, y3);
                        ctx.stroke();

                        // Draw the label
                        const label = `${chart.data.labels[index]}: ${percentages[index]}%`;
                        ctx.fillStyle = '#333';
                        ctx.font = '12px Arial, sans-serif';
                        ctx.textAlign = Math.cos(angle) > 0 ? 'left' : 'right';
                        ctx.textBaseline = 'middle';

                        const labelX = x3 + (Math.cos(angle) > 0 ? 5 : -5);
                        ctx.fillText(label, labelX, y3);
                    });
                }
            }]
        });
    }

    /**
     * Initialize Revenue by Location Chart
     */
    function initRevenueChart() {
        const ctx = document.getElementById('revenueByLocationChart');

        if (!ctx) return;

        const labels = getLocationLabels();
        if (!labels.length) {
            return;
        }

        const values = getRandomValues(labels.length, 0, 10000);
        const bgColors = labels.map(label => mulopimfwc_DashboardData.locationColors[label] || 'rgba(75, 192, 192, 0.7)');
        const borderColors = labels.map(label => mulopimfwc_DashboardData.locationBorderColors[label] || 'rgba(75, 192, 192, 1)');
        const currency_code = mulopimfwc_DashboardData.currency_code;

        // Get currency symbol
        const currencySymbol = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency_code,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(0).replace(/\d/g, '').trim();

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: mulopimfwc_DashboardData.i18n.revenue,
                    data: values,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: 0,
                    borderRadius: 6,
                    barThickness: 50,
                    maxBarThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            display: false
                        },
                        grid: {
                            color: '#f3f4f6',
                            drawTicks: false
                        },
                        title: {
                            display: true,
                            text: `Revenue (${currencySymbol})`,
                            color: '#374151',
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            padding: {
                                bottom: 10
                            }
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            padding: 10,
                            callback: function (value) {
                                // Format with k for thousands
                                if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return mulopimfwc_DashboardData.i18n.revenue + ': ' +
                                    new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: currency_code
                                    }).format(context.raw);
                            }
                        },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });
    }

})(jQuery);
