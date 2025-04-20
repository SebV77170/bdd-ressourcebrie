const express = require('express');
const router = express.Router();
const db = require('../db');

// Récupérer le ticket en cours
router.get('/', (req, res) => {
  db.query('SELECT * FROM ticketdecaissetemp', (err, results) => {
    if (err) return res.status(500).json({ error: err });
    res.json(results);
  });
});

// Ajouter un produit au ticket
router.post('/', (req, res) => {
  const { id_produit, quantite = 1 } = req.body;

  const sql = `
    INSERT INTO ticketdecaissetemp (
      id_temp_vente, id_vendeur, nom_vendeur, nom, categorie, souscat, prix, nbr, prixt
    )
    SELECT 
      0, 0, 'inconnu', b.nom, c1.category, c2.category, b.prix, ?, b.prix * ?
    FROM boutons_ventes b
    LEFT JOIN categories c1 ON b.id_cat = c1.id
    LEFT JOIN categories c2 ON b.id_souscat = c2.id
    WHERE b.id_bouton = ?
  `;

  db.query(sql, [quantite, quantite, id_produit], (err, result) => {
    if (err) return res.status(500).json({ error: err });
    res.json({ success: true });
  });
});

// Supprimer un produit du ticket
router.delete('/:id', (req, res) => {
  const id = req.params.id;
  db.query('DELETE FROM ticketdecaissetemp WHERE id = ?', [id], (err) => {
    if (err) return res.status(500).json({ error: err });
    res.json({ success: true });
  });
});

module.exports = router;
