const express = require('express');
const fs = require('fs');
const path = require('path');
const router = express.Router();
const db = require('../db');

router.post('/', (req, res) => {
  try {
    const tables = [
      'bilan',
      'journal_corrections',
      'modifticketdecaisse',
      'objets_vendus',
      'paiement_mixte',
      'ticketdecaisse',
      'ticketdecaissetemp'
    ];

    // ⚙️ Suppression des données + reset auto-incrément
    db.transaction(() => {
      for (const table of tables) {
        db.prepare(`DELETE FROM ${table}`).run();
        db.prepare(`DELETE FROM sqlite_sequence WHERE name = ?`).run(table);
      }
    })();

    // 🧹 Suppression des fichiers de tickets
    const ticketDir = path.join(__dirname, '../../tickets');
    if (fs.existsSync(ticketDir)) {
      const fichiers = fs.readdirSync(ticketDir);
      fichiers.forEach(f => {
        if (f.endsWith('.txt')) {
          fs.unlinkSync(path.join(ticketDir, f));
        }
      });
    }

    res.json({ success: true, message: 'Base et fichiers tickets réinitialisés.' });
  } catch (err) {
    console.error('Erreur reset :', err);
    res.status(500).json({ error: 'Erreur lors de la réinitialisation.' });
  }
});

module.exports = router;
