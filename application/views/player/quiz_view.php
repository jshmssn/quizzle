<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .content {
            width: 70%;
            padding: 20px;
            background-color: #fff;
        }
        .question {
            margin-bottom: 20px;
        }
        .question h2 {
            font-size: 24px;
            margin: 0;
            color: #007bff;
        }
        .timer {
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .answers button {
            margin: 5px;
            padding: 10px 15px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .answers button:hover {
            background-color: #007bff;
            color: #fff;
        }
        .answers .selected {
            background-color: #007bff;
            color: #fff;
        }
        .waiting-message, .correct-answer {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            display: none;
        }
        .waiting-message {
            color: #ffc107;
        }
        .correct-answer {
            color: #28a745;
        }
        .player-list {
            flex: 1;
            padding: 20px;
            border-right: 2px solid #e0e0e0;
        }
        .player-list h3 {
            margin-top: 0;
            color: #444;
            font-size: 22px;
        }
        .player-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .player-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            color: #555;
            display: flex;
            justify-content: space-between;
        }
        .player-list li.highlighted {
            background-color: #e0f7fa;
            font-weight: bold;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
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
<body>
    <div class="container">
        <div class="player-list">
            <h3>Player List - Scores</h3>
            <ul>
                <?php foreach($players as $player): ?>
                    <li class="<?= ($player['name'] === $correct_answer) ? 'highlighted' : '' ?>">
                        <?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?>
                        <span><?= htmlspecialchars($player['scores'], ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="content">
            <div class="question">
                <h2><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></h2>
                <input type="hidden" id="questionId" value="<?= htmlspecialchars($question_id, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="timer" id="timer">Time left: --</div>
            <div class="answers">
                <?php foreach($answers as $answer): ?>
                    <button class="answer-button" data-answer="<?= htmlspecialchars($answer['answer_text'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($answer['answer_text'], ENT_QUOTES, 'UTF-8') ?></button>
                <?php endforeach; ?>
            </div>
            <div class="waiting-message" id="waitingMessage">Waiting for other players to answer...</div>
            <div class="correct-answer" id="correctAnswer">The correct answer is: <?= htmlspecialchars($correct_answer, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timerElement = document.getElementById('timer');
            const waitingMessage = document.getElementById('waitingMessage');
            const correctAnswer = document.getElementById('correctAnswer');
            const questionId = document.getElementById('questionId').value;

            // Fetch the timer and question details
            async function fetchQuestionDetails() {
                try {
                    const response = await fetch(`http://localhost:3000/get-time?questionId=${questionId}`);
                    const data = await response.json();

                    if (response.ok) {
                        const endTime = data.endTime;
                        startTimer(endTime);
                    } else {
                        console.error('Failed to fetch question details:', data.error);
                        timerElement.innerHTML = "Error fetching timer.";
                    }
                } catch (error) {
                    console.error('Error fetching question details:', error);
                    timerElement.innerHTML = "Error fetching timer.";
                }
            }

            // Function to start the timer
            function startTimer(endTime) {
                const timerInterval = setInterval(() => {
                    const timeLeft = Math.max(Math.floor((endTime - Date.now()) / 1000), 0);
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        timerElement.innerHTML = "Time's up!";
                        displayCorrectAnswer();
                    } else {
                        if (timeLeft >= 60) {
                            const minutes = Math.floor(timeLeft / 60);
                            const remainingSeconds = timeLeft % 60;
                            timerElement.innerHTML = `Time left: ${minutes}m ${remainingSeconds}s`;
                        } else {
                            timerElement.innerHTML = `Time left: ${timeLeft}s`;
                        }
                    }
                }, 1000);
            }

            // Function to display the correct answer
            function displayCorrectAnswer() {
                correctAnswer.style.display = 'block';
            }

            // Function to handle answer selection
            function handleAnswerSelection() {
                document.querySelectorAll('.answer-button').forEach(button => {
                    button.addEventListener('click', function() {
                        // Mark selected answer
                        document.querySelectorAll('.answer-button').forEach(btn => btn.classList.remove('selected'));
                        this.classList.add('selected');

                        // Show waiting message
                        waitingMessage.style.display = 'block';

                        // Disable all answer buttons
                        document.querySelectorAll('.answer-button').forEach(btn => btn.disabled = true);
                    });
                });
            }

            // Fetch and start the timer when the page loads
            fetchQuestionDetails();

            // Initialize answer selection handling
            handleAnswerSelection();
        });
    </script>
</body>
</html>
