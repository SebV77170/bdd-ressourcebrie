const express = require('express');
const cors = require('cors');
const app = express();
const port = 3001;
const validerVenteRoutes = require('./routes/validerVente.routes');
const ventesRoutes = require('./routes/ventes.routes');
const produitsRoutes = require('./routes/produits');
const ticketRoutes = require('./routes/ticket.routes');
const bilanRoutes = require('./routes/bilan.routes');



app.use(cors());
app.use(express.json());
app.use('/api/produits', produitsRoutes);
app.use('/api/ticket', ticketRoutes);
app.use('/api/valider', validerVenteRoutes);
app.use('/api/ventes', ventesRoutes);
app.use('/api/bilan', bilanRoutes);


app.listen(port, () => {
  console.log(`Serveur backend lancé sur http://localhost:${port}`);
});
