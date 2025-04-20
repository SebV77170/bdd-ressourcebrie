const express = require('express');
const router = express.Router();
const db = require('../db');

// Validation simple de la vente
router.post('/', (req, res) => {
  const { moyenPaiement } = req.body;

  // Tu pourras ici ajouter logique de réduction ensuite

  // Exemple de log et purge simple
  console.log("Vente validée en", moyenPaiement);

  db.query('DELETE FROM ticketdecaissetemp', (err) => {
    if (err) return res.status(500).json({ error: err });
    res.json({ success: true, paiement: moyenPaiement });
  });
});

module.exports = router;
