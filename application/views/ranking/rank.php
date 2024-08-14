<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>

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
            font-family: 'Press Start 2P', cursive;
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
                    <tr id="rank-1">
                        <td>2</td>
                        <td>Player One</td>
                        <td>1000</td>
                    </tr>
                    <tr id="rank-2">
                        <td>1</td>
                        <td>You</td>
                        <td>1100</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Player Three</td>
                        <td>850</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-4 mt-4">
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        window.onload = () => {
            // Get the rows for Player One and Player Two
            const playerOneRow = document.getElementById('rank-1');
            const playerTwoRow = document.getElementById('rank-2');

            // Add animation classes to switch places
            playerOneRow.classList.add('animate-down');
            playerTwoRow.classList.add('animate-up');

            // Remove the animation classes after the animation completes
            setTimeout(() => {
                playerOneRow.classList.remove('animate-down');
                playerTwoRow.classList.remove('animate-up');
                // Swap the rows
                const parent = playerOneRow.parentNode;
                parent.insertBefore(playerTwoRow, playerOneRow);
            }, 2000); // Match the duration of the animation
        };
    </script>
</body>
</html>
