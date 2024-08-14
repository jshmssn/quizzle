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
    <script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
    <style>
        body {
            font-family: 'Press Start 2P', cursive;
            background-color: #f8f9fa;
        }
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
    const socket = new WebSocket(socketUrl);

    let isSocketOpen = false;

    function getRoomId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('roomId');
    }

    let roomId = getRoomId();

    socket.onopen = function() {
        console.log('WebSocket connection established.');
        isSocketOpen = true;
    };

    socket.onerror = function(error) {
        console.error('WebSocket Error:', error);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const overlay = document.getElementById('overlay');
        const countdownTimer = document.getElementById('countdown-timer');
        const questionTextElement = document.getElementById('question-text');
        const answersElement = document.getElementById('answers');
        const speakButton = document.getElementById('speak-button');
        const questionNumberElement = document.getElementById('question-number');
        const countdownBar = document.getElementById('countdown-bar');
        const imageContainer = document.getElementById('image-container');

        if (!roomId) {
            console.error('Room id is required.');
            return;
        }

        let countdown = 2;
        const countdownInterval = setInterval(() => {
            countdownTimer.textContent = countdown;
            countdownTimer.style.opacity = 1;
            countdown--;
            if (countdown < 0) {
                clearInterval(countdownInterval);
                countdownTimer.style.opacity = 0;
                setTimeout(() => {
                    overlay.classList.add('hidden');
                    fetchQuestions();
                }, 1000);
            }
        }, 1000);

        let totalQuestions = 0; // Initialize totalQuestions

        async function fetchQuestions() {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_questions')?>',
                    type: 'POST',
                    data: { room_id: roomId },
                    dataType: 'json'
                });
                if (response.status === 'success') {
                    totalQuestions = response.data.length; // Set totalQuestions
                    console.log('Questions:', response.data);

                    response.data.forEach((question, index) => {
                        const questionId = question.id;
                        const questionText = question.question_text;
                        const questTime = question.time;
                        const questImage =question.image_path;

                        if (questionText) {
                            questionTextElement.textContent = questionText;
                        } else {
                            console.error('Question text not found in response.');
                        }

                        console.log(questImage);
                        // Update the question number display
                        questionNumberElement.textContent = `Question ${index + 1} out of ${totalQuestions}`;

                        fetchAnswers(questionId, questTime, totalQuestions); // Pass totalQuestions to fetchAnswers
                        fetchCorrectAnswers(questionId);
                        loadImage(questionId);
                    });
                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        async function fetchAnswers(questionId, questTime, totalQuestions) {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_answers')?>',
                    type: 'POST',
                    data: { question_id: questionId },
                    dataType: 'json'
                });
                if (response.status === 'success') {
                    console.log('Answers:', response.data);

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

                    startCountdown(questTime);

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
                    console.log('Correct Answer:', response.data);

                    const correctAnswer = document.getElementById('correctAnswer');
                    if (correctAnswer) {
                        correctAnswer.innerHTML = ''; // Clear any previous content

                        // Assuming response.data is an array of correct answers
                        response.data.forEach(answer => {
                            const answerText = answer.answer_text; // Adjust this if your data structure is different
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
            const countdownBar = document.querySelector('.countdown-bar');
            
            // Update the countdown bar width and display the remaining time
            const countdownInterval = setInterval(() => {
                // Calculate the percentage width for the countdown bar
                const width = (timeLeft / duration) * 100 + '%';
                countdownBar.style.width = width;
                
                // Decrease the time left
                timeLeft--;
                
                // If time is up
                if (timeLeft < 0) {            
                    clearInterval(countdownInterval);
                    showAnswer();
                }
            }, 1000);
        }

        function showAnswer(){
            // Show waiting message and hide correct answer
            const waitingMessage = document.getElementById('waitingMessage');
                const correctAnswer = document.getElementById('correctAnswer');
                const answerButtons = document.querySelectorAll('.btn.answer-btn');

                waitingMessage.style.display = 'none';
                correctAnswer.style.display = 'block';
                correctAnswer.removeAttribute('hidden');

            answerButtons.forEach(btn => {
                btn.classList.add('disabled');
                btn.disabled = true;
            });
        }
        function loadImage(questId) {
            $.ajax({
                url: '<?= site_url('main_controller/get_image_path') ?>',
                type: 'POST',
                dataType: 'json',
                data: { questId: questId },
                success: function(response) {
                    if (response.imagePath) {
                        // Set the image source
                        $('.image-container img').attr('src', '<?= base_url() ?>' + response.imagePath);
                    } else {
                        // Handle case where no image path is returned
                        $('.image-container img').attr('src', ''); // Clear the image source
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
