// fichier: routes/ouvertureCaisse.routes.js

const express = require('express');
const router = express.Router();
const { sqlite } = require('../db');
const session = require('../session');
const { v4: uuidv4 } = require('uuid');
const bcrypt = require('bcrypt');

router.post('/ouverture', (req, res) => {
  const { fond_initial, responsable_pseudo, mot_de_passe } = req.body;
  const utilisateur = session.getUser();

  if (!utilisateur) {
    return res.status(401).json({ error: 'Aucun utilisateur connecté' });
  }

  // Vérifier qu'il n'existe pas déjà une session caisse ouverte
  const sessionExistante = sqlite.prepare(`
    SELECT * FROM session_caisse 
    WHERE date_fermeture IS NULL
  `).get();

  if (sessionExistante) {
    return res.status(400).json({ error: 'Une session caisse est déjà ouverte' });
  }

  // Vérifier mot de passe du responsable
  const responsable = sqlite.prepare(`
  SELECT * FROM users 
  WHERE pseudo = ? AND admin >= 2
`).get(responsable_pseudo);

if (!responsable) {
  return res.status(403).json({ error: 'Responsable introuvable' });
}

// ✅ Corriger le préfixe $2y$ en $2b$ si nécessaire
const hashCorrige = responsable.password.replace(/^\$2y\$/, '$2b$');

// ✅ Comparaison
const motDePasseValide = bcrypt.compareSync(mot_de_passe.trim(), hashCorrige);

if (!motDePasseValide) {
  return res.status(403).json({ error: 'Mot de passe responsable invalide' });
}

  const now = new Date();
  const date_ouverture = now.toISOString().slice(0, 10);
  const heure_ouverture = now.toTimeString().slice(0, 5);

  const id_session = uuidv4();
  const caissiers = JSON.stringify([utilisateur.nom]);

  sqlite.prepare(`
    INSERT INTO session_caisse (
      id_session, date_ouverture, heure_ouverture, 
      utilisateur_ouverture, responsable_ouverture, 
      fond_initial, caissiers
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
  `).run(
    id_session,
    date_ouverture,
    heure_ouverture,
    utilisateur.nom,
    responsable.nom,
    fond_initial,
    caissiers
  );

  const io = req.app.get('socketio');
  if (io) io.emit('etatCaisseUpdated', { ouverte: true });

   res.json({ success: true, id_session });
});

module.exports = router;
