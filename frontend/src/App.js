import React, { useEffect, useState, useCallback } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/App.scss';
import VenteSelector from './components/VenteSelector';
import CategorieSelector from './components/CategorieSelector';
import BoutonsCaisse from './components/BoutonsCaisse';
import TicketVente from './components/TicketVente';
import PaiementForm from './components/PaiementForm';

function App() {
  const [boutons, setBoutons] = useState({});
  const [categorieActive, setCategorieActive] = useState('');
  const [ticketModif, setTicketModif] = useState([]);
  const [paiement, setPaiement] = useState('');
  const [modifs, setModifs] = useState({});
  const [ventes, setVentes] = useState([]);
  const [venteActive, setVenteActive] = useState(null);

  useEffect(() => {
    fetch('http://localhost:3001/api/produits/organises')
      .then(res => res.json())
      .then(data => {
        setBoutons(data);
        const categories = Object.keys(data);
        if (categories.length > 0) setCategorieActive(categories[0]);
      });
  }, []);

  const chargerVentes = useCallback(() => {
    fetch('http://localhost:3001/api/ventes')
      .then(res => res.json())
      .then(data => {
        setVentes(data);
        if (!venteActive && data.length > 0) {
          setVenteActive(data[0].id_temp_vente);
        }
      });
  }, [venteActive]);

  const chargerTicket = useCallback(() => {
    if (!venteActive) return;
    fetch(`http://localhost:3001/api/ticket/${venteActive}`)
      .then(res => res.json())
      .then(data => {
        setTicketModif(data);
        setModifs({});
      });
  }, [venteActive]);

  useEffect(() => {
    chargerVentes();
  }, [chargerVentes]);

  useEffect(() => {
    chargerTicket();
  }, [chargerTicket]);

  const nouvelleVente = () => {
    fetch('http://localhost:3001/api/ventes', { method: 'POST' })
      .then(res => res.json())
      .then(data => {
        setVenteActive(data.id_temp_vente);
        chargerVentes();
      });
  };

  const ajouterAuTicket = (produit) => {
    if (!venteActive) return;
    fetch('http://localhost:3001/api/ticket', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_produit: produit.id_bouton, quantite: 1, id_temp_vente: venteActive })
    })
      .then(() => chargerTicket())
      .catch(err => console.error('Erreur dans fetch ticket:', err));
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
    if (!paiement || !venteActive) return alert("Sélectionnez un mode de paiement.");
    fetch('http://localhost:3001/api/valider', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ moyenPaiement: paiement, id_temp_vente: venteActive })
    })
      .then(() => {
        alert(`Vente ${venteActive} validée en ${paiement}`);
        setPaiement('');
        setVenteActive(null);
        chargerVentes();
      });
  };

  const annulerVente = () => {
    if (!venteActive) return;
  
    const confirmer = window.confirm("Confirmer l'annulation de la vente ?");
    if (!confirmer) return;
  
    fetch(`http://localhost:3001/api/ventes/${venteActive}`, {
      method: 'DELETE'
    })
      .then(() => {
        setVenteActive(null);
        setTicketModif([]);
        setModifs({});
        chargerVentes(); // recharge les autres ventes
      })
      .catch(err => console.error("Erreur lors de l'annulation :", err));
  };
  

  return (
    <div className="container-fluid p-0">
      {/* Bandeau horizontal ventes sticky */}
      <div className="bg-light border-bottom sticky-top py-2 px-3" style={{ overflowX: 'auto', whiteSpace: 'nowrap' }}>
        <div className="d-inline-flex gap-2">
          <VenteSelector
            ventes={ventes}
            venteActive={venteActive}
            onSelect={setVenteActive}
            onNew={nouvelleVente}
          />
          <button
            className="btn btn-sm btn-outline-danger mt-3"
            onClick={annulerVente}
          >
            ❌ Annuler la vente
          </button>

        </div>
      </div>

      <div className="row m-0">
        <div className="col-md-2 bg-light vh-100 overflow-auto pt-3">
          <CategorieSelector
            categories={Object.keys(boutons)}
            active={categorieActive}
            onSelect={setCategorieActive}
          />
        </div>

        <div className="col-md-6 pt-3 overflow-auto" style={{ maxHeight: '100vh' }}>
          <h4>{categorieActive}</h4>
          {categorieActive && boutons[categorieActive] && (
            <BoutonsCaisse produits={boutons[categorieActive]} onClick={ajouterAuTicket} />
          )}
        </div>

        <div className="col-md-4 bg-light pt-3 d-flex flex-column justify-content-between vh-100">
          <TicketVente
            ticket={ticketModif}
            modifs={modifs}
            onChange={handleInputChange}
            onDelete={supprimerArticle}
            onSave={enregistrerModifs}
          />
          <PaiementForm
            paiement={paiement}
            onChange={setPaiement}
            onValider={validerVente}
          />
        </div>
      </div>
    </div>
  );
}

export default App;
