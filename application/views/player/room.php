<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Area</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .centered-container {
            text-align: center;
        }
        .form-control {
            width: 100%;
        }
        .players-box {
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .players-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .player-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            color: #343a40;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            flex: 1 1 150px;
            max-width: 200px;
        }
        @media (max-width: 768px) {
            .player-card {
                flex: 1 1 100px;
                max-width: 150px;
            }
        }
        @media (max-width: 576px) {
            .player-card {
                flex: 1 1 80px;
                max-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php if ($this->session->flashdata('status') === 'success'): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif ($this->session->flashdata('status') === 'error'): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->userdata('roomPin')): ?>
            <h3 class="mb-4 text-center">Room PIN</h3>
            <div class="form-group">
                <input type="text" class="form-control text-center form-control-lg" value="<?php echo htmlspecialchars($this->session->userdata('roomPin'), ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <br>
            <h3 class="mb-4 text-center">Players</h3>
            <div class="centered-container mt-4">
                <form action="<?php echo site_url('main_controller/start_game'); ?>" method="post">
                    <input type="hidden" name="room_pin" value="<?php echo htmlspecialchars($this->session->userdata('roomPin'), ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="players-box">
                        <div id="players-container" class="players-container">
                            <!-- Player cards will be updated here -->
                        </div>
                    </div>
                </form>
                <h3 class="mt-4">Waiting for the host to start the game.</h3>
                <a style="font-weight: 700;" href="<?php echo site_url('main_controller/leftroom') ?>" type="#">Left Room</a>

            </div>
        <?php else: ?>
            <p class="alert alert-warning">Room PIN could not be retrieved.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Define a flag to control logging
        const SHOW_LOGS = false; // Set to `true` to enable logging

        // Custom logging function
        function customLog(message) {
            if (SHOW_LOGS) {
                console.log(message);
            }
        }

        // Function to fetch players
        function fetchPlayers() {
            $.ajax({
                url: '<?php echo site_url("main_controller/get_players"); ?>', // Replace with your endpoint URL
                method: 'GET',
                success: function(data) {
                    try {
                        // Parse JSON if not already parsed
                        var response = typeof data === 'string' ? JSON.parse(data) : data;

                        // Log the response to check its structure
                        customLog(response);

                        // Check if 'players' is an array
                        if (Array.isArray(response.players)) {
                            // Clear current player cards
                            $('#players-container').empty();

                            // Update player cards with new data
                            if (response.players.length > 0) {
                                response.players.forEach(function(player) {
                                    $('#players-container').append('<div class="player-card">' + $('<div>').text(player.name).html() + '</div>');
                                });
                            } else {
                                $('#players-container').append('<div class="player-card">No participants yet.</div>');
                            }
                        } else {
                            console.error('Invalid response format.');
                        }
                    } catch (error) {
                        console.error('Failed to parse response:', error);
                    }
                },
                error: function() {
                    console.error('Failed to fetch players.');
                }
            });
        }

        // Fetch players every 3 seconds
        setInterval(fetchPlayers, 3000);

        // Initial fetch
        fetchPlayers();
    </script>
</body>
</html>
