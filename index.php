<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bee Occurrence Map & Chart</title>
    
    <!-- jQuery & Chart.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        #chartContainer { width: 500px; margin: auto; }
    </style>
</head>
<body>

<h4>Occurrences Per Month here</h4>

<!-- Chart Container -->
<div id="chartContainer">
    <canvas id="occurrenceChart"></canvas>
</div>

<script>
$(document).ready(function () {
    const sortedMonths = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    // Create chart with zero data initially
    const ctx = document.getElementById("occurrenceChart").getContext("2d");
    const chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: sortedMonths,
            datasets: [{
                label: "Occurrences",
                data: new Array(12).fill(0), // Start with zero data
                backgroundColor: "rgba(75, 192, 192, 0.5)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            animation: false, // Disable animations for instant rendering
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Function to process and update chart data
    function processChartData(data) {
        if (!data || !data.results) return;
        
        const occurrencesByMonth = data.results.reduce((acc, item) => {
            if (item.eventDate) {
                const month = new Date(item.eventDate).toLocaleString("default", { month: "short" });
                acc[month] = (acc[month] || 0) + 1;
            }
            return acc;
        }, {});

        // Use requestAnimationFrame for smooth updates
        requestAnimationFrame(() => {
            chart.data.datasets[0].data = sortedMonths.map(month => occurrencesByMonth[month] || 0);
            chart.update();
        });

        // Save image once the chart is updated
        setTimeout(saveChartAsImage, 2000);
    }

    // Function to fetch data efficiently
    function fetchData(apiUrl) {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => processChartData(data))
            .catch(error => console.error("Error fetching data:", error));
    }

    // API URL for occurrence data
    const apiUrl = "https://api.gbif.org/v1/occurrence/search?q=Trypoxylon&country=US&hasCoordinate=true&hasGeospatialIssue=false&occurrenceStatus=present&limit=500";

    // Fetch data & update chart
    fetchData(apiUrl);

    // Function to save the chart image automatically
    function saveChartAsImage() {
        const canvas = document.getElementById("occurrenceChart");
        const image = canvas.toDataURL("image/png");

        // Send the image to the server via AJAX
        $.ajax({
            url: "save_image.php",
            type: "POST",
            data: {
                image: image
            },
            success: function(response) {
                console.log("Image saved successfully:", response);
            },
            error: function(xhr, status, error) {
                console.error("Error saving image:", error);
            }
        });
    }
});
</script>

</body>
</html>
