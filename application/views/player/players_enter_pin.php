<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Room PIN</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .centered-container {
            text-align: center;
        }
        .form-control {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4 text-center">Enter Room PIN</h3>
        <div class="centered-container">
            <form action="<?php echo site_url('main_controller/enter_room_pin'); ?>" method="post">
                <div class="form-group">
                    <input type="text" name="room_pin" class="form-control form-control-lg" placeholder="Enter Room PIN" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Submit</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
