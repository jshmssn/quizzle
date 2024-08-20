<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Animation with AJAX</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
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
            font-family: 'Press Start 2P';
        }

        .card-body {
            margin-top: 20px; 
        }

        .ranking-table {
            margin: 40px auto; 
            max-width: 80%;
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
    </style>
</head>
<body class="background-col">

<div class="align-items-center">
    <div class="card-body">
        <h1 class="text-center">Ranking</h1>
    </div>
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
                <!-- Rows will be inserted dynamically -->
            </tbody>
        </table>
    </div>

    <div class="col-md-4 mt-4">
    </div>
</div>

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
            const rankingBody = $('#ranking-body');
            rankingBody.empty();

            players.forEach((player, index) => {
                rankingBody.append(`
                    <tr id="rank-${index + 1}">
                        <td>${index + 1}</td>
                        <td>${player.name}</td>
                        <td>${player.score}</td>
                    </tr>
                `);
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
                        generateRankingRows(players);
                    } else {
                        animateRankings(players);
                    }
                },
                error: function(error) {
                    console.error('Error fetching player data:', error);
                }
            });
        }

        // Initial population of the ranking table with example player data
        generateRankingRows(initialPlayers);

        // Set up a timer to refresh and animate rankings every 5 seconds
        setInterval(fetchAndAnimateRankings, 5000);
    };
</script>

</body>
</html>
