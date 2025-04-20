const express = require('express');
const cors = require('cors');
const app = express();
const port = 3001;
const ticketRoutes = require('./routes/ticket');
const validerRoute = require('./routes/valider');


const produitsRoutes = require('./routes/produits');

app.use(cors());
app.use(express.json());

app.use('/api/produits', produitsRoutes);
app.use('/api/ticket', ticketRoutes);
app.use('/api/valider', validerRoute);

app.listen(port, () => {
  console.log(`Serveur backend lancé sur http://localhost:${port}`);
});
