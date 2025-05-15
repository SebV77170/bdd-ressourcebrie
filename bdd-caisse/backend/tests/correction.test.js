const request = require('supertest');
const app = require('../app');
const db = require('../db');
const fs = require('fs');

// Mock de la session utilisateur
jest.mock('../session', () => ({
  getUser: () => ({ id: 1, nom: 'Testeur' })
}));

describe('Correction de ticket', () => {
  let id_ticket_original;

  beforeAll(() => {
    // Crée un ticket de test
    const insert = db.prepare(`
      INSERT INTO ticketdecaisse (date_achat_dt, nom_vendeur, id_vendeur, nbr_objet, prix_total, moyen_paiement)
      VALUES (?, ?, ?, ?, ?, ?)
    `).run(new Date().toISOString(), 'Testeur', 1, 1, 300, 'carte');

    id_ticket_original = insert.lastInsertRowid;

    // Crée une ligne d'article dans objets_vendus
    db.prepare(`
      INSERT INTO objets_vendus (id_ticket, nom, prix, nbr, categorie, nom_vendeur, id_vendeur, date_achat, timestamp)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
      id_ticket_original,
      'Produit test',
      300,
      1,
      'Test',
      'Testeur',
      1,
      new Date().toISOString(),
      Math.floor(Date.now() / 1000)
    );
  });

  it('doit créer un ticket de correction et un ticket d\'annulation', async () => {
    const response = await request(app)
      .post('/api/correction')
      .send({
        id_ticket_original,
        articles_origine: [{ nom: 'Produit test', prix: 300, nbr: 1, categorie: 'Test' }],
        articles_correction: [{ nom: 'Produit test', prix: 300, nbr: 1, categorie: 'Test' }],
        motif: 'Erreur test',
        moyen_paiement: 'espèces',
        reductionType: 'trueClient',
        paiements: [{ moyen: 'espèces', montant: 0 }]
      });

    expect(response.statusCode).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.id_annul).toBeDefined();
    expect(response.body.id_corrige).toBeDefined();

    // Vérifie la génération des fichiers tickets
    expect(fs.existsSync(`../tickets/Ticket${response.body.id_annul}.txt`)).toBe(true);
    expect(fs.existsSync(`../tickets/Ticket${response.body.id_corrige}.txt`)).toBe(true);
  });
});
