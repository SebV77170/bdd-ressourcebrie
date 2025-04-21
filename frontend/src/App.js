import React, { useEffect, useState } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/App.scss';


function App() {
  const [boutons, setBoutons] = useState({});
  const [categorieActive, setCategorieActive] = useState('');
  const [ticketModif, setTicketModif] = useState([]);
  const [paiement, setPaiement] = useState('');
  const [modifs, setModifs] = useState({});

  useEffect(() => {
    fetch('http://localhost:3001/api/produits/organises')
      .then(res => res.json())
      .then(data => {
        setBoutons(data);
        const categories = Object.keys(data);
        if (categories.length > 0) setCategorieActive(categories[0]);
      });
  }, []);

  const chargerTicket = () => {
    fetch('http://localhost:3001/api/ticket')
      .then(res => res.json())
      .then(data => {
        setTicketModif(data);
        setModifs({});
      });
  };

  useEffect(() => {
    chargerTicket();
  }, []);

  const ajouterAuTicket = (produit) => {
    fetch('http://localhost:3001/api/ticket', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_produit: produit.id_bouton, quantite: 1 })
    }).then(() => chargerTicket());
  };

  const supprimerArticle = (id) => {
    fetch(`http://localhost:3001/api/ticket/${id}`, { method: 'DELETE' })
      .then(() => chargerTicket());
  };

  const handleInputChange = (id, champ, valeur) => {
    const nouveau = ticketModif.map(item =>
      item.id === id ? { ...item, [champ]: valeur } : item
    );
    setTicketModif(nouveau);
    setModifs(prev => ({
      ...prev,
      [id]: { ...prev[id], [champ]: valeur }
    }));
  };

  const enregistrerModifs = (id) => {
    const changements = modifs[id];
    if (!changements) return;

    const requetes = Object.entries(changements).map(([champ, valeur]) =>
      fetch(`http://localhost:3001/api/ticket/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ champ, valeur })
      })
    );

    Promise.all(requetes)
      .then(() => {
        const newModifs = { ...modifs };
        delete newModifs[id];
        setModifs(newModifs);
        chargerTicket();
      });
  };

  const validerVente = () => {
    if (!paiement) return alert("Sélectionnez un mode de paiement.");
    fetch('http://localhost:3001/api/valider', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ moyenPaiement: paiement })
    })
      .then(() => {
        alert(`Vente validée en ${paiement}`);
        setPaiement('');
        chargerTicket();
      });
  };

  return (
    <div className="container-fluid">
      <div className="row">
        <div className="col-md-2 bg-light vh-100 overflow-auto pt-3">
          <h5>Catégories</h5>
          <ul className="nav flex-column">
            {Object.keys(boutons).map((cat, i) => (
              <li key={i} className="nav-item">
                <button className={`nav-link btn btn-link ${categorieActive === cat ? 'fw-bold text-primary' : ''}`} onClick={() => setCategorieActive(cat)}>
                  {cat}
                </button>
              </li>
            ))}
          </ul>
        </div>

        <div className="col-md-6 pt-3 overflow-auto" style={{ maxHeight: '100vh' }}>
          <h4>{categorieActive}</h4>
          {categorieActive && boutons[categorieActive] && Object.entries(boutons[categorieActive]).map(([sousCat, produits], i) => (
            <div key={i}>
              <h6 className="mt-3">{sousCat}</h6>
              <div className="d-flex flex-wrap">
                {produits.map(prod => (
                  <button
                    key={prod.id_bouton}
                    className={`btn btn-${prod.color || 'secondary'} m-1`}
                    onClick={() => ajouterAuTicket(prod)}
                  >
                    {(prod.prix / 100).toFixed(2)} € - {prod.nom}
                  </button>
                ))}
              </div>
            </div>
          ))}
        </div>

        <div className="col-md-4 bg-light pt-3 d-flex flex-column justify-content-between vh-100">
          <div>
            <h5>Ticket</h5>
            <ul className="list-group">
              {Array.isArray(ticketModif) && ticketModif.map(item => (
                <li key={item.id} className="list-group-item">
                  <div className="d-flex justify-content-between align-items-center">
                    <div>
                      <button className="btn btn-sm btn-outline-danger me-2" onClick={() => supprimerArticle(item.id)}>🗑️</button>
                      <strong>{item.nom}</strong>
                    </div>
                    <div className="d-flex align-items-center">
                    <input
                      type="number"
                      value={item.nbr}
                      onChange={(e) => handleInputChange(item.id, 'nbr', parseInt(e.target.value))}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                          enregistrerModifs(item.id);
                          e.target.blur(); // pour que l’utilisateur voie le résultat
                        }
                      }}
                      className="form-control form-control-sm mx-1"
                      style={{ width: "50px" }}
                    />

                    <input
                      type="number"
                      value={(item.prix / 100).toFixed(2)}
                      onChange={(e) =>
                        handleInputChange(item.id, 'prix', Math.round(parseFloat(e.target.value) * 100))
                      }
                      onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                          enregistrerModifs(item.id);
                          e.target.blur(); // sort du champ pour recharger la valeur
                        }
                      }}
                      className="form-control form-control-sm mx-1"
                      style={{ width: "70px" }}
                    />

                      <span className="ms-2">{(item.prixt / 100).toFixed(2)} €</span>
                    
                    </div>
                  </div>
                </li>
              ))}
            </ul>

            <h5 className="mt-3">
              Total: {(ticketModif.reduce((total, item) => total + (item.prixt || 0), 0) / 100).toFixed(2)} €
            </h5>
          </div>

          <div className="mb-3">
            <label className="form-label">Mode de paiement</label>
            <select className="form-select" value={paiement} onChange={(e) => setPaiement(e.target.value)}>
              <option value="">-- Choisir --</option>
              <option value="espèces">Espèces</option>
              <option value="carte">Carte</option>
              <option value="chèque">Chèque</option>
              <option value="virement">Virement</option>
            </select>
            <button className="btn btn-success mt-2 w-100" onClick={validerVente}>
              Valider la vente
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;