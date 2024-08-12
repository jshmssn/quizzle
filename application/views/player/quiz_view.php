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
        .answers button.disabled {
            background-color: #e0e0e0;
            color: #888;
            cursor: not-allowed;
        }
        .answers button.selected {
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
        /* Overlay and timer styles */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 1000;
            flex-direction: column;
        }
        .overlay.hidden {
            display: none;
        }
        .countdown {
            font-size: 48px;
            margin-top: 20px;
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
            (e.ctrlKey && e.ctrlKey && e.keyCode == 85) || // Prevent Ctrl+U
            (e.keyCode == 123)) { // Prevent F12
            e.preventDefault();
            return false;
        }
    });
</script>
<body>
    <div class="overlay" id="overlay">
        <div id="countdown" class="countdown"></div>
        <div>Preparing...</div>
    </div>
    <div class="container">
        <div class="player-list">
            <h3>Player List - Scores</h3>
            <ul id="playerList">
                <!-- Player list will be populated here -->
            </ul>
        </div>
        <div class="content">
            <div class="question">
                <h2 id="questionText">Loading question...</h2>
                <input type="hidden" id="questionId">
            </div>
            <div class="timer" id="timer">Time left: --</div>
            <div class="answers" id="answers">
                <!-- Answers will be populated here -->
            </div>
            <div class="waiting-message" id="waitingMessage">Waiting for other players to answer...</div>
            <div class="correct-answer" id="correctAnswer">The correct answer is: </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // WebSocket URL, adjust as necessary
        const socketUrl = `ws://${window.location.hostname}:3000`;
        const socket = new WebSocket(socketUrl);

        let isSocketOpen = false;
        let alertShown = false;
        let correctAnswer = '';

        // Get roomPin from URL query parameters
        function getRoomPin() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('roomPin');
        }

        const roomPin = getRoomPin();

        // Handle WebSocket open event
        socket.onopen = function() {
            console.log('WebSocket connection established.');
            isSocketOpen = true;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('overlay');
            const countdownElement = document.getElementById('countdown');
            const timerElement = document.getElementById('timer');
            const waitingMessage = document.getElementById('waitingMessage');
            const correctAnswerElement = document.getElementById('correctAnswer');
            const questionIdInput = document.getElementById('questionId');
            const playerList = document.getElementById('playerList');
            const answersElement = document.getElementById('answers');

            if (!roomPin) {
                console.error('Room pin is required.');
                return;
            }

            // Start countdown from 5 seconds
            let countdown = 5;
            const countdownInterval = setInterval(() => {
                countdownElement.textContent = countdown;
                countdown--;
                if (countdown < 0) {
                    clearInterval(countdownInterval);
                    overlay.classList.add('hidden');
                    fetchQuestionDetails();
                    fetchPlayers();
                }
            }, 1000);

            // Fetch players from the server
            async function fetchPlayers() {
                try {
                    const response = await fetch(`http://localhost:3000/api/get_players?room_pin=${roomPin}`);
                    const data = await response.json();
                    
                    if (response.ok) {
                        const players = data.players;
                        playerList.innerHTML = players.map(player => 
                            `<li class="${player.name === correctAnswer ? 'highlighted' : ''}">
                                ${player.name}
                                <span>${player.scores}</span>
                            </li>`
                        ).join('');
                    } else {
                        console.error('Failed to fetch players:', data.error);
                    }
                } catch (error) {
                    console.error('Error fetching players:', error);
                }
            }

            // Fetch question details and answers
            async function fetchQuestionDetails() {
                try {
                    const response = await fetch(`http://localhost:3000/api/get_question?room_pin=${roomPin}`);
                    const data = await response.json();

                    if (response.ok) {
                        const { question_text, answer_text, question_id } = data;
                        document.getElementById('questionText').textContent = question_text;
                        questionIdInput.value = question_id;

                        // Fetch answers based on question_id
                        fetchAnswers(question_id);
                        document.getElementById('correctAnswer').textContent = `The correct answer is: ${answer_text}`;
                        
                        // Fetch question time and start the game timer
                        const questionTime = await fetchQuestionTime();
                        startGameTimer(questionTime);
                    } else {
                        console.error('Failed to fetch question details:', data.error);
                    }
                } catch (error) {
                    console.error('Error fetching question details:', error);
                }
            }

            // Fetch answers based on question ID
            async function fetchAnswers(questionId) {
                try {
                    const response = await fetch(`http://localhost:3000/api/get-answers?question_id=${questionId}&room_pin=${roomPin}`);
                    const data = await response.json();

                    if (response.ok) {
                        const answers = data.answers;
                        answersElement.innerHTML = answers.map(answer => 
                            `<button data-answer-id="${answer.id}">${answer.answer_text}</button>`
                        ).join('');

                        // Add click event listeners to answer buttons
                        document.querySelectorAll('.answers button').forEach(button => {
                            button.addEventListener('click', () => {
                                const selectedAnswerId = button.getAttribute('data-answer-id');
                                submitAnswer(selectedAnswerId, button);
                            });
                        });
                    } else {
                        console.error('Failed to fetch answers:', data.error);
                    }
                } catch (error) {
                    console.error('Error fetching answers:', error);
                }
            }
            
            // Fetch question time
            async function fetchQuestionTime() {
                try {
                    const response = await fetch(`http://localhost:3000/api/get-question-time?room_pin=${roomPin}`);
                    const data = await response.json();

                    if (response.ok) {
                        return data.time;
                    } else {
                        console.error('Failed to fetch question time:', data.error);
                    }
                } catch (error) {
                    console.error('Error fetching question time:', error);
                }
                return 0;
            }

            // Start the game timer
            function startGameTimer(duration) {
                const endTime = Date.now() + duration * 1000;
                const timerInterval = setInterval(() => {
                    const remainingTime = Math.max(0, endTime - Date.now());
                    const minutes = Math.floor(remainingTime / 60000);
                    const seconds = Math.floor((remainingTime % 60000) / 1000);
                    timerElement.textContent = `Time left: ${minutes}:${seconds.toString().padStart(2, '0')}`;

                    if (remainingTime <= 0) {
                        clearInterval(timerInterval);
                        timerElement.textContent = 'Time is up!';
                        showCorrectAnswer();
                    }
                }, 1000);
            }

            // Show the correct answer
            function showCorrectAnswer() {
                correctAnswerElement.textContent = `The correct answer is: ${correctAnswer}`;
                correctAnswerElement.style.display = 'block';
                waitingMessage.style.display = 'none';
            }

            // Submit the selected answer
            function submitAnswer(selectedAnswer, selectedButton) {
                if (socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify({ action: 'submit_answer', answer: selectedAnswer }));
                    
                    // Disable all answer buttons and highlight the selected one
                    document.querySelectorAll('.answers button').forEach(button => {
                        button.disabled = true;  // Disable button
                        if (button !== selectedButton) {
                            button.classList.add('disabled');
                        } else {
                            button.classList.add('selected');
                        }
                    });
                    
                    waitingMessage.style.display = 'block';
                } else {
                    console.error('WebSocket is not open.');
                }
            }

            socket.onmessage = function(event) {
                const data = JSON.parse(event.data);

                if (data.action === 'update_players') {
                    updatePlayerList(data.players);
                }

                if (data.action === 'show_correct_answer') {
                    correctAnswer = data.correct_answer;
                    waitingMessage.style.display = 'none';
                    showCorrectAnswer();
                }
            };

            function updatePlayerList(players) {
                playerList.innerHTML = players.map(player => 
                    `<li>${player.name} - ${player.scores}</li>`
                ).join('');
            }
        });
    </script>
</body>
</html>
