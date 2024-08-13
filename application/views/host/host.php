<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Area</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
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
                max-width: 150px;
            }
        }
        .disabled {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
</head>
<script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
<body>
    <div class="container text-center">
        <div class="image-wrapper">
            <img src="<?php echo base_url('assets/images/logo.png'); ?>" class="img-fluid centered-image" alt="Logo">
        </div>
        <?php if ($this->session->userdata('room_pin')): ?>
            <h4 class="mb-3 text-center">Room PIN</h4>
            <div class="form-group">
                <input type="text" id="room-pin" class="form-control text-center form-control-lg" value="<?php echo htmlspecialchars($this->session->userdata('room_pin'), ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <br>
            <h4 class="mb-3 text-center">Players Joined</h4>
            <div class="centered-container mt-4">
                <form id="form-start" action="<?php echo site_url('main_controller/start'); ?>" method="post">
                    <input type="hidden" name="room_pin" value="<?php echo htmlspecialchars($this->session->userdata('room_pin'), ENT_QUOTES, 'UTF-8'); ?>">         
                    <div class="players-box">
                        <div id="players-container" class="players-container">
                            <!-- Player cards will be updated here -->
                        </div>
                    </div>

                    <button type="button" id="start-button" class="btn btn-light btn-lg mt-4">Start Game</button><br>
                    <a href="<?php echo site_url('main_controller/quitroom') ?>" type="#">Cancel Room</a>
                </form>
            </div>
        <?php else: ?>
            <p class="alert alert-warning">Room PIN could not be retrieved.</p>
            <a href="<?php echo site_url('/') ?>" type="#">Go Home</a>
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

        // Get the room pin and player name
        const roomPin = document.getElementById('room-pin').value;
        const playerName = "";

        // WebSocket URL, adjust as necessary
        const socketUrl = `ws://${window.location.hostname}:3000`;
        const socket = new WebSocket(socketUrl);

        let isSocketOpen = false;

        // Handle WebSocket open event
        socket.onopen = function() {
            console.log('WebSocket connection established.');
            isSocketOpen = true;
            sendJoinRoomRequest();
        };

        // Handle WebSocket message event
        socket.onmessage = function(event) {
            console.log('Received WebSocket message:', event.data);
            try {
                const message = JSON.parse(event.data);

                if (message.type === 'updatePlayers') {
                    updatePlayers(message.players);
                    iziToast.success({
                        title: 'Notice',
                        message: 'A player has joined the room.',
                        position: 'topRight'
                    });
                } else if (message.type === 'leftPlayer') {
                    updatePlayers(message.players);
                    iziToast.error({
                        title: 'Notice',
                        message: 'A player has left the room.',
                        position: 'topRight'
                    });
                } else if (message.type === 'roomStatus') {
                    handleRoomStatus(message);
                }
            } catch (e) {
                console.error('Error processing WebSocket message:', e);
            }
        };

        // Handle WebSocket close event
        socket.onclose = function() {
            console.log('WebSocket connection closed.');
            isSocketOpen = false;
        };

        // Handle WebSocket error event
        socket.onerror = function(error) {
            console.error('WebSocket error:', error);
        };

        // Send request to join room
        function sendJoinRoomRequest() {
            if (isSocketOpen) {
                const joinMessage = JSON.stringify({ type: 'joinRoom', pin: roomPin, playerName: playerName });
                socket.send(joinMessage);
            }
        }

        // Update player list and button state
        function updatePlayers(players) {
            $('#players-container').empty();
            if (players && players.length > 0) {
                players.forEach(function(player) {
                    const displayName = player.name;
                    $('#players-container').append('<div class="player-card">' + $('<div>').text(displayName).html() + '</div>');
                });
                $('#start-button').removeClass('disabled'); // Enable the button
            } else {
                $('#players-container').append('<div class="player-card">No players yet.</div>');
                $('#start-button').addClass('disabled'); // Disable the button
            }
        }

        // Handle room status updates
        function handleRoomStatus(message) {
            if (message.hasStarted === 1 && message.isValid === 0) {
                window.location.href = "<?php echo site_url('/start_game_host'); ?>";
            } else if (message.isValid === 0) {
                console.warn("Room is not available. Redirecting...");
                window.location.href = "<?php echo site_url('main_controller/index'); ?>";
            }
        }

        $('#start-button').on('click', function(e) {
        e.preventDefault();

        const actionUrl = $('#form-start').attr('action');
        const roomPin = $('#room-pin').val();

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: { room_pin: roomPin },
                success: function(response) {
                    // Handle the response
                },
                error: function(xhr, status, error) {
                    console.error('Error starting game:', error);
                    iziToast.error({
                        title: 'Error',
                        message: 'Failed to start the game. Please try again.',
                        position: 'topRight'
                    });
                }
            });
        });

        // Optional: Periodic polling if WebSocket is not open (for debugging or fallback)
        setInterval(function() {
            if (!isSocketOpen) {
                console.error('WebSocket is not open. Polling might be needed.');
            }
        }, 500);
    </script>
</body>
</html>
