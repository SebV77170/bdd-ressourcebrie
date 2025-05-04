// ---------- ventes.routes.js ----------
const express = require('express');
const router = express.Router();
const db = require('../db');

// Créer une nouvelle vente (retourne un id_temp_vente auto-incrémenté)
router.post('/', (req, res) => {
  try {
    console.log('📥 Requête POST /api/ventes reçue');
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const result = db.prepare('INSERT INTO vente (dateheure) VALUES (?)').run(now);
    console.log('✅ Vente créée avec ID :', result.lastInsertRowid);
    res.json({ id_temp_vente: result.lastInsertRowid });
  } catch (err) {
    console.error('❌ Erreur SQLite (POST /vente) :', err);
    res.status(500).json({ error: err.message });
  }
});

// Obtenir toutes les ventes
router.get('/', (req, res) => {
  try {
    console.log('📥 Requête GET /api/ventes reçue');
    const rows = db.prepare('SELECT id_temp_vente FROM vente ORDER BY id_temp_vente DESC').all();
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Supprimer une vente et ses articles
router.delete('/:id_temp_vente', (req, res) => {
  try {
    const id = req.params.id_temp_vente;
    console.log('🗑 Suppression de la vente ID :', id);
    db.prepare('DELETE FROM ticketdecaissetemp WHERE id_temp_vente = ?').run(id);
    db.prepare('DELETE FROM vente WHERE id_temp_vente = ?').run(id);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
