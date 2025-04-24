const express = require('express');
const router = express.Router();
const db = require('../db'); // Assure-toi que ce fichier contient la connexion MySQL

// Créer une nouvelle vente
router.post('/', (req, res) => {
  const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
  const sql = `INSERT INTO vente (dateheure) VALUES (?)`;
  db.query(sql, [now], (err, result) => {
    if (err) return res.status(500).json({ error: err });
    res.json({ id_temp_vente: result.insertId });
  });
});

// Obtenir toutes les ventes
router.get('/', (req, res) => {
  db.query('SELECT id_temp_vente FROM vente ORDER BY id_temp_vente DESC', (err, rows) => {
    if (err) return res.status(500).json({ error: err });
    res.json(rows);
  });
});

router.delete('/:id_temp_vente', (req, res) => {
  const id = req.params.id_temp_vente;

  const deleteTicket = `DELETE FROM ticketdecaissetemp WHERE id_temp_vente = ?`;
  const deleteVente = `DELETE FROM vente WHERE id_temp_vente = ?`;

  db.query(deleteTicket, [id], (err) => {
    if (err) return res.status(500).json({ error: err });

    db.query(deleteVente, [id], (err2) => {
      if (err2) return res.status(500).json({ error: err2 });
      res.json({ success: true });
    });
  });
});


module.exports = router;
