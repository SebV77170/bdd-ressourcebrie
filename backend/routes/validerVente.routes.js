const express = require('express');
const router = express.Router();
const db = require('../db');
const fs = require('fs');
const path = require('path');

router.post('/', (req, res) => {
  const { id_temp_vente, reductionType, moyenPaiement } = req.body;

  if (!id_temp_vente || !moyenPaiement) {
    return res.status(400).json({ error: 'Informations manquantes' });
  }

  // 1. Récupérer tous les articles du ticket temporaire
  db.query('SELECT * FROM ticketdecaissetemp WHERE id_temp_vente = ?', [id_temp_vente], (err, articles) => {
    if (err) return res.status(500).json({ error: err });

    if (articles.length === 0) {
      return res.status(400).json({ error: 'Aucun article dans le ticket' });
    }

    const totalInitial = articles.reduce((sum, item) => sum + item.prixt, 0);
    let prixTotal = totalInitial;
    let reducBene = 0, reducClient = 0, reducGrosPanierClient = 0, reducGrosPanierBene = 0;

    // 2. Appliquer la réduction si besoin
    if (reductionType) {
      if (reductionType === 'trueClient') {
        prixTotal -= 500;
        reducClient = 1;
      } else if (reductionType === 'trueBene') {
        prixTotal -= 1000;
        reducBene = 1;
      } else if (reductionType === 'trueGrosPanierClient') {
        prixTotal = Math.round(prixTotal * 0.9);
        reducGrosPanierClient = 1;
      } else if (reductionType === 'trueGrosPanierBene') {
        prixTotal = Math.round(prixTotal * 0.8);
        reducGrosPanierBene = 1;
      }
    }

    if (prixTotal < 0) prixTotal = 0;

    const vendeur = "Inconnu"; // À remplacer si tu as un système de session
    const id_vendeur = 1; // À remplacer aussi
    const date_achat = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const lien = '';

    // 3. Insérer dans ticketdecaisse
    const insertVente = `
      INSERT INTO ticketdecaisse (nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;

    db.query(insertVente, [
      vendeur,
      id_vendeur,
      date_achat,
      articles.length,
      moyenPaiement,
      prixTotal,
      lien,
      reducBene,
      reducClient,
      reducGrosPanierClient,
      reducGrosPanierBene
    ], (err, result) => {
      if (err) return res.status(500).json({ error: err });

      const id_ticket = result.insertId;

      // 4. Déplacer les articles dans objets_vendus
      const insertVendus = `
        INSERT INTO objets_vendus (id_ticket, nom, nom_vendeur, id_vendeur, categorie, souscat, date_achat, timestamp, prix, nbr)
        VALUES ?
      `;

      const dataVendus = articles.map(item => [
        id_ticket,
        item.nom,
        vendeur,
        id_vendeur,
        item.categorie,
        item.souscat,
        date_achat,
        Math.floor(Date.now() / 1000),
        item.prix,
        item.nbr
      ]);

      db.query(insertVendus, [dataVendus], (err2) => {
        if (err2) return res.status(500).json({ error: err2 });

        // 5. Créer le fichier ticket
        const ticketPath = path.join(__dirname, `../../tickets/Ticket${id_ticket}.txt`);
        let contenu = `RESSOURCE'BRIE\nAssociation loi 1901\nTicket de caisse #${id_ticket}\nDate : ${date_achat}\nVendeur : ${vendeur}\n\n`;

        articles.forEach(article => {
          const prixArticle = (article.prix * article.nbr) / 100;
          contenu += `${article.nbr} x ${article.nom} (${article.categorie}) - ${prixArticle.toFixed(2)}€\n`;
        });

        contenu += `\nTOTAL : ${(prixTotal / 100).toFixed(2)}€\nMoyen de paiement : ${moyenPaiement}\nMerci de votre visite !\n`;

        fs.writeFileSync(ticketPath, contenu, 'utf8');

        // 6. Mettre à jour le lien du ticket
        db.query('UPDATE ticketdecaisse SET lien = ? WHERE id_ticket = ?', [`tickets/Ticket${id_ticket}.txt`, id_ticket]);

        // 7. Supprimer la vente temporaire
        db.query('DELETE FROM vente WHERE id_temp_vente = ?', [id_temp_vente]);
        db.query('DELETE FROM ticketdecaissetemp WHERE id_temp_vente = ?', [id_temp_vente]);

        res.status(200).json({ success: true, id_ticket });
      });
    });
  });
});

module.exports = router;
