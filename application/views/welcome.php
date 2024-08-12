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
    <script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
</head>
<body>
    <div class="container text-center">
        <div class="image-wrapper">
            <img src="<?= base_url('assets/images/logo.png') ?>" class="img-fluid" alt="Logo">
        </div>        
        <h1 class="mb-4">Welcome Players!</h1>
        <form action="<?php echo site_url('main_controller/join'); ?>" method="post" id="joinForm">
            <div class="mb-3">
                <label for="roomId" class="form-label">Room PIN</label>
                <input type="number" name="room_pin" maxlength="4" id="roomId" class="form-control" required aria-required="true">
            </div>    
            <div class="mb-3">
                <label for="displayName" class="form-label">Player Name</label>
                <input type="text" name="name" id="displayName" class="form-control" required aria-required="true">
            </div>
            <button type="submit" class="btn btn-light">Join</button>
        </form>
        <a href="<?php echo site_url('/create'); ?>">Click here to host a room.</a>
    </div>
    <script>
        // JavaScript to limit input length
        document.getElementById('roomId').addEventListener('input', function() {
            this.value = this.value.slice(0, 5);
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
