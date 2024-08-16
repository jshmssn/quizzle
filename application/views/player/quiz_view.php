<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Choice</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>
    <!-- Sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #cc0000;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            font-weight: bold;
            z-index: 9999;
            transition: opacity 1s ease-out;
        }
        .overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .countdown-timer {
            font-size: 3rem;
            font-weight: bold;
            transition: opacity 0.5s ease;
        }
        .overlay-text {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        #question-text {
            font-size: 1.5rem;
            line-height: 1.4;
            text-align: left; /* Align text to the left */
            margin: 0; /* Remove margin to align with button */
        }
        .question-container {
            display: flex;
            align-items: center; /* Vertically center align items */
            gap: 10px; /* Space between question text and button */
        }
        #answers {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn.answer-btn {
            font-size: 1.1rem;
            padding: 15px;
        }
        .btn.selected {
            background-color: #28a745;
            color: #fff;
        }
        .btn.disabled {
            background-color: #6c757d;
            color: #fff;
            pointer-events: none;
        }
        .question-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #cc0000;
            border: 2px solid #cc0000;
            padding: 10px;
            border-radius: 8px;
            background-color: #fff;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        .image-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            margin: 20px 0; /* Add vertical spacing */
        }
        .image-container img {
            max-width: 100%; /* Ensure responsiveness */
            height: auto; /* Maintain aspect ratio */
            border-radius: 10px;
        }
        .countdown-bar {
            width: 100%;
            height: 5px;
            background-color: #28a745;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: width 1s linear;
        }
        @media (max-width: 768px) {
            #question-text {
                font-size: 1.25rem;
            }
            .btn.answer-btn {
                font-size: 1rem;
            }
        }
        @media (max-width: 576px) {
            #question-text {
                font-size: 1rem;
            }
            .btn.answer-btn {
                font-size: 0.9rem;
            }
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay">
    <div class="overlay-text">Starting in:</div>
    <div class="countdown-timer" id="countdown-timer"></div>
</div>

<div class="container mt-3">
    <div class="row justify-content-between">
        <div class="col-12">
            <div class="question-number text-dark">Player 
                <?= $this->session->userdata('player_name') ?>
            </div>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <div class="question-number">
                <div class="countdown-bar" id="countdown-bar"></div>
                <span id="question-number">Loading...</span>
            </div>
        </div>
    </div>
</div>
<div class="container mt-3">
    <div class="row">
        <div class="col-12 col-md-8">
            <div class="card-body">
                <div class="question-container">
                    <h1 id="question-text" class="fit-text">Loading question...</h1>
                    <button id="speak-button" class="btn btn-link">
                        <i class="fas fa-volume-up"></i>
                    </button>
                </div>
            </div>
            <div class="image-container">
                <!-- Image will be fetched from the database -->
                <img src="" class="img-fluid" alt="Question Image" />
            </div>
        </div>
        <div class="col-12 col-md-4 mt-3">
            <div id="answers">
                <!-- Answer buttons will be dynamically inserted here -->
            </div>
            <div id="fill-in-the-blank" hidden>
                <input type="text" id="answer-input" class="form-control" placeholder="Type your answer here...">
                <button id="submit-answer" class="btn btn-primary btn-block mt-2">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Will display Waiting and Correct answer after all players have answered -->
<div id="waitingMessage" class="text-center mt-3" hidden>Waiting for other players to answer...</div>
<div id="correctAnswer" class="text-center mt-3" hidden>The correct answer is </div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- WebSocket and AJAX Script -->
<script>
    const socketUrl = `${window.location.protocol === 'https:' ? 'wss' : 'ws'}://${window.location.hostname}:3000`;
    let socket = null;
    let isSocketOpen = false;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5; // Adjust as needed

    function getRoomId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('roomId');
    }

    let roomId = getRoomId();

    function connectWebSocket() {
        socket = new WebSocket(socketUrl);

        socket.onopen = function() {
            console.log('WebSocket connection established.');
            isSocketOpen = true;
            reconnectAttempts = 0; // Reset attempts on successful connection
        };

        socket.onclose = function() {
            console.log('WebSocket connection closed.');
            isSocketOpen = false;
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                setTimeout(connectWebSocket, 3000); // Attempt to reconnect after 3 seconds
            } else {
                console.error('Max reconnect attempts reached. Please refresh the page.');
            }
        };

        socket.onerror = function(error) {
            console.error('WebSocket Error:', error);
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        connectWebSocket();

        const overlay = document.getElementById('overlay');
        const countdownTimer = document.getElementById('countdown-timer');
        const questionTextElement = document.getElementById('question-text');
        const answersElement = document.getElementById('answers');
        const speakButton = document.getElementById('speak-button');
        const questionNumberElement = document.getElementById('question-number');
        const countdownBar = document.getElementById('countdown-bar');
        const fillInTheBlankContainer = document.getElementById('fill-in-the-blank');
        const answerInput = document.getElementById('answer-input');
        const submitAnswerButton = document.getElementById('submit-answer');

        if (!roomId) {
            console.error('Room id is required.');
            return;
        }

        let countdown = 5;
        const countdownInterval = setInterval(() => {
            if (countdown > 0) {
                countdownTimer.textContent = countdown;
            }

            countdown--;

            if (countdown < 0) {
                clearInterval(countdownInterval);

                overlay.style.transition = "opacity 1s ease-out";
                overlay.style.opacity = 0;

                setTimeout(() => {
                    overlay.classList.add('hidden');
                    fetchQuestions();
                }, 1000);
            }
        }, 1000);


        let questions = [];
        let currentQuestionIndex = 0;

        async function fetchQuestions() {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_questions')?>',
                    type: 'POST',
                    data: { room_id: roomId },
                    dataType: 'json'
                });
                if (response.status === 'success') {
                    questions = response.data;
                    if (questions.length > 0) {
                        displayQuestion(questions[currentQuestionIndex]);
                    } else {
                        console.error('No questions found.');
                    }
                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        // Function to display a question
        function displayQuestion(question) {
            if (!question) return;

            // console.log('Question Data:', question); // Debugging line

            // Display question text and number
            questionTextElement.textContent = question.question_text || 'Loading question...';
            questionNumberElement.textContent = `Question ${currentQuestionIndex + 1} out of ${questions.length}`;
            
            // Show image and fetch answers if necessary
            loadImage(question.id);
            fetchAnswers(question.id);
            fetchCorrectAnswers(question.id);
            startCountdown(question.time);

            // Ensure submitAnswerButton is enabled for the new question
            submitAnswerButton.removeAttribute('disabled');

            // Handle display based on question type
            if (question.isFill === '1') {
                answersElement.style.display = 'none';
                fillInTheBlankContainer.removeAttribute('hidden');
                fillInTheBlankContainer.style.display = 'block'; // Ensure it is visible
                submitAnswerButton.removeEventListener('click', handleFillInTheBlankAnswer); // Remove previous handlers if any
                submitAnswerButton.addEventListener('click', () => handleFillInTheBlankAnswer(answerInput.value, question.id));
            } else {
                fillInTheBlankContainer.style.display = 'none'; // Hide fill-in-the-blank section
                answersElement.style.display = 'flex';
            }
        }

        function handleFillInTheBlankAnswer(answerText, questionId) {
            const waitingMessage = document.getElementById('waitingMessage');
            waitingMessage.removeAttribute('hidden');
            waitingMessage.style.display = 'block';

            if (isSocketOpen) {
                const data = {
                    type: 'answer_selected',
                    answerText: answerText,
                    questionId: questionId,
                    roomId: roomId
                };
                socket.send(JSON.stringify(data));
                console.log('Selected answer data sent via WebSocket:', data);
            } else {
                console.error('Socket connection is not open.');
            }

            answerInput.value = '';
            fillInTheBlankContainer.setAttribute('hidden', 'true');
            waitingMessage.style.display = 'block';
        }

        async function fetchAnswers(questionId) {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_answers')?>',
                    type: 'POST',
                    data: { question_id: questionId },
                    dataType: 'json'
                });
                if (response.status === 'success') {
                    // console.log('Answers:', response.data);

                    answersElement.innerHTML = '';

                    response.data.forEach(answer => {
                        const answerButton = document.createElement('button');
                        answerButton.classList.add('btn', 'answer-btn', 'red', 'btn-block');
                        answerButton.textContent = answer.answer_text;

                        answerButton.addEventListener('click', function() {
                            handleAnswerSelection(answerButton, answer.id, questionId);
                        });

                        answersElement.appendChild(answerButton);
                    });
                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        async function fetchCorrectAnswers(questionId) {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_correct_answers')?>',
                    type: 'POST',
                    data: { question_id: questionId },
                    dataType: 'json'
                });

                if (response.status === 'success') {
                    // console.log('Correct Answer:', response.data);

                    const correctAnswer = document.getElementById('correctAnswer');
                    if (correctAnswer) {
                        correctAnswer.innerHTML = ''; // Clear any previous content
                        
                        response.data.forEach(answer => {
                            const answerText = answer.answer_text; // Adjust this if your data structure is different
                            // console.log(answer.answer_text);
                            correctAnswer.innerHTML += `<h3>The correct answer is <span style="color: #cc0000;">${answerText}</span></h3>`;
                        });
                    } else {
                        console.error('Element with id "correctAnswer" not found.');
                    }

                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        function startCountdown(duration) {
            let timeLeft = duration;

            // Retrieve the remaining time from localStorage if it exists
            const storedTime = localStorage.getItem('countdownTime');
            if (storedTime) {
                timeLeft = parseInt(storedTime, 10);
                localStorage.removeItem('countdownTime'); // Remove the stored time after use
            }

            const countdownBar = document.querySelector('.countdown-bar');
            const submitAnswerButton = document.getElementById('submit-answer'); // Ensure you have this element

            // Function to update the countdown bar width and remaining time display
            function updateCountdown() {
                const width = (timeLeft / duration) * 100 + '%';
                countdownBar.style.width = width;
                // Optionally update a display element with timeLeft here
            }

            // Start the countdown
            const countdownInterval = setInterval(() => {
                updateCountdown();
                
                // Save the remaining time to localStorage
                localStorage.setItem('countdownTime', timeLeft);
                
                // Decrease the time left
                timeLeft--;
                
                // If time is up
                if (timeLeft < 0) {
                    clearInterval(countdownInterval);
                    submitAnswerButton.setAttribute('disabled', 'true');
                    showAnswer();
                    localStorage.removeItem('countdownTime'); // Clear the stored time
                }
            }, 1000);

            // Initial update of the countdown bar
            updateCountdown();
        }

        function showAnswer() {
            const waitingMessage = document.getElementById('waitingMessage');
            const correctAnswer = document.getElementById('correctAnswer');
            const answerButtons = document.querySelectorAll('.btn.answer-btn');

            // Hide the waiting message and show the correct answer
            waitingMessage.style.display = 'none';
            correctAnswer.style.display = 'block';
            correctAnswer.removeAttribute('hidden');

            // Disable answer buttons
            answerButtons.forEach(btn => {
                btn.classList.add('disabled');
                btn.disabled = true;
            });

            // Move to the next question after a delay
            setTimeout(() => {
                // Hide the correct answer before moving to the next question
                correctAnswer.style.display = 'none';
                
                currentQuestionIndex++;
                if (currentQuestionIndex < questions.length) {
                    displayQuestion(questions[currentQuestionIndex]);
                } else {
                    // Handle the end of the quiz
                    // console.log('Quiz completed!');
                    Swal.fire({
                        title: "Good job!",
                        text: "The quiz is now done.",
                        icon: "success",
                        confirmButtonText: "Go to ranking"
                    }).then((result) => {
                        /* Read more about isConfirmed */
                        if (result.isConfirmed) {
                            
                        }
                    });
                }
            }, 3000); // Delay to show the correct answer before moving to the next question
        }

        
        function loadImage(questId) {
            $.ajax({
                url: '<?= site_url('main_controller/get_image_path') ?>',
                type: 'POST',
                dataType: 'json',
                data: { questId: questId },
                success: function(response) {
                    if (response.imagePath) {
                        // Set the image source and show the image container
                        $('.image-container img').attr('src', '<?= base_url() ?>' + response.imagePath);
                        $('.image-container').removeClass('hidden');
                    } else {
                        // Clear the image source and hide the image container
                        $('.image-container img').attr('src', '');
                        $('.image-container').addClass('hidden');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }

        function handleAnswerSelection(button, answerId, questionId) {
            const answerButtons = document.querySelectorAll('.btn.answer-btn');
            const waitingMessage = document.getElementById('waitingMessage');

            answerButtons.forEach(btn => {
                btn.classList.add('disabled');
                btn.disabled = true;
                waitingMessage.removeAttribute('hidden');
                waitingMessage.style.display = 'block';
            });

            button.classList.remove('disabled');
            button.classList.add('selected');

            if (isSocketOpen) {
                const data = {
                    type: 'answer_selected',
                    answerId: answerId,
                    questionId: questionId,
                    roomId: roomId
                };
                socket.send(JSON.stringify(data));
                console.log('Selected answer data sent via WebSocket:', data);
            } else {
                console.error('Socket connection is not open.');
            }
        }

        speakButton.addEventListener('click', () => {
            const questionText = questionTextElement.textContent;
            const speech = new SpeechSynthesisUtterance(questionText);
            speechSynthesis.speak(speech);
        });
    });
</script>
</body>
</html>
