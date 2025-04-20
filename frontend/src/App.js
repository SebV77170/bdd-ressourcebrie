import React, { useEffect, useState } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';

function App() {
  const [boutons, setBoutons] = useState({});
  const [ticket, setTicket] = useState([]);
  const [categorieActive, setCategorieActive] = useState('');
  const [paiement, setPaiement] = useState('');

  useEffect(() => {
    fetch('http://localhost:3001/api/produits/organises')
      .then(res => res.json())
      .then(data => {
        setBoutons(data);
        const categories = Object.keys(data);
        if (categories.length > 0) {
          setCategorieActive(categories[0]);
        }
      })
      .catch(err => console.error('Erreur lors du chargement des produits:', err));
  }, []);

  useEffect(() => {
    chargerTicket();
  }, []);
  

  const chargerTicket = () => {
    fetch('http://localhost:3001/api/ticket')
      .then(res => res.json())
      .then(data => setTicket(data))
      .catch(err => console.error('Erreur chargement ticket:', err));
  };
  

  const ajouterAuTicket = (produit) => {
    fetch('http://localhost:3001/api/ticket', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id_produit: produit.id_bouton,
        quantite: 1
      })
    })
      .then(res => res.json())
      .then(() => {
        chargerTicket(); // ← Pour recharger les lignes depuis MySQL
      })
      .catch(err => console.error('Erreur ajout ticket:', err));
  };
  
  const supprimerArticle = (id) => {
    fetch(`http://localhost:3001/api/ticket/${id}`, {
      method: 'DELETE'
    })
      .then(res => res.json())
      .then(() => {
        chargerTicket(); // Recharge la liste après suppression
      })
      .catch(err => console.error('Erreur suppression article:', err));
  };
  

  const validerVente = () => {
    if (!paiement) {
      alert('Veuillez sélectionner un mode de paiement.');
      return;
    }
    // Logique de validation à implémenter
    alert(`Vente validée avec le mode de paiement: ${paiement}`);
    setTicket([]);
    setPaiement('');
  };

  return (
    <div className="container-fluid">
      <div className="row">
        {/* Barre latérale des catégories */}
        <div className="col-md-2 bg-light vh-100">
          <h5 className="mt-3">Catégories</h5>
          <ul className="nav flex-column">
            {Object.keys(boutons).map((categorie, index) => (
              <li key={index} className="nav-item">
                <button
                  className={`nav-link btn btn-link ${categorieActive === categorie ? 'active' : ''}`}
                  onClick={() => setCategorieActive(categorie)}
                >
                  {categorie}
                </button>
              </li>
            ))}
          </ul>
        </div>

        {/* Zone des boutons produits */}
        <div className="col-md-7">
          <h5 className="mt-3">{categorieActive}</h5>
          <div className="row">
            {categorieActive && boutons[categorieActive] &&
              Object.entries(boutons[categorieActive]).map(([sousCategorie, produits], index) => (
                <div key={index} className="mb-3">
                  <h6>{sousCategorie}</h6>
                  <div className="d-flex flex-wrap">
                    {produits.map((produit) => (
                      <button
                        key={produit.id_bouton}
                        className={`btn btn-${produit.color || 'secondary'} m-1`}
                        onClick={() => ajouterAuTicket(produit)}
                      >
                        {produit.nom} - {(produit.prix / 100).toFixed(2)}€
                      </button>
                    ))}
                  </div>
                </div>
              ))}
          </div>
        </div>

        {/* Colonne du ticket */}
        <div className="col-md-3 bg-light vh-100 d-flex flex-column justify-content-between">
          <div>
            <h5 className="mt-3">Ticket</h5>
            <ul className="list-group">
            {ticket.map((item, index) => (
              <li key={index} className="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <button
                    className="btn btn-sm btn-outline-danger me-2"
                    onClick={() => supprimerArticle(item.id)}
                    title="Supprimer"
                  >
                    🗑️
                  </button>
                  {item.nom}
                </div>
                <span>{(item.prixt / 100).toFixed(2)} €</span>
              </li>
            ))}
          </ul>

            <h5 className="mt-3">Total: {(ticket.reduce((total, item) => total + item.prix, 0) / 100).toFixed(2)}€</h5>
          </div>
          <div className="mb-3">
            <label htmlFor="paiement" className="form-label">Mode de paiement</label>
            <select
              id="paiement"
              className="form-select"
              value={paiement}
              onChange={(e) => setPaiement(e.target.value)}
            >
              <option value="">-- Sélectionner --</option>
              <option value="espèces">Espèces</option>
              <option value="carte">Carte</option>
              <option value="chèque">Chèque</option>
              <option value="virement">Virement</option>
            </select>
            <button className="btn btn-success w-100 mt-2" onClick={validerVente}>
              Valider la vente
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;
