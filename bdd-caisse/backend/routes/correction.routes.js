// ✅ Correction complète avec génération des tickets .txt
const express = require('express');
const router = express.Router();
const db = require('../db');
const session = require('../session');
const fs = require('fs');
const path = require('path');

router.post('/', (req, res) => {
  const {
    id_ticket_original,
    articles_origine,
    articles_correction,
    motif,
    moyen_paiement,
    reductionType,
    paiements = []
  } = req.body;

  const now = new Date().toISOString();
  const timestamp = Math.floor(Date.now() / 1000);

  const user = session.getUser();
  if (!user) return res.status(401).json({ error: 'Aucun utilisateur connecté' });

  const utilisateur = user.nom;
  const id_vendeur = user.id;

  let prixTotal = articles_correction.reduce((sum, a) => sum + a.prix * a.nbr, 0);
  let reducBene = 0, reducClient = 0, reducGrosPanierClient = 0, reducGrosPanierBene = 0;

  if (reductionType === 'trueClient') {
    prixTotal -= 500; reducClient = 1;
  } else if (reductionType === 'trueBene') {
    prixTotal -= 1000; reducBene = 1;
  } else if (reductionType === 'trueGrosPanierClient') {
    prixTotal = Math.round(prixTotal * 0.9); reducGrosPanierClient = 1;
  } else if (reductionType === 'trueGrosPanierBene') {
    prixTotal = Math.round(prixTotal * 0.8); reducGrosPanierBene = 1;
  }

  if (prixTotal < 0) prixTotal = 0;

  const totalAnnulation = articles_origine.reduce((sum, a) => sum + a.prix * Math.abs(a.nbr), 0);

  try {
    const annul = db.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, correction_de, flag_correction, nom_vendeur, id_vendeur,
        nbr_objet, prix_total, moyen_paiement
      ) VALUES (?, ?, 1, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, utilisateur, id_vendeur, articles_origine.length, totalAnnulation, moyen_paiement);
    const id_annul = annul.lastInsertRowid;

    const insertArticle = db.prepare(`
      INSERT INTO objets_vendus (
        id_ticket, nom, prix, nbr, categorie,
        nom_vendeur, id_vendeur, date_achat, timestamp
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    for (const art of articles_origine) {
      insertArticle.run(id_annul, art.nom, art.prix, -Math.abs(art.nbr), art.categorie, utilisateur, id_vendeur, now, timestamp);
    }

    const correc = db.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, nom_vendeur, id_vendeur, nbr_objet, prix_total, moyen_paiement,
        reducbene, reducclient, reducgrospanierclient, reducgrospanierbene
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(now, utilisateur, id_vendeur, articles_correction.length, prixTotal, moyen_paiement,
      reducBene, reducClient, reducGrosPanierClient, reducGrosPanierBene);
    const id_corrige = correc.lastInsertRowid;

    db.prepare('UPDATE ticketdecaisse SET corrige_le_ticket = ? WHERE id_ticket = ?')
  .run(id_ticket_original, id_corrige);


    for (const art of articles_correction) {
      insertArticle.run(id_corrige, art.nom, art.prix, art.nbr, art.categorie, utilisateur, id_vendeur, now, timestamp);
    }

    if (moyen_paiement === 'mixte' && Array.isArray(paiements)) {
      const pm = { espece: 0, carte: 0, cheque: 0, virement: 0 };
      const normalisation = {
        'espèce': 'espece', 'espèces': 'espece', 'carte': 'carte',
        'chèque': 'cheque', 'chéque': 'cheque', 'cheque': 'cheque', 'virement': 'virement'
      };

      for (const p of paiements) {
        const champ = normalisation[p.moyen?.toLowerCase()] || null;
        if (champ && pm.hasOwnProperty(champ)) {
          pm[champ] += p.montant;
        }
      }

      db.prepare(`
        INSERT INTO paiement_mixte (id_ticket, espece, carte, cheque, virement)
        VALUES (?, ?, ?, ?, ?)
      `).run(id_corrige, pm.espece, pm.carte, pm.cheque, pm.virement);
    }

    // 🎟️ Générer le ticket d'annulation
    const pathAnnul = path.join(__dirname, `../../tickets/Ticket${id_annul}.txt`);
    let contenuAnnul = `RESSOURCE'BRIE\nAssociation loi 1901\n`;
    contenuAnnul += `TICKET D'ANNULATION #${id_annul}\n`;
    contenuAnnul += `⚠️ ANNULE LE TICKET #${id_ticket_original}\n`;
    contenuAnnul += `Date : ${now}\nVendeur : ${utilisateur}\n\n`;

    articles_origine.forEach(a => {
      contenuAnnul += `${a.nbr} x ${a.nom} (${a.categorie}) - ${(a.prix * a.nbr / 100).toFixed(2)}€\n`;
    });
    contenuAnnul += `\nTOTAL ANNULÉ : ${(totalAnnulation / 100).toFixed(2)}€\n`;
    contenuAnnul += `Paiement initial : ${moyen_paiement}\n\n`;
    contenuAnnul += `Motif de correction : ${motif || '—'}\nMerci de votre compréhension.\n`;
    fs.writeFileSync(pathAnnul, contenuAnnul, 'utf8');
    db.prepare('UPDATE ticketdecaisse SET lien = ? WHERE id_ticket = ?').run(`tickets/Ticket${id_annul}.txt`, id_annul);

    // 🧾 Générer le ticket corrigé
    const ticketPath = path.join(__dirname, `../../tickets/Ticket${id_corrige}.txt`);
    let contenu = `RESSOURCE'BRIE\nAssociation loi 1901\n`;
    contenu += `TICKET DE CORRECTION #${id_corrige}\n`;
    contenu += `⚠️ CE TICKET EST UNE MODIFICATION DU TICKET #${id_ticket_original}\n`;
    contenu += `Date : ${now}\nVendeur : ${utilisateur}\n\n`;

    articles_correction.forEach(a => {
      contenu += `${a.nbr} x ${a.nom} (${a.categorie}) - ${(a.prix * a.nbr / 100).toFixed(2)}€\n`;
    });
    contenu += `\nTOTAL : ${(prixTotal / 100).toFixed(2)}€\n`;
    contenu += `Paiement : ${moyen_paiement}\n`;

    if (reductionType) {
      const reductionsMap = {
        'trueClient': 'Fidélité client (-5€)',
        'trueBene': 'Fidélité bénévole (-10€)',
        'trueGrosPanierClient': 'Gros panier client (-10%)',
        'trueGrosPanierBene': 'Gros panier bénévole (-20%)'
      };
      contenu += `Réduction appliquée : ${reductionsMap[reductionType] || reductionType}\n`;
    }

    contenu += `Motif de la correction : ${motif || '—'}\n`;
    contenu += `\nMerci de votre visite !\n`;
    fs.writeFileSync(ticketPath, contenu, 'utf8');
    db.prepare('UPDATE ticketdecaisse SET lien = ? WHERE id_ticket = ?').run(`tickets/Ticket${id_corrige}.txt`, id_corrige);

    db.prepare(`
      INSERT INTO journal_corrections (
        date_correction, id_ticket_original, id_ticket_annulation,
        id_ticket_correction, utilisateur, motif
      ) VALUES (?, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, id_annul, id_corrige, utilisateur, motif || '');

    res.json({ success: true, id_annul, id_corrige });

  } catch (err) {
    console.error("Erreur correction :", err);
    res.status(500).json({ error: 'Erreur serveur lors de la correction' });
  }

  const io = req.app.get('socketio');
if (io) {
  io.emit('bilanUpdated');
}

});

module.exports = router;
