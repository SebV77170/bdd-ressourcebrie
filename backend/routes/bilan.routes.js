const express = require('express');
const router = express.Router();
const db = require('../db');

router.get('/', (req, res) => {
    try {
      const tickets = db.prepare('SELECT * FROM ticketdecaisse ORDER BY date_achat_dt DESC').all();
      res.json(tickets);
    } catch (err) {
      console.error('Erreur lecture tickets :', err);
      res.status(500).json({ error: err.message });
    }
  });

  router.get('/:id/objets', (req, res) => {
    const id = req.params.id;
    try {
      const lignes = db.prepare('SELECT * FROM objets_vendus WHERE id_ticket = ?').all(id);
      res.json(lignes);
    } catch (err) {
      console.error('Erreur chargement objets_vendus :', err);
      res.status(500).json({ error: err.message });
    }
  });
  

  module.exports = router;
  