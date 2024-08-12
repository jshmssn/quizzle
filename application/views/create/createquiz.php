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
	<script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
</head>
<body>
	<div class="container text-center">
		<div class="image-wrapper">
            <img src="<?= base_url('assets/images/logo.png') ?>" class="img-fluid" alt="Logo">
        </div>
		<div id="body" class="mt-4">
			<h2 class="mb-4">Choose a game below or <br><a href="<?php echo site_url('/quiz_creator'); ?>">Create your own.</a></h2>
			<a href="#" class="btn btn-light">Math Quiz</a>
			<h4 class="mt-4 mb-4">OR</h4>
			<a href="#" class="btn btn-light">Trivia</a>
			<br>
			<a href="<?php echo site_url('/'); ?>">Join a room?</a>
		</div>
	</div>
</body>
</html>
