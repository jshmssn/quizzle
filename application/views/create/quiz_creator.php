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
            padding-top: 20px; /* Ensure there's space at the top */
        }
        #container {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-height: 100vh; /* Ensure the container fills the viewport height */
        }
        .answer-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .answer-container > div {
            flex: 1;
        }
        .quiz-set {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            text-align: center;
            line-height: 50px;
            font-size: 24px;
            cursor: pointer;
        }
        .scroll-to-top:hover {
            background-color: #0056b3;
        }
        .remove-btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="body">
            <h1 class="mb-4">Create your quiz</h1>
            <form id="quiz-form" method="POST" action="<?php echo site_url('main_controller/submit'); ?>">
                <div id="quiz-container">
                    <div class="quiz-set mb-5" data-index="1">
                        <div class="mb-3">
                            <label for="question-1" class="form-label">Question 1</label>
                            <input type="text" id="question-1" name="questions[1][text]" class="form-control question" placeholder="Enter question" required>
                        </div>
                        <div class="answer-container">
                            <div class="mb-3">
                                <label class="form-label">Answer 1</label>
                                <input type="text" name="questions[1][answers][0]" class="form-control answer" placeholder="Answer 1" required>
                                <input type="radio" name="questions[1][correct]" value="0" required> Correct
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Answer 2</label>
                                <input type="text" name="questions[1][answers][1]" class="form-control answer" placeholder="Answer 2" required>
                                <input type="radio" name="questions[1][correct]" value="1" required> Correct
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Answer 3</label>
                                <input type="text" name="questions[1][answers][2]" class="form-control answer" placeholder="Answer 3" required>
                                <input type="radio" name="questions[1][correct]" value="2" required> Correct
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Answer 4</label>
                                <input type="text" name="questions[1][answers][3]" class="form-control answer" placeholder="Answer 4" required>
                                <input type="radio" name="questions[1][correct]" value="3" required> Correct
                            </div>
                        </div>
                    </div>
                </div>
                <button id="add-quiz" type="button" class="btn btn-primary">Add more quiz</button>
                <button id="submit-button" type="submit" class="btn btn-success">Submit</button>
                <button id="home-button" class="btn btn-danger">Cancel</button>
            </form>
        </div>
    </div>
    <button id="scroll-to-top" class="scroll-to-top">↑</button>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Are you sure?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel your quiz creation? All unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <a href="<?php echo site_url('main_controller/create'); ?>" id="confirm-home" class="btn btn-danger">Yes, cancel and return to quiz selection</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script>
        let quizIndex = 1;
        const colors = ['#f8d7da', '#d4edda', '#d1ecf1', '#fff3cd', '#f5f5f5'];

        document.getElementById('add-quiz').addEventListener('click', function() {
            quizIndex++;
            const quizContainer = document.getElementById('quiz-container');
            
            const newQuizSet = document.createElement('div');
            newQuizSet.className = 'quiz-set mb-5';
            newQuizSet.dataset.index = quizIndex;
            newQuizSet.style.backgroundColor = colors[(quizIndex - 1) % colors.length];
            newQuizSet.innerHTML = `
                <div class="mb-3">
                    <label for="question-${quizIndex}" class="form-label">Question ${quizIndex}</label>
                    <input type="text" id="question-${quizIndex}" name="questions[${quizIndex}][text]" class="form-control question" placeholder="Enter question" required>
                </div>
                <div class="answer-container">
                    <div class="mb-3">
                        <label class="form-label">Answer 1</label>
                        <input type="text" name="questions[${quizIndex}][answers][0]" class="form-control answer" placeholder="Answer 1" required>
                        <input type="radio" name="questions[${quizIndex}][correct]" value="0" required> Correct
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer 2</label>
                        <input type="text" name="questions[${quizIndex}][answers][1]" class="form-control answer" placeholder="Answer 2" required>
                        <input type="radio" name="questions[${quizIndex}][correct]" value="1" required> Correct
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer 3</label>
                        <input type="text" name="questions[${quizIndex}][answers][2]" class="form-control answer" placeholder="Answer 3" required>
                        <input type="radio" name="questions[${quizIndex}][correct]" value="2" required> Correct
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer 4</label>
                        <input type="text" name="questions[${quizIndex}][answers][3]" class="form-control answer" placeholder="Answer 4" required>
                        <input type="radio" name="questions[${quizIndex}][correct]" value="3" required> Correct
                    </div>
                </div>
                <button class="btn btn-danger remove-btn">Remove</button>
            `;

            quizContainer.appendChild(newQuizSet);
            randomizeAnswers();
        });

        // Remove button functionality
        document.getElementById('quiz-container').addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('remove-btn')) {
                if (confirm("Are you sure you want to remove Question #" + quizIndex + "?")) {
                    event.target.parentElement.remove();
                }
            }
        });

        // Randomize Answer Slots
        function randomizeAnswers() {
            document.querySelectorAll('.quiz-set').forEach((quizSet) => {
                const radios = quizSet.querySelectorAll('input[type="radio"]');
                const answers = Array.from(radios).map(radio => radio.parentElement);
                const randomizedAnswers = answers.sort(() => Math.random() - 0.5);
                
                randomizedAnswers.forEach((answer, index) => {
                    answer.querySelector('input[type="radio"]').value = index;
                });
            });
        }

        // Scroll to Top Button Functionality
        const scrollToTopButton = document.getElementById('scroll-to-top');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollToTopButton.style.display = 'block';
            } else {
                scrollToTopButton.style.display = 'none';
            }
        });

        scrollToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Handle Home Button Click
        document.getElementById('home-button').addEventListener('click', (event) => {
            event.preventDefault();
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            confirmModal.show();
        });

        // Initialize randomization on page load
        window.addEventListener('load', randomizeAnswers);
    </script>
</body>
</html>
