const db = require('../config/db');

async function updatePlayerStatus(playerName, roomPin, status) {
  return new Promise((resolve, reject) => {
    const query = 'UPDATE participants SET isValid = ? WHERE name = ? AND room_pin = ?';
    db.query(query, [status, playerName, roomPin], (err, results) => {
      if (err) return reject(err);
      console.log(`Updated status for player ${playerName} in room ${roomPin} to ${status}`);
      resolve(results);
    });
  });
}

async function getPlayers(roomPin) {
  return new Promise((resolve, reject) => {
    const query = 'SELECT name FROM participants WHERE room_pin = ?';
    db.query(query, [roomPin], (err, results) => {
      if (err) return reject(err);
      resolve(results.map(row => ({ name: row.name })));
    });
  });
}

module.exports = {
  updatePlayerStatus,
  getPlayers,
};
