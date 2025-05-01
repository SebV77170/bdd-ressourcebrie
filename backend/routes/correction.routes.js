const express = require('express');
const router = express.Router();
const db = require('../db');

router.post('/', (req, res) => {
  const { id_ticket_original, articles_origine, articles_correction, utilisateur, motif } = req.body;
  const now = new Date().toISOString();

  try {
    // Étape 1 : Créer un ticket d'annulation
    const annul = db.prepare(`
      INSERT INTO ticketdecaisse (date_achat_dt, correction_de, flag_correction, nom_vendeur)
      VALUES (?, ?, 1, ?)
    `).run(now, id_ticket_original, utilisateur || 'système');
    const id_annul = annul.lastInsertRowid;

    // Étape 2 : Insérer les articles annulés (quantités négatives)
    const insertArticle = db.prepare(`
      INSERT INTO objets_vendus (id_ticket, nom, prix, nbr, categorie)
      VALUES (?, ?, ?, ?, ?)
    `);
    for (const art of articles_origine) {
      insertArticle.run(id_annul, art.nom, art.prix, -Math.abs(art.nbr), art.categorie);
    }

    // Étape 3 : Créer un nouveau ticket corrigé
    const correc = db.prepare(`
      INSERT INTO ticketdecaisse (date_achat_dt, nom_vendeur)
      VALUES (?, ?)
    `).run(now, utilisateur || 'système');
    const id_corrige = correc.lastInsertRowid;

    for (const art of articles_correction) {
      insertArticle.run(id_corrige, art.nom, art.prix, art.nbr, art.categorie);
    }

    // Étape 4 : Journaliser
    db.prepare(`
      INSERT INTO journal_corrections
      (date_correction, id_ticket_original, id_ticket_annulation, id_ticket_correction, utilisateur, motif)
      VALUES (?, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, id_annul, id_corrige, utilisateur || 'système', motif || '');

    res.json({ success: true, id_annul, id_corrige });

  } catch (err) {
    console.error("Erreur correction :", err);
    res.status(500).json({ error: 'Erreur serveur lors de la correction' });
  }
});

module.exports = router;
