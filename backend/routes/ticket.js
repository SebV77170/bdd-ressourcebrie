const express = require('express');
const router = express.Router();
const db = require('../db');

// Ajouter un article
router.post('/', (req, res) => {
  const { id_produit, quantite } = req.body;
  db.query(
    'INSERT INTO ticketdecaissetemp (id_produit, quantite) VALUES (?, ?)',
    [id_produit, quantite],
    (err, result) => {
      if (err) return res.status(500).json({ error: err });
      res.status(201).json({ message: 'Ajouté au ticket' });
    }
  );
});

// Lire le ticket
router.get('/', (req, res) => {
  db.query('SELECT * FROM ticketdecaissetemp', (err, results) => {
    if (err) return res.status(500).json({ error: err });
    res.json(results);
  });
});

module.exports = router;
