const db = require('../config/db');

// Function to get a question based on roomPin
async function getQuestion(roomPin) {
  return new Promise((resolve, reject) => {
    const query = `
      SELECT id, question_text 
      FROM questions 
      WHERE room_pin = ? AND isValid = 1 
      ORDER BY RAND() 
      LIMIT 1
    `;
    db.query(query, [roomPin], (err, results) => {
      if (err) return reject(new Error('Database query failed'));
      if (results.length === 0) return resolve(null);
      resolve(results[0]);
    });
  });
}

// Function to get the correct answer for a given question ID
async function getAnswer(questionId) {
  return new Promise((resolve, reject) => {
    const query = `
      SELECT answer_text 
      FROM answers 
      WHERE question_id = ? AND is_correct = 1
    `;
    db.query(query, [questionId], (err, results) => {
      if (err) return reject(new Error('Database query failed'));
      if (results.length === 0) return resolve(null);
      resolve(results[0]);
    });
  });
}

// Function to get all answers based on question ID
async function getAnswers(questionId) {
  return new Promise((resolve, reject) => {
    const query = `
      SELECT id, answer_text 
      FROM answers 
      WHERE question_id = ?
    `;
    db.query(query, [questionId], (err, results) => {
      if (err) return reject(new Error('Database query failed'));
      resolve(results);
    });
  });
}

// Function to get the question time based on roomPin
async function getQuestionTime(roomPin) {
  return new Promise((resolve, reject) => {
    const query = `
      SELECT time 
      FROM questions 
      WHERE room_pin = ? AND isValid = 1 
    `;
    db.query(query, [roomPin], (err, results) => {
      if (err) return reject(new Error('Database query failed'));
      if (results.length === 0) return resolve(null);
      resolve(results[0].time);
    });
  });
}

module.exports = {
  getQuestion,
  getAnswer,
  getAnswers,     // Added this line
  getQuestionTime
};
