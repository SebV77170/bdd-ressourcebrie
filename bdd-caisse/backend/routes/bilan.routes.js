// ✅ Mise à jour de bilan.routes.js pour inclure les détails des paiements mixtes
const express = require('express');
const router = express.Router();
const db = require('../db');

// Tous les tickets (liste)
router.get('/', (req, res) => {
  try {
    const tickets = db.prepare(`
      SELECT 
        t.*, 
        EXISTS (
          SELECT 1 FROM journal_corrections jc WHERE jc.id_ticket_original = t.id_ticket
        ) AS ticket_corrige,
        EXISTS (
          SELECT 1 FROM journal_corrections jc WHERE jc.id_ticket_correction = t.id_ticket
        ) AS est_correction
      FROM ticketdecaisse t
      ORDER BY t.id_ticket DESC
    `).all();
    
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

router.get('/jour', (req, res) => {
  const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
  const bilan = db.prepare('SELECT nombre_vente, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte, prix_total_virement FROM bilan WHERE date = ?').get(today);
  if (!bilan) return res.json({ nombre_vente: 0, prix_total: 0 });
  res.json(bilan);
});


module.exports = router;
