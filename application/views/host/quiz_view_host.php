<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Host View</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f4f8;
            color: #333;
        }
        .container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
        .content {
            flex: 3;
            padding: 20px;
        }
        .question {
            margin-bottom: 20px;
        }
        .question h2 {
            font-size: 26px;
            color: #444;
            margin: 0;
        }
        .timer {
            font-size: 24px;
            color: #d32f2f;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .answers button {
            background-color: #0288d1;
            border: none;
            color: #fff;
            padding: 12px 24px;
            font-size: 18px;
            margin: 5px;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .answers button.correct {
            background-color: #388e3c;
            pointer-events: none;
        }
        .waiting-message, .correct-answer {
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
        }
        .waiting-message {
            color: #fbc02d;
            display: none;
        }
        .correct-answer {
            color: #2e7d32;
            display: none;
        }
    </style>
</head>
<script type="text/javascript"> 
    function disableRightClick() {  
        return false; 
    } 
</script>
<body oncontextmenu="return disableRightClick()">
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
            </div>
            <div class="timer" id="timer">Time left: 00</div>
            <div class="answers">
                <?php foreach($answers as $answer): ?>
                    <button class="answer-button <?= ($answer === $correct_answer) ? 'correct' : '' ?>" data-answer="<?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="waiting-message" id="waitingMessage">Waiting for other players to answer...</div>
            <div class="correct-answer" id="correctAnswer">The correct answer is: <?= htmlspecialchars($correct_answer, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timerElement = document.getElementById('timer');
            const waitingMessage = document.getElementById('waitingMessage');
            const correctAnswer = document.getElementById('correctAnswer');
            let questionId = 33; // Replace with the actual question ID or set dynamically

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

            // Fetch and start the timer when the page loads
            fetchQuestionDetails();
        });
    </script>
</body>
</html>
