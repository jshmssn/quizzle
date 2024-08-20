<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking in SweetAlert</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.3/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
      /* Base styles */
html, body {
    height: 100%; 
    margin: 0; 
    overflow: hidden; 
}

.quiz-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.background-col {
    background: #528352;
}

/* Apply the custom font */
body, .card-title, .ranking-btn {
    font-family: 'Press Start 2P', cursive;
}

.ranking-table {
    margin: 20px auto;
    max-width: 100%;
}

.ranking-table table {
    width: 100%;
    border-collapse: collapse;
}

.ranking-table th, .ranking-table td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

.ranking-table th {
    background-color: #f8f9fa;
}

.ranking-table tr:hover {
    background-color: #f1f1f1;
}

.ranking-table tr {
    transition: transform 2s ease;
}

.ranking-table tr.animate-up {
    animation: moveUp 2s forwards;
}

.ranking-table tr.animate-down {
    animation: moveDown 2s forwards;
}

/* Keyframe animations */
@keyframes moveUp {
    0% {
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        transform: translateY(-100%);
        opacity: 0;
    }
}

@keyframes moveDown {
    0% {
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        transform: translateY(100%);
        opacity: 0;
    }
}

/* Media query for tablets and small devices (max-width: 768px) */
@media (max-width: 768px) {
    .ranking-table th, .ranking-table td {
        font-size: 12px; /* Adjust the font size for smaller screens */
        padding: 8px;    /* Reduce padding for smaller screens */
    }

    .ranking-table th {
        font-size: 14px; /* Slightly larger font size for table headers on smaller screens */
    }

    .ranking-table td {
        font-size: 12px; /* Smaller font size for table data on smaller screens */
    }
}

/* Media query for mobile devices (max-width: 480px) */
@media (max-width: 480px) {
    .ranking-table th, .ranking-table td {
        font-size: 10px; /* Further reduce the font size for very small screens */
        padding: 5px;    /* Further reduce padding for very small screens */
    }

    .ranking-table th {
        font-size: 12px; /* Keep the header font slightly larger */
    }

    .ranking-table td {
        font-size: 10px; /* Keep the data font small for readability */
    }
}

    </style>
</head>
<body class="background-col">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.onload = () => {
        // Example player data
        const initialPlayers = [
            { name: "Player 1", score: 10 },
            { name: "Player 2", score: 20 },
            { name: "Player 3", score: 15 }
        ];

        // Function to generate the table rows based on player data
        function generateRankingRows(players) {
            let rows = '';

            players.forEach((player, index) => {
                rows += `
                    <tr id="rank-${index + 1}">
                        <td>${index + 1}</td>
                        <td>${player.name}</td>
                        <td>${player.score}</td>
                    </tr>
                `;
            });

            return rows;
        }

        // Function to show ranking in SweetAlert
        function showRankingAlert() {
            const rankingTable = `
                <div class="ranking-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody id="ranking-body">
                            ${generateRankingRows(initialPlayers)}
                        </tbody>
                    </table>
                </div>
            `;

            Swal.fire({
                title: 'Ranking',
                html: rankingTable,
                width: 600,
                padding: '2em',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: `
                    rgba(0,0,123,0.4)
                    left top
                    no-repeat
                `
            });
        }

        // Function to animate ranking changes
        function animateRankings(players) {
            const rows = $('#ranking-body tr').get();

            // Sort the rows based on the new player data
            rows.sort((a, b) => {
                const scoreA = parseInt($(a).find('td:nth-child(3)').text());
                const scoreB = parseInt($(b).find('td:nth-child(3)').text());
                return scoreB - scoreA; // Sort by score descending
            });

            rows.forEach((row, index) => {
                const $row = $(row);
                const currentRank = parseInt($row.find('td:nth-child(1)').text());
                const newRank = index + 1;

                if (newRank < currentRank) {
                    $row.addClass('animate-up');
                } else if (newRank > currentRank) {
                    $row.addClass('animate-down');
                }

                setTimeout(() => {
                    $row.removeClass('animate-up animate-down');
                    $row.find('td:nth-child(1)').text(newRank); // Update rank
                }, 2000);
            });

            // Append rows in correct order
            rows.forEach(row => $('#ranking-body').append(row));
        }

        // Function to fetch player data and update the rankings
        function fetchAndAnimateRankings() {
            // Example: Simulating an AJAX call to fetch updated player data
            $.ajax({
                url: '/get-updated-player-data', // Replace with your actual data URL
                method: 'GET',
                success: function(data) {
                    const players = data; // Assuming 'data' is the array of players

                    // Generate initial rows if not already present
                    if ($('#ranking-body').children().length === 0) {
                        $('#ranking-body').html(generateRankingRows(players));
                    } else {
                        animateRankings(players);
                    }
                },
                error: function(error) {
                    console.error('Error fetching player data:', error);
                }
            });
        }

        // Show initial ranking in SweetAlert
        showRankingAlert();

        // Set up a timer to refresh and animate rankings every 5 seconds
        setInterval(fetchAndAnimateRankings, 5000);
    };
</script>

</body>
</html>
