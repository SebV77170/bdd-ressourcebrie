// ✅ Mise à jour de bilan.routes.js pour inclure les détails des paiements mixtes
const express = require('express');
const router = express.Router();
const db = require('../db');

// Tous les tickets (liste)
router.get('/', (req, res) => {
  try {
    const tickets = db.prepare('SELECT * FROM ticketdecaisse ORDER BY date_achat_dt DESC').all();
    res.json(tickets);
  } catch (err) {
    console.error('Erreur lecture tickets :', err);
    res.status(500).json({ error: err.message });
  }
});

// Détail des objets d'un ticket
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

// Détail complet du ticket, avec paiements mixtes si présent
router.get('/:id/details', (req, res) => {
  const id = req.params.id;
  try {
    const ticket = db.prepare('SELECT * FROM ticketdecaisse WHERE id_ticket = ?').get(id);
    if (!ticket) return res.status(404).json({ error: 'Ticket non trouvé' });

    const objets = db.prepare('SELECT * FROM objets_vendus WHERE id_ticket = ?').all(id);

    const paiementMixte = db.prepare('SELECT * FROM paiement_mixte WHERE id_ticket = ?').get(id);

    res.json({ ticket, objets, paiementMixte });
  } catch (err) {
    console.error('Erreur détails ticket :', err);
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
