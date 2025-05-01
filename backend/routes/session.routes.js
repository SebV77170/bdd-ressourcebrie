const express = require('express');
const router = express.Router();
const db = require('../db');
const session = require('../session');


// Connexion par pseudo simple
router.post('/', (req, res) => {
  const { pseudo } = req.body;
  const user = db.prepare('SELECT * FROM users WHERE pseudo = ?').get(pseudo);
  if (!user) return res.status(404).json({ error: 'Utilisateur non trouvé' });

  session.setUser({ id: user.id, nom: user.nom });
  res.json({ success: true, user: { id: user.id, nom: user.nom } });
});

router.get('/', (req, res) => {
  const user = session.getUser();
  if (!user) return res.status(401).json({ error: 'Aucun utilisateur connecté' });
  res.json({ user });
});

router.delete('/', (req, res) => {
  session.clearUser();
  res.json({ success: true });
});

module.exports = router;
