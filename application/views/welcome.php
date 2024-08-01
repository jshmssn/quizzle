<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Welcome to WebName</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
	<style>
		body {
			margin: 0;
			padding: 0;
			height: 100vh;
			overflow: hidden;
			background: linear-gradient(45deg, #ec2d01, #6aa84f, #2986cc);
			background-size: 600% 600%;
			animation: gradientBG 10s ease infinite;
		}

		#container {
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}

		#body {
			max-width: 500px;
			width: 100%;
			text-align: center;
			padding: 20px;
			box-shadow: 0 0 10px rgba(0,0,0,0.1);
			border-radius: 8px;
			background: rgba(255, 255, 255, 0.9);
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

		input{
			text-align: center;
		}
	</style>
</head>
<body>
	<div id="container">
		<div id="body">
			<h1 class="mb-4">Welcome Players!</h1>
			<form action="">
				<div class="mb-3">
					<label for="displayName" class="form-label">Player Name</label>
					<input type="text" id="displayName" class="form-control">
				</div>
				<div class="mb-3">
					<label for="roomId" class="form-label">Room ID</label>
					<input type="number" maxlength="5" id="roomId" class="form-control">
				</div>
				<button type="submit" class="btn btn-primary">Join</button>
			</form>
			<a href="<?php echo site_url('main_controller/create'); ?>">Click here to host a room.</a>
		</div>
	</div>
	<script>
        // JavaScript to limit input length
        const input = document.getElementById('roomId');
        const maxLength = 4;

        input.addEventListener('input', () => {
            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
        });
    </script>
</body>
</html>
