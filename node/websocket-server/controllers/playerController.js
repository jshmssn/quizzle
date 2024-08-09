const playerService = require('../services/playerService');

async function handleJoinRoom(ws, msg, clients, playerStatuses) {
  const { pin: roomPin, playerName } = msg;

  if (playerStatuses.has(playerName)) {
    const playerStatus = playerStatuses.get(playerName);
    if (Date.now() - playerStatus.lastSeen < 5000) {
      clients.set(ws, { roomPin, playerName });
      playerStatuses.set(playerName, { ws, roomPin, lastSeen: Date.now() });
      await playerService.updatePlayerStatus(playerName, roomPin, 1);
    }
  } else {
    clients.set(ws, { roomPin, playerName });
    playerStatuses.set(playerName, { ws, roomPin, lastSeen: Date.now() });
    await playerService.updatePlayerStatus(playerName, roomPin, 1);
  }

  const players = await playerService.getPlayers(roomPin);
  return players;
}

async function handlePlayerLeave(playerName, roomPin, clients, playerStatuses) {
  // Remove the player from the playerStatuses map
  playerStatuses.delete(playerName);

  // Update the clients map
  clients.forEach((info, client) => {
    if (info.roomPin === roomPin && info.playerName === playerName) {
      clients.delete(client);
    }
  });

  // Update the player's status in the database
  await playerService.updatePlayerStatus(playerName, roomPin, 0); // Assuming 0 represents the player has left

  // Get the updated list of players in the room
  const players = await playerService.getPlayers(roomPin);
  return players;
}

module.exports = {
  handleJoinRoom,
  handlePlayerLeave,
};
