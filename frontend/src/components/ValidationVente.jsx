import React, { useState, useEffect } from 'react';

function ValidationVente({ total, id_temp_vente, onValide }) {
  const [reduction, setReduction] = useState('');
  const [paiement, setPaiement] = useState('');
  const [reductionsDisponibles, setReductionsDisponibles] = useState([]);

  useEffect(() => {
    if (total < 5000) { // 50,00€ en centimes
      setReductionsDisponibles([
        { value: 'trueClient', label: 'Fidélité Client (-5€)' },
        { value: 'trueBene', label: 'Fidélité Bénévole (-10€)' },
      ]);
    } else {
      setReductionsDisponibles([
        { value: 'trueGrosPanierClient', label: 'Gros Panier Client (-10%)' },
        { value: 'trueGrosPanierBene', label: 'Gros Panier Bénévole (-20%)' },
      ]);
    }
  }, [total]);

  const validerVente = () => {
    if (!paiement) {
      alert('Veuillez choisir un mode de paiement');
      return;
    }

    const data = {
      id_temp_vente,
      reductionType: reduction || null,
      moyenPaiement: paiement
    };

    fetch('http://localhost:3001/api/valider', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          alert('Vente validée avec succès');
          onValide(); // on recharge les ventes etc
        } else {
          alert('Erreur pendant validation');
        }
      })
      .catch(err => {
        console.error('Erreur lors de la validation', err);
        alert('Erreur de communication');
      });
  };

  return (
    <div className="p-3 bg-white border rounded shadow-sm">
      <h5>Finaliser la vente</h5>

      <div className="mb-2">
        <label>Réduction :</label>
        <select
          className="form-select"
          value={reduction}
          onChange={(e) => setReduction(e.target.value)}
        >
          <option value="">Aucune</option>
          {reductionsDisponibles.map(red => (
            <option key={red.value} value={red.value}>
              {red.label}
            </option>
          ))}
        </select>
      </div>

      <div className="mb-2">
        <label>Moyen de paiement :</label>
        <select
          className="form-select"
          value={paiement}
          onChange={(e) => setPaiement(e.target.value)}
        >
          <option value="">Sélectionner...</option>
          <option value="espèces">Espèces</option>
          <option value="chèque">Chèque</option>
          <option value="carte">Carte</option>
          <option value="virement">Virement</option>
        </select>
      </div>

      <button
        className="btn btn-success w-100 mt-3"
        onClick={validerVente}
      >
        Valider la vente
      </button>
    </div>
  );
}

export default ValidationVente;
