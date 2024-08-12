const roomService = require('../services/roomService');

async function getRoomStatus(roomPin) {
  const roomStatus = await roomService.getRoomStatus(roomPin);
  return roomStatus;
}

module.exports = {
  getRoomStatus,
};
