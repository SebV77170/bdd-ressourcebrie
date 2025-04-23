const express = require('express');
const router = express.Router();
const db = require('../db');

// Ajouter un article au ticket
router.post('/', (req, res) => {
  const { id_produit, quantite, id_temp_vente } = req.body;

  const sql = `
    INSERT INTO ticketdecaissetemp (id_temp_vente, nom, categorie, souscat, prix, nbr, prixt)
    SELECT ?, bv.nom, cat1.category AS categorie, cat2.category AS souscat, bv.prix, ?, bv.prix * ?
    FROM boutons_ventes bv
    LEFT JOIN categories cat1 ON bv.id_cat = cat1.id
    LEFT JOIN categories cat2 ON bv.id_souscat = cat2.id
    WHERE bv.id_bouton = ?
  `;

  db.query(sql, [id_temp_vente, quantite, quantite, id_produit], (err, result) => {
    if (err) {
      console.error('❌ ERREUR SQL :', err.sqlMessage);
      return res.status(500).json({ error: err.sqlMessage });
    }
    res.status(200).json({ success: true });
  });
});


// Lire les articles d'un ticket
router.get('/:id_temp_vente', (req, res) => {
  const id = req.params.id_temp_vente;
  db.query('SELECT * FROM ticketdecaissetemp WHERE id_temp_vente = ?', [id], (err, rows) => {
    if (err) return res.status(500).json({ error: err });
    res.json(rows);
  });
});

// Supprimer un article du ticket
router.delete('/:id', (req, res) => {
  db.query('DELETE FROM ticketdecaissetemp WHERE id = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ error: err });
    res.json({ success: true });
  });
});

// Mettre à jour une info d'un article
router.put('/:id', (req, res) => {
  const { champ, valeur } = req.body;
  const id = req.params.id;
  const sql = `UPDATE ticketdecaissetemp SET ?? = ? WHERE id = ?`;

  db.query(sql, [champ, valeur, id], (err, result) => {
    if (err) return res.status(500).json({ error: err });

    // Recalculer le total (prixt) si champ modifié est nbr ou prix
    if (champ === 'nbr' || champ === 'prix') {
      const recalcul = `UPDATE ticketdecaissetemp SET prixt = prix * nbr WHERE id = ?`;
      db.query(recalcul, [id], (err2) => {
        if (err2) return res.status(500).json({ error: err2 });
        res.json({ success: true });
      });
    } else {
      res.json({ success: true });
    }
  });
});

module.exports = router;
