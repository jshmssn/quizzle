function broadcastToRoom(clients, roomPin, message) {
    clients.forEach((clientInfo, clientWs) => {
      if (clientInfo.roomPin === roomPin && clientWs.readyState === WebSocket.OPEN) {
        clientWs.send(message);
      }
    });
  }
  
  module.exports = {
    broadcastToRoom,
  };
  