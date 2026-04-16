<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eris Distillery - RSVP Admin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
            color: #000000;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            font-weight: 300;
            font-size: 32px;
            margin-bottom: 30px;
            border-bottom: 1px solid #000000;
            padding-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #000000;
            padding: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #666666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #ffffff;
            border: 1px solid #000000;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #f8f8f8;
            font-weight: 500;
            color: #000000;
        }
        tr:hover {
            background-color: #f8f8f8;
        }
        .export-btn {
            background: transparent;
            border: 1px solid #000000;
            color: #000000;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.2s ease;
        }
        .export-btn:hover {
            background: #000000;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>RSVP Data Admin Panel</h1>
        
        <div class="stats" id="stats">
            <!-- Stats will be loaded here -->
        </div>
        
        <button class="export-btn" onclick="exportData()">Export to CSV</button>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Guests</th>
                    <th>Discount %</th>
                    <th>Timestamp</th>
                    <th>Dietary</th>
                </tr>
            </thead>
            <tbody id="rsvp-table">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>

    <script>
        function loadRSVPData() {
            fetch('get_all_rsvp.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayStats(data.rsvp_data);
                        displayTable(data.rsvp_data);
                    } else {
                        console.error('Error loading data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayStats(rsvpData) {
            const totalRSVPs = rsvpData.length;
            const totalGuests = rsvpData.reduce((sum, rsvp) => sum + parseInt(rsvp.guests), 0);
            const avgDiscount = totalRSVPs > 0 ? 
                (rsvpData.reduce((sum, rsvp) => sum + parseInt(rsvp.discount), 0) / totalRSVPs).toFixed(1) : 0;
            
            const discountCounts = rsvpData.reduce((acc, rsvp) => {
                acc[rsvp.discount] = (acc[rsvp.discount] || 0) + 1;
                return acc;
            }, {});

            const mostCommonDiscount = Object.keys(discountCounts).reduce((a, b) => 
                discountCounts[a] > discountCounts[b] ? a : b, '20');

            document.getElementById('stats').innerHTML = `
                <div class="stat-card">
                    <div class="stat-number">${totalRSVPs}</div>
                    <div class="stat-label">Total RSVPs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${totalGuests}</div>
                    <div class="stat-label">Total Guests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${avgDiscount}%</div>
                    <div class="stat-label">Avg Discount</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${mostCommonDiscount}%</div>
                    <div class="stat-label">Most Common</div>
                </div>
            `;
        }

        function displayTable(rsvpData) {
            const tbody = document.getElementById('rsvp-table');
            tbody.innerHTML = '';

            rsvpData.forEach(rsvp => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${rsvp.id}</td>
                    <td>${rsvp.full_name}</td>
                    <td>${rsvp.email}</td>
                    <td>${rsvp.phone || '-'}</td>
                    <td>${rsvp.guests}</td>
                    <td>${rsvp.discount}%</td>
                    <td>${new Date(rsvp.timestamp).toLocaleString()}</td>
                    <td>${rsvp.dietary || '-'}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function exportData() {
            fetch('get_all_rsvp.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const csv = convertToCSV(data.rsvp_data);
                        downloadCSV(csv, 'rsvp_data.csv');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function convertToCSV(data) {
            const headers = ['ID', 'Name', 'Email', 'Phone', 'Guests', 'Discount', 'Timestamp', 'Dietary'];
            const csvContent = [
                headers.join(','),
                ...data.map(row => [
                    row.id,
                    `"${row.full_name}"`,
                    `"${row.email}"`,
                    `"${row.phone || ''}"`,
                    row.guests,
                    row.discount,
                    `"${row.timestamp}"`,
                    `"${row.dietary || ''}"`
                ].join(','))
            ].join('\n');
            return csvContent;
        }

        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadRSVPData);
    </script>
</body>
</html>



