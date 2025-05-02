const express = require('express');
const router = express.Router();
const db = require('../db');
const session = require('../session');

router.post('/', (req, res) => {
  const { id_ticket_original, articles_origine, articles_correction, motif } = req.body;
  const now = new Date().toISOString();
  const timestamp = Math.floor(Date.now() / 1000);

  const user = session.getUser();
  if (!user) {
    return res.status(401).json({ error: 'Aucun utilisateur connecté' });
  }
  const utilisateur = user.nom;
  const id_vendeur = user.id;

  // Calcul des totaux
  const totalAnnulation = articles_origine.reduce((sum, a) => sum + a.prix * Math.abs(a.nbr), 0);
  const totalCorrection = articles_correction.reduce((sum, a) => sum + a.prix * a.nbr, 0);

  try {
    // Étape 1 : Ticket d'annulation
    const annul = db.prepare(`
      INSERT INTO ticketdecaisse (date_achat_dt, correction_de, flag_correction, nom_vendeur, id_vendeur, nbr_objet, prix_total)
      VALUES (?, ?, 1, ?, ?, ?, ?)
    `).run(now, id_ticket_original, utilisateur, id_vendeur, articles_origine.length, totalAnnulation);
    const id_annul = annul.lastInsertRowid;

    const insertArticle = db.prepare(`
      INSERT INTO objets_vendus (id_ticket, nom, prix, nbr, categorie, nom_vendeur, id_vendeur, date_achat, timestamp)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);
    for (const art of articles_origine) {
      insertArticle.run(id_annul, art.nom, art.prix, -Math.abs(art.nbr), art.categorie, utilisateur, id_vendeur, now, timestamp);
    }

    // Étape 2 : Ticket corrigé
    const correc = db.prepare(`
      INSERT INTO ticketdecaisse (date_achat_dt, nom_vendeur, id_vendeur, nbr_objet, prix_total)
      VALUES (?, ?, ?, ?, ?)
    `).run(now, utilisateur, id_vendeur, articles_correction.length, totalCorrection);
    const id_corrige = correc.lastInsertRowid;

    for (const art of articles_correction) {
      insertArticle.run(id_corrige, art.nom, art.prix, art.nbr, art.categorie, utilisateur, id_vendeur, now, timestamp);
    }

    // Étape 3 : Journal de correction
    db.prepare(`
      INSERT INTO journal_corrections
      (date_correction, id_ticket_original, id_ticket_annulation, id_ticket_correction, utilisateur, motif)
      VALUES (?, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, id_annul, id_corrige, utilisateur, motif || '');

    res.json({ success: true, id_annul, id_corrige });

  } catch (err) {
    console.error("Erreur correction :", err);
    res.status(500).json({ error: 'Erreur serveur lors de la correction' });
  }
});

module.exports = router;
