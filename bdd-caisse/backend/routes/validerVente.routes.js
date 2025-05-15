// ✅ Mise à jour de validerVente.routes.js avec gestion des réductions excessives, ventes gratuites et bilan cohérent
const express = require('express');
const router = express.Router();
const db = require('../db');
const fs = require('fs');
const path = require('path');
const session = require('../session');

router.post('/', (req, res) => {
  const { id_temp_vente, reductionType, paiements } = req.body;

  if (!id_temp_vente || !paiements || !Array.isArray(paiements) || paiements.length === 0) {
    return res.status(400).json({ error: 'Informations manquantes ou invalide' });
  }

  const user = session.getUser();
  if (!user) return res.status(401).json({ error: 'Aucun utilisateur connecté' });

  try {
    const articles = db.prepare('SELECT * FROM ticketdecaissetemp WHERE id_temp_vente = ?').all(id_temp_vente);
    if (articles.length === 0) return res.status(400).json({ error: 'Aucun article dans le ticket' });

    const vendeur = user.nom;
    const id_vendeur = user.id;
    const date_achat = new Date().toISOString().slice(0, 19).replace('T', ' ');
    let prixTotal = articles.reduce((sum, item) => sum + item.prixt, 0);
    let reducBene = 0, reducClient = 0, reducGrosPanierClient = 0, reducGrosPanierBene = 0;
    let reductionValeurTheorique = 0;
    let reductionValeurReelle = 0;

    const mapReductions = {
      'trueClient': { label: 'Fidélité client', montant: 500 },
      'trueBene': { label: 'Fidélité bénévole', montant: 1000 },
      'trueGrosPanierClient': { label: 'Gros panier client', taux: 0.10 },
      'trueGrosPanierBene': { label: 'Gros panier bénévole', taux: 0.20 }
    };

    if (reductionType && mapReductions[reductionType]) {
      const reduc = mapReductions[reductionType];

      if (reduc.montant) {
        reductionValeurTheorique = reduc.montant;
      } else if (reduc.taux) {
        reductionValeurTheorique = Math.round(prixTotal * reduc.taux);
      }

      reductionValeurReelle = Math.min(prixTotal, reductionValeurTheorique);
      prixTotal -= reductionValeurReelle;

      if (reductionType === 'trueClient') reducClient = 1;
      else if (reductionType === 'trueBene') reducBene = 1;
      else if (reductionType === 'trueGrosPanierClient') reducGrosPanierClient = 1;
      else if (reductionType === 'trueGrosPanierBene') reducGrosPanierBene = 1;
    }

    const moyenGlobal = paiements.length > 1 ? 'mixte' : paiements[0].moyen;
    const result = db.prepare(`
      INSERT INTO ticketdecaisse (nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(vendeur, id_vendeur, date_achat, articles.length, moyenGlobal, prixTotal, '', reducBene, reducClient, reducGrosPanierClient, reducGrosPanierBene);
    const id_ticket = result.lastInsertRowid;

    const insertArticle = db.prepare(`
      INSERT INTO objets_vendus (id_ticket, nom, nom_vendeur, id_vendeur, categorie, souscat, date_achat, timestamp, prix, nbr)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const insertMany = db.transaction((items) => {
      for (const item of items) {
        insertArticle.run(id_ticket, item.nom, vendeur, id_vendeur, item.categorie, item.souscat, date_achat, Math.floor(Date.now() / 1000), item.prix, item.nbr);
      }
    });
    insertMany(articles);

    if (reductionValeurReelle > 0) {
      db.prepare(`
        INSERT INTO objets_vendus
        (id_ticket, nom, nom_vendeur, id_vendeur, categorie, souscat, date_achat, timestamp, prix, nbr)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `).run(
        id_ticket,
        'Réduction',
        vendeur,
        id_vendeur,
        'Réduction',
        `${reductionType} (valeur théorique ${(reductionValeurTheorique / 100).toFixed(2)}€, réelle ${(reductionValeurReelle / 100).toFixed(2)}€)`,
        date_achat,
        Math.floor(Date.now() / 1000),
        reductionValeurReelle,
        -1
      );
    }

    const pm = { espece: 0, carte: 0, cheque: 0, virement: 0 };
    const normalisation = {
      'espèces': 'espece', 'espèce': 'espece', 'carte': 'carte',
      'chèque': 'cheque', 'chéque': 'cheque', 'cheque': 'cheque', 'virement': 'virement'
    };

    for (const p of paiements) {
      const champ = normalisation[p.moyen.toLowerCase()] || null;
      if (champ && pm.hasOwnProperty(champ)) {
        pm[champ] += p.montant;
      }
    }

    if (prixTotal > 0) {
      db.prepare(`INSERT INTO paiement_mixte (id_ticket, espece, carte, cheque, virement) VALUES (?, ?, ?, ?, ?)`).run(id_ticket, pm.espece, pm.carte, pm.cheque, pm.virement);
    }

    const ticketPath = path.join(__dirname, `../../tickets/Ticket${id_ticket}.txt`);
    let contenu = `RESSOURCE'BRIE\nAssociation loi 1901\nTicket de caisse #${id_ticket}\nDate : ${date_achat}\nVendeur : ${vendeur}\n\n`;
    const lignesTicket = db.prepare('SELECT * FROM objets_vendus WHERE id_ticket = ?').all(id_ticket);
    lignesTicket.forEach(a => {
      const ligne = a.nbr > 0
        ? `${a.nbr} x ${a.nom} (${a.categorie}) - ${(a.prix * a.nbr / 100).toFixed(2)}€\n`
        : `${a.nom} (${a.souscat}) : -${(a.prix / 100).toFixed(2)}€\n`;
      contenu += ligne;
    });
    contenu += `\nTOTAL : ${(prixTotal / 100).toFixed(2)}€\nPaiement : ${moyenGlobal}\n`;
    if (reductionValeurReelle > 0) {
      contenu += `Réduction appliquée : ${reductionType}\nValeur théorique : ${(reductionValeurTheorique / 100).toFixed(2)}€, réelle : ${(reductionValeurReelle / 100).toFixed(2)}€\n`;
    }
    contenu += `Merci de votre visite !\n`;
    fs.writeFileSync(ticketPath, contenu, 'utf8');

    db.prepare('UPDATE ticketdecaisse SET lien = ? WHERE id_ticket = ?').run(`tickets/Ticket${id_ticket}.txt`, id_ticket);
    db.prepare('DELETE FROM vente WHERE id_temp_vente = ?').run(id_temp_vente);
    db.prepare('DELETE FROM ticketdecaissetemp WHERE id_temp_vente = ?').run(id_temp_vente);

    const today = date_achat.slice(0, 10);
    const poids = articles.reduce((s, a) => s + (a.poids || 0), 0);
    const bilanExistant = db.prepare('SELECT * FROM bilan WHERE date = ?').get(today);
    const totalPaiement = pm.espece + pm.carte + pm.cheque + pm.virement;

    if (bilanExistant) {
      db.prepare(`
        UPDATE bilan
        SET nombre_vente = nombre_vente + 1,
            poids = poids + ?,
            prix_total = prix_total + ?,
            prix_total_espece = prix_total_espece + ?,
            prix_total_cheque = prix_total_cheque + ?,
            prix_total_carte = prix_total_carte + ?,
            prix_total_virement = prix_total_virement + ?
        WHERE date = ?
      `).run(
        poids,
        prixTotal,
        prixTotal === 0 ? 0 : pm.espece,
        prixTotal === 0 ? 0 : pm.cheque,
        prixTotal === 0 ? 0 : pm.carte,
        prixTotal === 0 ? 0 : pm.virement,
        today
      );
    } else {
      db.prepare(`
        INSERT INTO bilan (date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte, prix_total_virement)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      `).run(
        today, Math.floor(Date.now() / 1000), 1, poids, prixTotal,
        prixTotal === 0 ? 0 : pm.espece,
        prixTotal === 0 ? 0 : pm.cheque,
        prixTotal === 0 ? 0 : pm.carte,
        prixTotal === 0 ? 0 : pm.virement
      );
    }

    res.json({ success: true, id_ticket });
  } catch (err) {
    console.error('Erreur validation :', err);
    res.status(500).json({ error: err.message });
  }

  const io = req.app.get('socketio');
  if (io) {
    io.emit('bilanUpdated');
  }
});

module.exports = router;
