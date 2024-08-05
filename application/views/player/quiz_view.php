<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <style>
        /* Basic styling */
        .container { display: flex; }
        .player-list { width: 20%; }
        .content { width: 80%; padding: 20px; }
        .question { margin-bottom: 20px; }
        .timer { font-size: 24px; margin-bottom: 20px; }
        .answers button { margin: 5px; }
        .waiting-message, .correct-answer { margin-top: 20px; font-size: 18px; font-weight: bold; display: none; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="container">
        <div class="player-list">
            <form action="">
                <input type="hidden" name="room_pin" value="<?php echo htmlspecialchars($this->session->userdata('roomPin'), ENT_QUOTES, 'UTF-8'); ?>">
            </form>
            <h3>Player List - Score</h3>
            <ul>
                <?php foreach($players as $player): ?>
                    <li><?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($player['scores'], ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="content">
            <div class="question">
                <h2><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <div class="timer" id="timer">Time left: 00</div>
            <div class="answers">
                <?php foreach($answers as $answer): ?>
                    <button class="answer-button" data-answer="<?= htmlspecialchars($answer['answer_text'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($answer['answer_text'], ENT_QUOTES, 'UTF-8') ?></button>
                <?php endforeach; ?>
            </div>
            <div class="waiting-message" id="waitingMessage">Waiting for other players to answer...</div>
            <div class="correct-answer" id="correctAnswer">The correct answer is: <?= htmlspecialchars($correct_answer, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Define a flag to control logging
        const SHOW_LOGS = false; // Set to `true` to enable logging

        // Flag to store the SweetAlert2 instance
        let swalInstance = null;

        // Custom logging function
        function customLog(message) {
            if (SHOW_LOGS) {
                console.log(message);
            }
        }

        let timeLeft = 10;
        let timerExpired = false;
        const timerElement = document.getElementById('timer');
        const waitingMessage = document.getElementById('waitingMessage');
        const correctAnswer = document.getElementById('correctAnswer');
        let alertShown = false;

        // Function to display the correct answer
        function displayCorrectAnswer() {
            correctAnswer.style.display = 'block';
        }

        // Function to handle answer submission
        function handleAnswerSubmission() {
            // Disable all answer buttons
            document.querySelectorAll('.answer-button').forEach(button => button.disabled = true);

            // Check the number of players
            const playerCount = <?php echo count($players); ?>;

            if (timerExpired) {
                // Timer has run out, do not display waiting message
                proceedToNextQuestion();
            } else if (playerCount > 1) {
                // More than one player, display waiting message
                waitingMessage.style.display = 'block';
            } else {
                // Only one player, proceed to the next question
                proceedToNextQuestion();
            }
        }

        // Function to proceed to the next question
        function proceedToNextQuestion() {
            // Code to fetch and display the next question
            console.log("Proceeding to the next question...");
            // You might need to make an AJAX request or redirect here
        }

        // Function to check room status
        function checkIfQuizStarted() {
            var pin = $('input[name="room_pin"]').val();

            $.ajax({
                url: '<?php echo site_url("main_controller/get_room_status"); ?>',
                method: 'GET',
                data: { pin: pin },
                success: function(data) {
                    try {
                        var response = typeof data === 'string' ? JSON.parse(data) : data;
                        customLog(response);
                        
                        if (response.hasStarted === "0" && !alertShown) {
                            swalInstance = Swal.fire({
                                title: "The room is not available.",
                                text: "Click okay to leave.",
                                showDenyButton: false,
                                showCancelButton: false,
                                confirmButtonText: "Okay",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "<?php echo site_url('/'); ?>";
                                }
                            });
                            alertShown = true;
                        }
                    } catch (error) {
                        console.error('Failed to parse response:', error);
                    }
                },
                error: function() {
                    console.error('Failed to fetch room status.');
                }
            });
        }

        // Event listener for answer buttons
        document.querySelectorAll('.answer-button').forEach(button => {
            button.addEventListener('click', () => {
                handleAnswerSubmission();
                // Additional code to handle answer submission can be added here
            });
        });

        // Timer countdown logic
        const timer = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timer);
                timerExpired = true;
                timerElement.innerHTML = "Time's up!";
                displayCorrectAnswer();
                handleAnswerSubmission(); // Ensure this is called when time runs out
            } else {
                timerElement.innerHTML = `Time left: ${timeLeft}`;
            }
            timeLeft--;
        }, 1000);

        // Check if the quiz has started every second
        setInterval(checkIfQuizStarted, 1000);
    </script>
</body>
</html>
