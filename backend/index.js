const express = require('express');
const cors = require('cors');
const app = express();
const port = 3001;
const validerRoute = require('./routes/valider');
const ventesRoutes = require('./routes/ventes.routes');
const produitsRoutes = require('./routes/produits');
const ticketRoutes = require('./routes/ticket.routes');



app.use(cors());
app.use(express.json());
app.use('/api/produits', produitsRoutes);
app.use('/api/ticket', ticketRoutes);
app.use('/api/valider', validerRoute);
app.use('/api/ventes', ventesRoutes);


app.listen(port, () => {
  console.log(`Serveur backend lancé sur http://localhost:${port}`);
});
