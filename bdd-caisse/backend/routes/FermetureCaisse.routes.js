const express = require('express');
const router = express.Router();
const { sqlite } = require('../db');
const bcrypt = require('bcrypt');
const session = require('../session');
const logSync = require('../logsync');

router.post('/', (req, res) => {
  const { montant_reel, commentaire, responsable_pseudo, mot_de_passe, montant_reel_carte, montant_reel_cheque, montant_reel_virement } = req.body;
  const utilisateur = session.getUser();

  if (!utilisateur) {
    return res.status(401).json({ error: 'Aucun utilisateur connecté' });
  }

  const sessionCaisse = sqlite.prepare(`
    SELECT * FROM session_caisse WHERE date_fermeture IS NULL
  `).get();

  if (!sessionCaisse) {
    return res.status(400).json({ error: 'Aucune session caisse ouverte' });
  }

  const responsable = sqlite.prepare(`
    SELECT * FROM users WHERE pseudo = ? AND admin >= 2
  `).get(responsable_pseudo);

  if (!responsable) {
    return res.status(403).json({ error: 'Responsable introuvable' });
  }

  const motDePasseValide = bcrypt.compareSync(
    mot_de_passe.trim(),
    responsable.password.replace(/^\$2y\$/, '$2b$')
  );

  if (!motDePasseValide) {
    return res.status(403).json({ error: 'Mot de passe responsable invalide' });
  }

  const now = new Date();
  const date_fermeture = now.toISOString().slice(0, 10);
  const heure_fermeture = now.toTimeString().slice(0, 5);

  const ventesJour = sqlite.prepare(`
    SELECT * FROM bilan
    WHERE date = ?
  `).get(date_fermeture);

  const fond_de_caisse = sessionCaisse.fond_initial*100 || 0;
  const attendu_espece = ventesJour.prix_total_espece ?? 0;
  const attendu_carte = ventesJour.prix_total_carte ?? 0;
  const attendu_cheque = ventesJour.prix_total_cheque ?? 0;
  const attendu_virement = ventesJour.prix_total_virement ?? 0;
  const ecart_espece = montant_reel - attendu_espece - fond_de_caisse;
  const ecart_carte = montant_reel_carte - attendu_carte;
  const ecart_cheque = montant_reel_cheque - attendu_cheque;
  const ecart_virement = montant_reel_virement - attendu_virement;
  const ecart = ecart_espece + ecart_carte + ecart_cheque + ecart_virement;

  console.log(attendu_carte, attendu_cheque, attendu_virement);

  sqlite.prepare(`
    UPDATE session_caisse
    SET 
      date_fermeture = ?,
      heure_fermeture = ?,
      utilisateur_fermeture = ?,
      responsable_fermeture = ?,
      montant_reel = ?,
      commentaire = ?,
      ecart = ?,
      montant_reel_carte = ?,
      montant_reel_cheque = ?,
      montant_reel_virement = ?
    WHERE id_session = ?
  `).run(
    date_fermeture,
    heure_fermeture,
    utilisateur.nom,
    responsable.nom,
    montant_reel,
    commentaire,
    ecart,
    montant_reel_carte,
    montant_reel_cheque,
    montant_reel_virement,
    sessionCaisse.id_session
  );

  logSync('session_caisse', 'UPDATE', {
    id_session: sessionCaisse.id_session,
    date_fermeture,
    heure_fermeture,
    utilisateur_fermeture: utilisateur.nom,
    responsable_fermeture: responsable.nom,
    montant_reel,
    commentaire,
    ecart,
    montant_reel_carte,
    montant_reel_cheque,
    montant_reel_virement
  });

  const io = req.app.get('socketio');
  if (io) io.emit('etatCaisseUpdated', { ouverte: false }); 

  res.json({ success: true });
});

module.exports = router;
