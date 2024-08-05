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
</head>
<body>
    <div class="container">
        <div class="player-list">
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
                    <button class="answer-button" data-answer="<?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></button>
                <?php endforeach; ?>
            </div>
            <div class="waiting-message" id="waitingMessage">Waiting for other players to answer...</div>
            <div class="correct-answer" id="correctAnswer">The correct answer is: <?= htmlspecialchars($correct_answer, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <script>
        let timeLeft = 10;
        let timerExpired = false;
        const timerElement = document.getElementById('timer');
        const waitingMessage = document.getElementById('waitingMessage');
        const correctAnswer = document.getElementById('correctAnswer');

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
            // You can use AJAX or redirect to another page
            console.log("Proceeding to the next question...");
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
    </script>
</body>
</html>
