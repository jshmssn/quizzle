const bodyParser = require('body-parser');

function applyBodyParser(app) {
  app.use(bodyParser.json());
}

module.exports = applyBodyParser;
