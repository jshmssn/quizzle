// testserver.js
const express = require('express');
const app = express();
const port = 3000;

// Serve a test endpoint
app.get('/api/testserver', (req, res) => {
    res.json({ message: 'Hello from Node.js!' });
});

app.listen(port, () => {
    console.log(`Node.js server running at http://localhost:${port}`);
});
