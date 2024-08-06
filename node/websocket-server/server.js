const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const mysql = require('mysql2');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

// Middleware
app.use(bodyParser.json());
app.use(cors());
app.use(express.static('public'));

// Database connection
const db = mysql.createConnection({
  host: '10.0.0.66',
  user: 'JRC',
  password: 'Mjas145326',
  database: 'quizzle'
});

db.connect((err) => {
  if (err) throw err;
  console.log('Connected to database.');
});

// Store client information
const clients = new Map();

// WebSocket event handling
wss.on('connection', (ws) => {
  console.log('A new client has connected.');

  ws.on('message', async (message) => {
    console.log(`Received: ${message}`);
    const msg = JSON.parse(message);

    if (msg.type === 'joinRoom') {
      const roomPin = msg.pin;
      const playerName = msg.playerName;

      clients.set(ws, { roomPin, playerName });

      try {
        const players = await getPlayers(roomPin);
        const roomStatus = await getRoomStatus(roomPin);

        const updatePlayersMsg = JSON.stringify({ type: 'updatePlayers', players: players });
        const roomStatusMsg = JSON.stringify({ type: 'roomStatus', ...roomStatus });

        wss.clients.forEach((client) => {
          if (client.readyState === WebSocket.OPEN) {
            client.send(updatePlayersMsg);
            client.send(roomStatusMsg);
          }
        });
      } catch (err) {
        console.error('Error handling join room request:', err);
      }
    }

    wss.clients.forEach((client) => {
      if (client !== ws && client.readyState === WebSocket.OPEN) {
        client.send(message);
      }
    });
  });

  ws.on('close', async () => {
    console.log('A client has disconnected.');

    const clientInfo = clients.get(ws);
    if (clientInfo) {
      const { roomPin, playerName } = clientInfo;

      try {
        await removePlayerFromDatabase(playerName, roomPin);

        clients.delete(ws);

        const players = await getPlayers(roomPin);
        const updateMsg = JSON.stringify({ type: 'updatePlayers', players: players });
        wss.clients.forEach((client) => {
          if (client.readyState === WebSocket.OPEN) {
            client.send(updateMsg);
          }
        });
      } catch (err) {
        console.error('Error removing player from database:', err);
      }
    }
  });
});

// Periodically broadcast room status updates
function broadcastRoomStatus(roomPin) {
  getRoomStatus(roomPin).then(roomStatus => {
    const roomStatusMsg = JSON.stringify({ type: 'roomStatus', ...roomStatus });
    wss.clients.forEach((client) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(roomStatusMsg);
      }
    });
  }).catch(err => console.error('Error broadcasting room status:', err));
}

setInterval(() => {
  clients.forEach((client, ws) => {
    broadcastRoomStatus(client.roomPin);
  });
}, 5000);

// API routes
app.get('/api/get_room_status', (req, res) => {
  console.log('GET /api/get_room_status hit');
  const roomPin = req.query.pin;

  if (!roomPin) {
    return res.json({ isValid: 0, hasStarted: 0 });
  }

  const query = 'SELECT isValid, hasStarted FROM rooms WHERE pin = ?';
  db.query(query, [roomPin], (err, results) => {
    if (err) throw err;
    if (results.length > 0) {
      res.json(results[0]);
    } else {
      res.json({ isValid: 0, hasStarted: 0 });
    }
  });
});

app.get('/api/get_players', (req, res) => {
  console.log('GET /api/get_players hit');
  const roomPin = req.query.room_pin;
  if (!roomPin) {
    return res.json({ players: [] });
  }

  const query = 'SELECT name FROM participants WHERE room_pin = ?';
  db.query(query, [roomPin], (err, results) => {
    if (err) throw err;
    res.json({ players: results });
  });
});

app.get('/', (req, res) => {
  res.type('text/plain');
  res.send('WebSocket is Running');
});

// Helper functions
async function getPlayers(roomPin) {
  return new Promise((resolve, reject) => {
    const query = 'SELECT name FROM participants WHERE room_pin = ?';
    db.query(query, [roomPin], (err, results) => {
      if (err) return reject(err);
      resolve(results.map(row => ({ name: row.name })));
    });
  });
}

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

async function removePlayerFromDatabase(playerName, roomPin) {
  return new Promise((resolve, reject) => {
    const deleteQuery = 'DELETE FROM participants WHERE name = ? AND room_pin = ?';
    db.query(deleteQuery, [playerName, roomPin], (err, results) => {
      if (err) return reject(err);
      console.log(`Removed player ${playerName} from room ${roomPin}`);
      resolve(results);
    });
  });
}

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
