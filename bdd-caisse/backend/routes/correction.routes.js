// ✅ Correction complète avec gestion propre des réductions et génération des tickets .txt
const express = require('express');
const router = express.Router();
const { sqlite } = require('../db');;
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

  const articles_sans_reduction = [...articles_origine];


  let articles_correction_sans_reduction = articles_correction.filter(a => a.categorie !== 'Réduction');

  let reductionArticle = null;
  let reducBene = 0, reducClient = 0, reducGrosPanierClient = 0, reducGrosPanierBene = 0;

  if (reductionType === 'trueClient') {
    reductionArticle = { nom: 'Réduction Fidélité Client', prix: -500, nbr: 1, categorie: 'Réduction' };
    reducClient = 1;
  } else if (reductionType === 'trueBene') {
    reductionArticle = { nom: 'Réduction Fidélité Bénévole', prix: -1000, nbr: 1, categorie: 'Réduction' };
    reducBene = 1;
  } else if (reductionType === 'trueGrosPanierClient') {
    const montantAvantReduc = articles_correction_sans_reduction.reduce((sum, a) => sum + a.prix * a.nbr, 0);
    const reducMontant = Math.round(montantAvantReduc * 0.1);
    reductionArticle = { nom: 'Réduction Gros Panier Client (-10%)', prix: -reducMontant, nbr: 1, categorie: 'Réduction' };
    reducGrosPanierClient = 1;
  } else if (reductionType === 'trueGrosPanierBene') {
    const montantAvantReduc = articles_correction_sans_reduction.reduce((sum, a) => sum + a.prix * a.nbr, 0);
    const reducMontant = Math.round(montantAvantReduc * 0.2);
    reductionArticle = { nom: 'Réduction Gros Panier Bénévole (-20%)', prix: -reducMontant, nbr: 1, categorie: 'Réduction' };
    reducGrosPanierBene = 1;
  }

  if (reductionArticle) {
    articles_correction_sans_reduction.push(reductionArticle);
  }

  let prixTotal = articles_correction_sans_reduction.reduce((sum, a) => sum + a.prix * a.nbr, 0);
  if (prixTotal < 0) prixTotal = 0;

  let totalAnnulation = articles_sans_reduction.reduce((sum, a) => sum + a.prix * (-(a.nbr)), 0);
  if (totalAnnulation > 0) totalAnnulation = 0;

  try {
    const annul = sqlite.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, correction_de, flag_correction, nom_vendeur, id_vendeur,
        nbr_objet, prix_total, moyen_paiement
      ) VALUES (?, ?, 1, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, utilisateur, id_vendeur, articles_sans_reduction.length, totalAnnulation, moyen_paiement);
    const id_annul = annul.lastInsertRowid;

    const insertArticle = sqlite.prepare(`
      INSERT INTO objets_vendus (
        id_ticket, nom, prix, nbr, categorie,
        nom_vendeur, id_vendeur, date_achat, timestamp
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    for (const art of articles_sans_reduction) {
      insertArticle.run(id_annul, art.nom, art.prix, -(art.nbr), art.categorie, utilisateur, id_vendeur, now, timestamp);
    }

    const correc = sqlite.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, nom_vendeur, id_vendeur, nbr_objet, prix_total, moyen_paiement,
        reducbene, reducclient, reducgrospanierclient, reducgrospanierbene
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(now, utilisateur, id_vendeur, articles_correction_sans_reduction.length, prixTotal, moyen_paiement,
      reducBene, reducClient, reducGrosPanierClient, reducGrosPanierBene);
    const id_corrige = correc.lastInsertRowid;

    sqlite.prepare('UPDATE ticketdecaisse SET corrige_le_ticket = ? WHERE id_ticket = ?')
      .run(id_ticket_original, id_corrige);

    for (const art of articles_correction_sans_reduction) {
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

      sqlite.prepare(`
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

    articles_sans_reduction.forEach(a => {
      contenuAnnul += `${a.nbr} x ${a.nom} (${a.categorie}) - ${(a.prix * a.nbr / 100).toFixed(2)}€\n`;
    });
    contenuAnnul += `\nTOTAL ANNULÉ : ${(totalAnnulation / 100).toFixed(2)}€\n`;
    contenuAnnul += `Paiement initial : ${moyen_paiement}\n\n`;
    contenuAnnul += `Motif de correction : ${motif || '—'}\nMerci de votre compréhension.\n`;
    fs.writeFileSync(pathAnnul, contenuAnnul, 'utf8');
    sqlite.prepare('UPDATE ticketdecaisse SET lien = ? WHERE id_ticket = ?').run(`tickets/Ticket${id_annul}.txt`, id_annul);

    // 🧾 Générer le ticket corrigé
    const ticketPath = path.join(__dirname, `../../tickets/Ticket${id_corrige}.txt`);
    let contenu = `RESSOURCE'BRIE\nAssociation loi 1901\n`;
    contenu += `TICKET DE CORRECTION #${id_corrige}\n`;
    contenu += `⚠️ CE TICKET EST UNE MODIFICATION DU TICKET #${id_ticket_original}\n`;
    contenu += `Date : ${now}\nVendeur : ${utilisateur}\n\n`;

    articles_correction_sans_reduction.forEach(a => {
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
    sqlite.prepare('UPDATE ticketdecaisse SET lien = ? WHERE id_ticket = ?').run(`tickets/Ticket${id_corrige}.txt`, id_corrige);

    sqlite.prepare(`
      INSERT INTO journal_corrections (
        date_correction, id_ticket_original, id_ticket_annulation,
        id_ticket_correction, utilisateur, motif
      ) VALUES (?, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, id_annul, id_corrige, utilisateur, motif || '');

    // 📊 Mise à jour du bilan
    const today = now.slice(0, 10);
    const ticketOriginal = sqlite.prepare('SELECT * FROM ticketdecaisse WHERE id_ticket = ?').get(id_ticket_original);
    if (!ticketOriginal) {
      return res.status(400).json({ error: `Ticket original #${id_ticket_original} introuvable` });
    }
    
    let pmAnnul = { espece: 0, carte: 0, cheque: 0, virement: 0 };

    if (ticketOriginal.moyen_paiement === 'mixte') {
      const pmx = sqlite.prepare('SELECT * FROM paiement_mixte WHERE id_ticket = ?').get(id_ticket_original);
      if (pmx) {
        pmAnnul = {
          espece: pmx.espece || 0,
          carte: pmx.carte || 0,
          cheque: pmx.cheque || 0,
          virement: pmx.virement || 0
        };
      }
    } else {
      const champ = {
        'espèce': 'espece', 'espèces': 'espece', 'carte': 'carte',
        'chèque': 'cheque', 'chéque': 'cheque', 'cheque': 'cheque', 'virement': 'virement'
      }[ticketOriginal.moyen_paiement.toLowerCase()];
      if (champ && pmAnnul.hasOwnProperty(champ)) {
        pmAnnul[champ] = ticketOriginal.prix_total;
      }
    }

    const pmCorrige = { espece: 0, carte: 0, cheque: 0, virement: 0 };
    const normalisation = {
      'espèce': 'espece', 'espèces': 'espece', 'carte': 'carte',
      'chèque': 'cheque', 'chéque': 'cheque', 'cheque': 'cheque', 'virement': 'virement'
    };

    if (moyen_paiement === 'mixte') {
      for (const p of paiements) {
        const champ = normalisation[p.moyen?.toLowerCase()] || null;
        if (champ && pmCorrige.hasOwnProperty(champ)) {
          pmCorrige[champ] += p.montant;
        }
      }
    } else {
      const champ = normalisation[moyen_paiement.toLowerCase()] || null;
      if (champ) pmCorrige[champ] = prixTotal;
    }

    const bilanExistant = sqlite.prepare('SELECT * FROM bilan WHERE date = ?').get(today);

    if (bilanExistant) {
      sqlite.prepare(`
        UPDATE bilan
        SET prix_total = prix_total - ? + ?,
            prix_total_espece = prix_total_espece - ? + ?,
            prix_total_cheque = prix_total_cheque - ? + ?,
            prix_total_carte = prix_total_carte - ? + ?,
            prix_total_virement = prix_total_virement - ? + ?
        WHERE date = ?
      `).run(
        Math.abs(ticketOriginal.prix_total), prixTotal,
        Math.abs(pmAnnul.espece), pmCorrige.espece,
        Math.abs(pmAnnul.cheque), pmCorrige.cheque,
        Math.abs(pmAnnul.carte), pmCorrige.carte,
        Math.abs(pmAnnul.virement), pmCorrige.virement,
        today
      );
    } else {
      sqlite.prepare(`
        INSERT INTO bilan (
          date, timestamp, nombre_vente, poids, prix_total,
          prix_total_espece, prix_total_cheque, prix_total_carte, prix_total_virement
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      `).run(
        today, timestamp, 1, 0,
        prixTotal - Math.abs(ticketOriginal.prix_total),
        pmCorrige.espece - Math.abs(pmAnnul.espece),
        pmCorrige.cheque - Math.abs(pmAnnul.cheque),
        pmCorrige.carte - Math.abs(pmAnnul.carte),
        pmCorrige.virement - Math.abs(pmAnnul.virement)
      );
    }

    res.json({
      success: true,
      id_ticket_annulation: id_annul,
      id_ticket_correction: id_corrige
    });
    
  } catch (err) {
    console.error("Erreur correction :", err);
    res.status(500).json({ error: 'Erreur serveur lors de la correction' });
  }

  const io = req.app.get('socketio');
  if (io) {
    io.emit('bilanUpdated');
    io.emit('ticketsmisajour');
  }
});

module.exports = router;
