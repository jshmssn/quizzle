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

// Store client information and player statuses
const clients = new Map(); // Maps WebSocket to client info
const playerStatuses = new Map(); // Maps player name to status

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
      playerStatuses.set(playerName, { ws, roomPin, lastSeen: Date.now() });

      try {
        const players = await getPlayers(roomPin);
        const roomStatus = await getRoomStatus(roomPin);

        // Broadcast updated player list to all clients in the room
        const updatePlayersMsg = JSON.stringify({ type: 'updatePlayers', players: players });
        broadcastToRoom(roomPin, updatePlayersMsg);

        // Send room status update only to the new client
        const roomStatusMsg = JSON.stringify({ type: 'roomStatus', ...roomStatus });
        ws.send(roomStatusMsg);
      } catch (err) {
        console.error('Error handling join room request:', err);
      }
    }
  });

  ws.on('close', async () => {
    console.log('A client has disconnected.');

    const clientInfo = clients.get(ws);
    if (clientInfo) {
      const { roomPin, playerName } = clientInfo;
      const playerStatus = playerStatuses.get(playerName);

      if (playerStatus) {
        // Update last seen time for the player
        playerStatus.lastSeen = Date.now();

        // Set a timeout to check if the player reconnects
        setTimeout(async () => {
          if (Date.now() - playerStatus.lastSeen >= 5000) { // 5 seconds timeout
            try {
              // The player has not reconnected; treat as a leave
              clients.delete(ws);
              playerStatuses.delete(playerName);
              await removePlayerFromDatabase(playerName, roomPin);

              // Get updated player list
              const players = await getPlayers(roomPin);

              // Broadcast updated player list to all clients in the room
              const updateMsg = JSON.stringify({ type: 'leftPlayers', players: players });
              broadcastToRoom(roomPin, updateMsg);
            } catch (err) {
              console.error('Error removing player from database:', err);
            }
          }
        }, 5000);
      }
    }
  });
});

// Broadcast message to all clients in the specified room
function broadcastToRoom(roomPin, message) {
  clients.forEach((clientInfo, clientWs) => {
    if (clientInfo.roomPin === roomPin && clientWs.readyState === WebSocket.OPEN) {
      clientWs.send(message);
    }
  });
}

// Periodically broadcast room status updates
function broadcastRoomStatus(roomPin) {
  getRoomStatus(roomPin).then(roomStatus => {
    const roomStatusMsg = JSON.stringify({ type: 'roomStatus', ...roomStatus });
    broadcastToRoom(roomPin, roomStatusMsg);
  }).catch(err => console.error('Error broadcasting room status:', err));
}

// Periodic status updates (optional)
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

let endTimeCache = {}; // In-memory cache for end times

app.get('/get-time', (req, res) => {
  const questionId = req.query.questionId; // Get question ID from query parameter

  if (endTimeCache[questionId]) {
    res.json({ endTime: endTimeCache[questionId] });
  } else {
    getEndTimeFromDatabase(questionId)
      .then(endTime => {
        if (endTime) {
          endTimeCache[questionId] = endTime;
          res.json({ endTime });
        } else {
          res.status(404).json({ error: 'Question not found' });
        }
      })
      .catch(error => {
        console.error('Database query failed:', error);
        res.status(500).json({ error: 'Internal server error' });
      });
  }
});

// Helper function to calculate and store end time
function getEndTimeFromDatabase(questionId) {
  return new Promise((resolve, reject) => {
    const query = 'SELECT time FROM questions WHERE id = ?';
    db.query(query, [questionId], (error, results) => {
      if (error) {
        return reject(error);
      }
      if (results.length > 0) {
        const timeInSeconds = results[0].time; // Time in seconds
        const endTime = Date.now() + timeInSeconds * 1000; // Calculate end time
        resolve(endTime);
      } else {
        resolve(null);
      }
    });
  });
}

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
