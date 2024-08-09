const express = require('express');
const playerService = require('../services/playerService');
const questionService = require('../services/questionService');

const router = express.Router();

// Middleware to extract roomPin from query parameters
router.use((req, res, next) => {
  req.roomPin = req.query.room_pin;
  if (!req.roomPin) {
    return res.status(400).json({ error: 'Room pin is required' });
  }
  next();
});

// Route to get players
router.get('/get_players', (req, res) => {
  const roomPin = req.roomPin;

  if (!roomPin) {
      return res.status(400).json({ error: 'Room pin is required' });
  }

  // Fetch players based on roomPin
  playerService.getPlayers(roomPin).then((players) => {
      res.json({ players });
  }).catch((err) => {
      console.error('Error fetching players:', err);
      res.status(500).json({ error: 'Internal server error' });
  });
});

// Route to get a question and its correct answer
router.get('/get_question', (req, res) => {
  const roomPin = req.roomPin;

  questionService.getQuestion(roomPin).then((question) => {
    if (!question) {
      return res.status(404).json({ error: 'Question not found' });
    }

    questionService.getAnswer(question.id).then((answer) => {
      res.json({
        question_text: question.question_text,
        answer_text: answer ? answer.answer_text : null,
        question_id: question.id,
      });
    }).catch((err) => {
      res.status(500).json({ error: 'Internal server error' });
    });
  }).catch((err) => {
    res.status(500).json({ error: 'Internal server error' });
  });
});

// Route to get question time
router.get('/get-question-time', (req, res) => {
  const roomPin = req.roomPin;

  questionService.getQuestionTime(roomPin).then((time) => {
    if (time === null) {
      return res.status(404).json({ error: 'Time not found' });
    }

    res.json({ time });
  }).catch((err) => {
    res.status(500).json({ error: 'Internal server error' });
  });
});

// Route to get answers based on question ID
router.get('/get-answers', (req, res) => {
  const questionId = req.query.question_id;
  const roomPin = req.query.room_pin; // Ensure room_pin is also extracted

  if (!questionId) {
    return res.status(400).json({ error: 'Question ID is required' });
  }

  if (!roomPin) {
    return res.status(400).json({ error: 'Room pin is required' });
  }

  questionService.getAnswers(questionId, roomPin).then((answers) => {
    res.json({ answers });
  }).catch((err) => {
    console.error('Error fetching answers:', err);
    res.status(500).json({ error: 'Internal server error' });
  });
});

module.exports = router;
