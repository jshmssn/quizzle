<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Setup</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .centered-container {
            text-align: center;
        }
        .form-control {
            width: 100%;
        }
        .textarea-container {
            min-height: 150px; /* Adjust based on your design needs */
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

        <?php if (!empty($room_pin)): ?>
            <h3 class="mb-4 text-center">Room PIN</h3>
            <div class="form-group">
                <input type="text" class="form-control text-center form-control-lg" value="<?php echo htmlspecialchars($room_pin, ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>

            <div class="centered-container mt-4">
                <form action="<?php echo site_url('main_controller/start_game'); ?>" method="post">
                    <input type="hidden" name="room_pin" value="<?php echo htmlspecialchars($room_pin, ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="form-group">
                        <textarea id="participants-list" class="form-control textarea-container" readonly>
                            <!-- MAKE ME A CONTROLLER THAT WILL FETCH PARTIPANTS LIST EVERY 3 SECONDS AND UPDATE THE TEXTAREA WITHOUT REFREHSHING THE PAGE -->
                        </textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Start Game</button>
                </form>
            </div>
        <?php else: ?>
            <p class="alert alert-warning">No room or participants data found.</p>
        <?php endif; ?>
    </div>
    <script>
        function fetchParticipants() {
            $.ajax({
                url: "<?php echo site_url('main_controller/get_participants'); ?>", // Controller method URL
                method: "GET",
                success: function(data) {
                    $('#participants-list').val(data);
                },
                error: function(xhr, status, error) {
                    console.error("Failed to fetch participants: " + error);
                }
            });
        }

        $(document).ready(function() {
            // Fetch participants initially and then every 3 seconds
            fetchParticipants();
            setInterval(fetchParticipants, 3000);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
