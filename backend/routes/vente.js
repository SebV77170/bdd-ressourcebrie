const express = require('express');
const router = express.Router();
const db = require('../db');

router.post('/', (req, res) => {
  const { moyenPaiement } = req.body;

  // Ici on imagine que tu traites les données de `ticketdecaissetemp`
  db.query('SELECT * FROM ticketdecaissetemp', (err, items) => {
    if (err) return res.status(500).json({ error: err });

    // Traitement fictif : on vide la table après validation
    db.query('DELETE FROM ticketdecaissetemp', (err2) => {
      if (err2) return res.status(500).json({ error: err2 });
      res.json({ message: 'Vente validée', moyenPaiement });
    });
  });
});

module.exports = router;
