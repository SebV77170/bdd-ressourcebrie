const express = require('express');
const cors = require('cors');
const app = express();
const PORT = process.env.PORT || 3001;

const http = require('http');
const server = http.createServer(app);
const io = require('socket.io')(server, {
  cors: { origin: '*' }
});

// Import des routes
const validerVenteRoutes = require('./routes/validerVente.routes');
const ventesRoutes = require('./routes/ventes.routes');
const produitsRoutes = require('./routes/produits');
const ticketRoutes = require('./routes/ticket.routes');
const bilanRoutes = require('./routes/bilan.routes');
const correctionRoutes = require('./routes/correction.routes');
const sessionRoutes = require('./routes/session.routes');
const usersRoutes = require('./routes/users.routes');
const resetRoutes = require('./routes/reset.routes');

// Middlewares
app.use(cors());
app.use(express.json());

// Routes
app.use('/api/produits', produitsRoutes);
app.use('/api/ticket', ticketRoutes);
app.use('/api/valider', validerVenteRoutes);
app.use('/api/ventes', ventesRoutes);
app.use('/api/bilan', bilanRoutes);
app.use('/api/correction', correctionRoutes);
app.use('/api/session', sessionRoutes);
app.use('/api/users', usersRoutes);
app.use('/api/reset', resetRoutes);

// Socket.IO
app.set('socketio', io);

// ✅ Lancement unique
server.listen(PORT, () => {
  console.log(`Serveur backend lancé sur http://localhost:${PORT}`);
});
