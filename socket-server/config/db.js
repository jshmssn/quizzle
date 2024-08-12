const mysql = require('mysql2');

const db = mysql.createConnection({
  host: '10.0.0.66',
  user: 'JRC',
  password: 'Mjas145326',
  database: 'quizzle',
});

db.connect((err) => {
  if (err) throw err;
  console.log('Connected to database.');
});

module.exports = db;
