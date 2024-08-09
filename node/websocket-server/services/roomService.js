const db = require('../config/db');

async function getRoomStatus(roomPin) {
  return new Promise((resolve, reject) => {
    const query = 'SELECT isValid, hasStarted FROM rooms WHERE pin = ?';
    db.query(query, [roomPin], (err, results) => {
      if (err) return reject(err);
      if (results.length > 0) {
        resolve(results[0]);
      } else {
        resolve({ isValid: 0, hasStarted: 0 });
      }
    });
  });
}

module.exports = {
  getRoomStatus,
};
