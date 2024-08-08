<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Quizzle</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js" integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" integrity="sha512-DIW4FkYTOxjCqRt7oS9BFO+nVOwDL4bzukDyDtMO7crjUZhwpyrWBFroq+IqRe6VnJkTpRAS6nhDvf0w+wHmxg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="text/javascript"> 
        // Disable right-click context menu
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        // Disable developer tools shortcuts
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey && e.shiftKey && e.keyCode == 73) || // Prevent Ctrl+Shift+I
                (e.ctrlKey && e.shiftKey && e.keyCode == 74) || // Prevent Ctrl+Shift+J
                (e.ctrlKey && e.keyCode == 85) ||              // Prevent Ctrl+U
                (e.keyCode == 123)) {                          // Prevent F12
                e.preventDefault();
                return false;
            }
        });
    </script>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background-color: #cfcfcf;
            background-size: 600% 600%;
            animation: gradientBG 10s ease infinite;
        }

        #container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        #body {
            max-width: 500px;
            width: 100%;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
        }

        img {
            max-width: 100%;
            height: auto;
            width: 350px; /* Adjust size as needed */
            margin-bottom: 20px;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 0%; }
            25% { background-position: 100% 0%; }
            50% { background-position: 100% 100%; }
            75% { background-position: 0% 100%; }
            100% { background-position: 0% 0%; }
        }

        button {
            padding: 15px 30px;
            font-size: 16px;
            margin: 10px;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        input {
            text-align: center;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div id="container">
        <img src="<?php echo base_url('assets/images/logo.png'); ?>" class="img-fluid" alt="Logo">
        <div id="body">
            <h1 class="mb-4">Welcome Players!</h1>
            <form action="<?php echo site_url('main_controller/join'); ?>" method="post" id="joinForm">
                <div class="mb-3">
                    <label for="displayName" class="form-label">Player Name</label>
                    <input type="text" name="name" id="displayName" class="form-control" required aria-required="true">
                </div>
                <div class="mb-3">
                    <label for="roomId" class="form-label">Room PIN</label>
                    <input type="number" name="room_pin" maxlength="4" id="roomId" class="form-control" required aria-required="true">
                </div>
                <button type="submit" class="btn btn-primary">Join</button>
            </form>
            <a href="<?php echo site_url('/create'); ?>">Click here to host a room.</a>
        </div>
    </div>
    <script>
        // JavaScript to limit input length
        document.getElementById('roomId').addEventListener('input', function() {
            this.value = this.value.slice(0, 4);
        });

        // Check for flash data
        <?php if($this->session->flashdata("status") == "error"): ?>
            iziToast.error({
                title: 'Error:',
                message: <?php echo json_encode($this->session->flashdata("msg")); ?>,
                position: 'topCenter',
                overlay: true
            });
        <?php endif; ?>
    </script>
</body>
</html>
