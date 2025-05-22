// ✅ Correction complète avec gestion propre des réductions et génération des tickets .txt
const express = require('express');
const router = express.Router();
const { sqlite } = require('../db');
const session = require('../session');
const fs = require('fs');
const path = require('path');
const logSync = require('../logsync');
const { v4: uuidv4 } = require('uuid');

router.post('/', (req, res) => {
  const {
    id_ticket_original,
    uuid_ticket_original,
    articles_origine,
    articles_correction,
    motif,
    moyen_paiement,
    reductionType,
    paiements = []
  } = req.body;
console.log(req.body);


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
    reductionArticle = { uuid_objet: uuidv4(), nom: 'Réduction Fidélité Client', prix: -500, nbr: 1, categorie: 'Réduction' };
    reducClient = 1;
  } else if (reductionType === 'trueBene') {
    reductionArticle = { uuid_objet: uuidv4(), nom: 'Réduction Fidélité Bénévole', prix: -1000, nbr: 1, categorie: 'Réduction' };
    reducBene = 1;
  } else if (reductionType === 'trueGrosPanierClient') {
    const montantAvantReduc = articles_correction_sans_reduction.reduce((sum, a) => sum + a.prix * a.nbr, 0);
    const reducMontant = Math.round(montantAvantReduc * 0.1);
    reductionArticle = { uuid_objet: uuidv4(), nom: 'Réduction Gros Panier Client (-10%)', prix: -reducMontant, nbr: 1, categorie: 'Réduction' };
    reducGrosPanierClient = 1;
  } else if (reductionType === 'trueGrosPanierBene') {
    const montantAvantReduc = articles_correction_sans_reduction.reduce((sum, a) => sum + a.prix * a.nbr, 0);
    const reducMontant = Math.round(montantAvantReduc * 0.2);
    reductionArticle = { uuid_objet: uuidv4(), nom: 'Réduction Gros Panier Bénévole (-20%)', prix: -reducMontant, nbr: 1, categorie: 'Réduction' };
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
    const uuid_ticket_annul = uuidv4();
    const annul = sqlite.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, correction_de, flag_correction, nom_vendeur, id_vendeur,
        nbr_objet, prix_total, moyen_paiement, uuid_ticket
      ) VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)
    `).run(now, id_ticket_original, utilisateur, id_vendeur, articles_sans_reduction.length, totalAnnulation, moyen_paiement, uuid_ticket_annul);
    const id_annul = annul.lastInsertRowid;
    logSync('ticketdecaisse', 'INSERT', {
      uuid_ticket: uuid_ticket_annul,
      id_annul,
      nom_vendeur: utilisateur,
      id_vendeur,
      date_achat_dt: now,
      nbr_objet: articles_sans_reduction.length,
      moyen_paiement: moyen_paiement,
      prix_total: totalAnnulation,
      reducbene: reducBene,
      reducclient: reducClient,
      reducgrospanierclient: reducGrosPanierClient,
      reducgrospanierbene: reducGrosPanierBene
    });

    const insertArticle = sqlite.prepare(`
      INSERT INTO objets_vendus (
        id_ticket, nom, prix, nbr, categorie,
        nom_vendeur, id_vendeur, date_achat, timestamp, uuid_objet
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    for (const art of articles_sans_reduction) {
      const uuid_objet = uuidv4();
      insertArticle.run(id_annul, art.nom, art.prix, -(art.nbr), art.categorie, utilisateur, id_vendeur, now, timestamp, uuid_objet);
      logSync('objets_vendus', 'INSERT', {
        id_ticket: id_annul,
        nom: art.nom,
        prix: art.prix,
        nbr: -(art.nbr),
        categorie: art.categorie,
        nom_vendeur: utilisateur,
        id_vendeur,
        date_achat: now,
        timestamp,
        uuid_objet
      });
    }

    const uuid_ticket_corrige = uuidv4();
    const correc = sqlite.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, nom_vendeur, id_vendeur, nbr_objet, prix_total, moyen_paiement,
        reducbene, reducclient, reducgrospanierclient, reducgrospanierbene, uuid_ticket
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(now, utilisateur, id_vendeur, articles_correction_sans_reduction.length, prixTotal, moyen_paiement,
      reducBene, reducClient, reducGrosPanierClient, reducGrosPanierBene, uuid_ticket_corrige);
    const id_corrige = correc.lastInsertRowid;

    logSync('ticketdecaisse', 'INSERT', {
      uuid_ticket: uuid_ticket_corrige,
      id_corrige,
      nom_vendeur: utilisateur,
      id_vendeur,
      date_achat_dt: now,
      nbr_objet: articles_correction_sans_reduction.length,
      moyen_paiement: moyen_paiement,
      prix_total: prixTotal,
      reducbene: reducBene,
      reducclient: reducClient,
      reducgrospanierclient: reducGrosPanierClient,
      reducgrospanierbene: reducGrosPanierBene
    });

    sqlite.prepare('UPDATE ticketdecaisse SET corrige_le_ticket = ? WHERE id_ticket = ?').run(id_ticket_original, id_corrige);

    let pmCorrige = { espece: 0, carte: 0, cheque: 0, virement: 0 };
    const normalisation = {
      'espèce': 'espece', 'espèces': 'espece', 'carte': 'carte',
      'chèque': 'cheque', 'chéque': 'cheque', 'cheque': 'cheque', 'virement': 'virement'
    };

    for (const art of articles_correction_sans_reduction) {
      const uuid_objet = uuidv4();
      insertArticle.run(id_corrige, art.nom, art.prix, art.nbr, art.categorie, utilisateur, id_vendeur, now, timestamp, uuid_objet);
      logSync('objets_vendus', 'INSERT', {
        id_ticket: id_corrige,
        nom: art.nom,
        prix: art.prix,
        nbr: art.nbr,
        categorie: art.categorie,
        nom_vendeur: utilisateur,
        id_vendeur,
        date_achat: now,
        timestamp,
        uuid_objet
      });
    }

    if (moyen_paiement === 'mixte' && Array.isArray(paiements)) {
      const pm = { espece: 0, carte: 0, cheque: 0, virement: 0 };

      for (const p of paiements) {
        const champ = normalisation[p.moyen?.toLowerCase()] || null;
        if (champ && pm.hasOwnProperty(champ)) {
          pm[champ] += p.montant;
        }
      }

      pmCorrige = { ...pm };

      const uuid_ticket_mixte = uuid_ticket_corrige;
      sqlite.prepare(`
        INSERT INTO paiement_mixte (id_ticket, espece, carte, cheque, virement, uuid_ticket)
        VALUES (?, ?, ?, ?, ?, ?)
      `).run(id_corrige, pm.espece, pm.carte, pm.cheque, pm.virement, uuid_ticket_mixte);

      logSync('paiement_mixte', 'INSERT', {
        id_ticket: id_corrige,
        uuid_ticket: uuid_ticket_mixte,
        espece: pm.espece,
        carte: pm.carte,
        cheque: pm.cheque,
        virement: pm.virement
      });
    } else {
      const champ = normalisation[moyen_paiement.toLowerCase()] || null;
      if (champ) pmCorrige[champ] = prixTotal;
    }

    // ✅ Définir pmAnnul à partir du ticket original
    let pmAnnul = { espece: 0, carte: 0, cheque: 0, virement: 0 };

    const ticketOriginal = sqlite.prepare('SELECT * FROM ticketdecaisse WHERE id_ticket = ?').get(id_ticket_original);
    if (!ticketOriginal) {
      return res.status(400).json({ error: `Ticket original #${id_ticket_original} introuvable` });
    }

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
      const champ = normalisation[ticketOriginal.moyen_paiement?.toLowerCase()] || null;
      if (champ && pmAnnul.hasOwnProperty(champ)) {
        pmAnnul[champ] = ticketOriginal.prix_total;
      }
    }

     // ✅ Mise à jour du bilan
     const today = now.slice(0, 10);
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
       logSync('bilan', 'UPDATE', {
         date: today,
         timestamp,
         prix_total: -Math.abs(ticketOriginal.prix_total),
         prix_total_espece: -Math.abs(pmAnnul.espece),
         prix_total_cheque: -Math.abs(pmAnnul.cheque),
         prix_total_carte: -Math.abs(pmAnnul.carte),
         prix_total_virement: -Math.abs(pmAnnul.virement)
       });
 
       logSync('bilan', 'UPDATE', {
         date: today,
         timestamp,
         prix_total: prixTotal,
         prix_total_espece: pmCorrige.espece,
         prix_total_cheque: pmCorrige.cheque,
         prix_total_carte: pmCorrige.carte,
         prix_total_virement: pmCorrige.virement
       });
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
       logSync('bilan', 'INSERT', {
         date: today,
         timestamp,
         nombre_vente: 1,
         poids: 0,
         prix_total: prixTotal - Math.abs(ticketOriginal.prix_total),
         prix_total_espece: pmCorrige.espece - Math.abs(pmAnnul.espece),
         prix_total_cheque: pmCorrige.cheque - Math.abs(pmAnnul.cheque),
         prix_total_carte: pmCorrige.carte - Math.abs(pmAnnul.carte),
         prix_total_virement: pmCorrige.virement - Math.abs(pmAnnul.virement)
       });
     }

    res.json({ success: true, id_ticket_annulation: id_annul, id_ticket_correction: id_corrige });

    sqlite.prepare(`
    INSERT INTO journal_corrections (date_correction, id_ticket_original,id_ticket_annulation, id_ticket_correction, utilisateur , motif)
    VALUES (?, ?, ?, ?, ?, ?)
  `).run(now, id_ticket_original, id_annul, id_corrige, utilisateur, motif);
   

    logSync('journal_corrections', 'INSERT', {
      id_ticket_original,
      id_ticket_correction: id_corrige,
      id_ticket_annulation: id_annul,
      date_correction: now,
      utilisateur,
      motif
    });

  }
  catch (err) {
    console.error('Erreur lors de l\'insertion de la correction :', err);
    res.status(500).json({ error: 'Erreur lors de l\'insertion de la correction' });
  }

  const io = req.app.get('socketio');
  if (io) {
    io.emit('bilanUpdated');
    io.emit('ticketsmisajour');
  }
});

// Nouvelle route pour suppression via ticket d'annulation
// Nouvelle route pour suppression via ticket d'annulation
router.post('/:id/supprimer', (req, res) => {
  const id = parseInt(req.params.id);
  const now = new Date().toISOString();
  const ticket = sqlite.prepare('SELECT * FROM ticketdecaisse WHERE id_ticket = ?').get(id);

  if (!ticket) return res.status(404).json({ error: 'Ticket introuvable' });

  try {
    const objets = sqlite.prepare('SELECT * FROM objets_vendus WHERE id_ticket = ?').all(id);

    const totalAnnulation = -Math.abs(ticket.prix_total);
    const annul = sqlite.prepare(`
      INSERT INTO ticketdecaisse (
        date_achat_dt, correction_de, flag_correction, nom_vendeur, id_vendeur,
        nbr_objet, prix_total, moyen_paiement, uuid_ticket
      ) VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)
    `).run(
      now, ticket.id_ticket, ticket.nom_vendeur, ticket.id_vendeur,
      objets.length, totalAnnulation, ticket.moyen_paiement, require('uuid').v4()
    );
    const id_annul = annul.lastInsertRowid;

    const insertArticle = sqlite.prepare(`
      INSERT INTO objets_vendus (
        id_ticket, nom, prix, nbr, categorie,
        nom_vendeur, id_vendeur, date_achat, timestamp, uuid_objet
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);
    const timestamp = Math.floor(Date.now() / 1000);

    for (const obj of objets) {
      insertArticle.run(
        id_annul,
        obj.nom,
        obj.prix,
        -obj.nbr,
        obj.categorie,
        ticket.nom_vendeur,
        ticket.id_vendeur,
        now,
        timestamp,
        require('uuid').v4()
      );
    }

    // Mise à jour bilan
    const today = now.slice(0, 10);
    const bilan = sqlite.prepare('SELECT * FROM bilan WHERE date = ?').get(today);
    if (bilan) {
      sqlite.prepare(`
        UPDATE bilan SET
          prix_total = prix_total + ?,
          prix_total_espece = prix_total_espece + ?,
          prix_total_carte = prix_total_carte + ?,
          prix_total_cheque = prix_total_cheque + ?,
          prix_total_virement = prix_total_virement + ?
        WHERE date = ?
      `).run(
        totalAnnulation,
        ticket.moyen_paiement === 'espèces' ? totalAnnulation : 0,
        ticket.moyen_paiement === 'carte' ? totalAnnulation : 0,
        ticket.moyen_paiement === 'chèque' ? totalAnnulation : 0,
        ticket.moyen_paiement === 'virement' ? totalAnnulation : 0,
        today
      );
    }

    // ✅ Journalisation de la suppression
    sqlite.prepare(`
      INSERT INTO journal_corrections (date_correction, id_ticket_original, id_ticket_annulation, id_ticket_correction, utilisateur, motif)
      VALUES (?, ?, ?, NULL, ?, 'Suppression demandée')
    `).run(now, ticket.id_ticket, id_annul, ticket.nom_vendeur);

    const io = req.app.get('socketio');
    if (io) {
      io.emit('bilanUpdated');
      io.emit('ticketsmisajour');
    }

    return res.json({ success: true, id_annul });
  } catch (err) {
    console.error('Erreur suppression/annulation :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
});




module.exports = router;
