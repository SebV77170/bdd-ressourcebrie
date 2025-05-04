const express = require('express');
const router = express.Router();
const db = require('../db');

router.post('/', (req, res) => {
  const { moyenPaiement } = req.body;

  try {
    // Récupération des lignes de vente temporaire
    const items = db.prepare('SELECT * FROM ticketdecaissetemp').all();

    // TODO : traiter les données (insertion dans une vraie table de ventes ?)

    // Vider la table temporaire
    db.prepare('DELETE FROM ticketdecaissetemp').run();

    res.json({ message: 'Vente validée', moyenPaiement, articles: items });
  } catch (err) {
    console.error('Erreur SQLite :', err);
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
