<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Area</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
    <style>
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
    <div class="container text-center">
        <div class="image-wrapper">
            <img src="<?php echo base_url('assets/images/logo.png'); ?>" class="img-fluid centered-image" alt="Logo">
        </div>        
        <div id="flash-messages"></div>
        <div id="room-info">
            <h3 class="mb-3 text-center">Room PIN</h3>
            <div class="form-group">
                <input type="text" class="form-control text-center form-control-lg" id="room-pin" value="<?php echo $this->session->userdata('room_pin'); ?>" readonly>
            </div>
            <h3 class="mb-3 text-center">Players</h3>
            <div class="centered-container mt-4">
                <div class="players-box">
                    <div id="players-container" class="players-container"></div>
                </div>
                <h3 class="mt-2">Waiting for the host to start the game.</h3>
                <a style="font-weight: 700;" id="left-room" href="<?php echo site_url('main_controller/leftroom') ?>" class="btn btn-light">Leave Room</a>
            </div>
        </div>
        <p id="no-room-pin" class="alert alert-warning d-none">Room PIN could not be retrieved.</p>
    </div>

    <script type="text/javascript">
        // Get the room pin and player name
        const roomPin = document.getElementById('room-pin').value;
        const playerName = "<?php echo $this->session->userdata('player_name'); ?>";

        // WebSocket URL, adjust as necessary
        const socketUrl = `ws://${window.location.hostname}:3000`;
        const socket = new WebSocket(socketUrl);

        let isSocketOpen = false;
        let alertShown = false;

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
                } else if (message.type === 'reconnected') {
                    iziToast.info({
                        title: 'Notice',
                        message: message.message,
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
            // Attempt to reconnect
            setTimeout(() => {
                if (!isSocketOpen) {
                    location.reload(); // Reload the page to attempt reconnection
                }
            }, 3000); // Adjust the delay as necessary
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

        // Update player list
        function updatePlayers(players) {
            $('#players-container').empty();
            if (players && players.length > 0) {
                players.forEach(function(player) {
                    const isCurrentPlayer = player.name.trim() === playerName.trim();
                    const displayName = isCurrentPlayer ? `${player.name} (You)` : player.name;
                    $('#players-container').append('<div class="player-card">' + $('<div>').text(displayName).html() + '</div>');
                });
            } else {
                $('#players-container').append('<div class="player-card">No players yet.</div>');
            }
        }

        // Handle room status updates
        function handleRoomStatus(message) {
            if (message.hasStarted === 1 && message.isValid === 0) {
                window.location.href = `<?php echo site_url('/start_game'); ?>?roomPin=${roomPin}`;
            } else if (message.isValid === 0 && !alertShown) {
                Swal.fire({
                    title: "Room is not available.",
                    text: "Click okay to leave.",
                    icon: 'warning',
                    confirmButtonText: "Okay",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "<?php echo site_url('main_controller/index'); ?>";
                    }
                });
                alertShown = true;
            }
        }

        // Optional: Periodic polling if WebSocket is not open (for debugging or fallback)
        setInterval(function() {
            if (!isSocketOpen) {
                console.error('WebSocket is not open. Polling might be needed.');
            }
        }, 500);
    </script>
</body>
</html>
