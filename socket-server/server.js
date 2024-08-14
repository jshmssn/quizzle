const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const applyCors = require('./middlewares/cors');
const applyBodyParser = require('./middlewares/bodyParser');
const { broadcastToRoom } = require('./utils/websocketUtils');
const playerController = require('./controllers/playerController');
const roomController = require('./controllers/roomController');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

applyCors(app);
applyBodyParser(app);

app.use(express.static('public'));

const clients = new Map(); // Map to store clients and their associated room PINs
const playerStatuses = new Map(); // Map to store player statuses

// Function to periodically send room status to all connected clients
const sendRoomStatusUpdates = async () => {
  for (const [ws, clientData] of clients.entries()) {
    const roomPin = clientData.roomPin;
    const roomStatus = await roomController.getRoomStatus(roomPin);
    const roomStatusMsg = JSON.stringify({ type: 'roomStatus', ...roomStatus });
    ws.send(roomStatusMsg);
  }
};

// Broadcast room status every 1 second
setInterval(sendRoomStatusUpdates, 1000);

wss.on('connection', (ws) => {
  ws.on('message', async (message) => {
    const msg = JSON.parse(message);

    if (msg.type === 'joinRoom') {
      const players = await playerController.handleJoinRoom(ws, msg, clients, playerStatuses);
      const roomStatus = await roomController.getRoomStatus(msg.pin);

      // Ensure the client is properly added to the Map
      clients.set(ws, { roomPin: msg.pin, playerName: msg.playerName });

      const updatePlayersMsg = JSON.stringify({ type: 'updatePlayers', players });
      broadcastToRoom(clients, msg.pin, updatePlayersMsg);

      const roomStatusMsg = JSON.stringify({ type: 'roomStatus', ...roomStatus });
      ws.send(roomStatusMsg);
    }
  });

  // Handle player disconnection
  ws.on('close', async () => {
    try {
      const clientData = clients.get(ws);
      if (clientData) {
        const disconnectedPlayer = clientData.playerName;
        const roomPin = clientData.roomPin;
        const players = await playerController.handlePlayerLeave(disconnectedPlayer, roomPin, clients, playerStatuses);

        const leftPlayerMsg = JSON.stringify({ type: 'leftPlayer', players });
        broadcastToRoom(clients, roomPin, leftPlayerMsg);

        // Optionally, delete the client from the Map if they disconnect
        clients.delete(ws);
      } else {
        console.error('WebSocket client data not found on disconnect.');
      }
    } catch (error) {
      console.error('Error handling WebSocket disconnection:', error);
    }
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
