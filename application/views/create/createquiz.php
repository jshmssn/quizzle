<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Welcome to WebName</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

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
	</style>
</head>
<body>
	<div id="container">
		<div id="body">
			<h1 class="mb-4">Choose a game below or <a href="<?php echo site_url('main_controller/creator'); ?>">Create your own.</a></h1>
			<button>Math Quiz</button>
			<h4>OR</h4>
			<button>Game Trivia</button><br>
			<a href="<?php echo site_url('/'); ?>">Join a room?</a>
		</div>
	</div>
</body>
</html>
