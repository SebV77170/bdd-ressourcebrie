const express = require('express');
const cors = require('cors');
const app = express();
const port = 3001;

const produitsRoutes = require('./routes/produits');

app.use(cors());
app.use(express.json());

app.use('/api/produits', produitsRoutes);

app.listen(port, () => {
  console.log(`Serveur backend lancé sur http://localhost:${port}`);
});
