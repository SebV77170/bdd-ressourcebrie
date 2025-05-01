import React, { useState } from 'react';

function CorrectionForm({ ticketOriginal, onSuccess }) {
  const [corrections, setCorrections] = useState(ticketOriginal.objets);
  const [motif, setMotif] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (index, field, value) => {
    const updated = [...corrections];
    updated[index][field] = field === 'nbr' || field === 'prix' ? parseInt(value) : value;
    setCorrections(updated);
  };

  const envoyerCorrection = async () => {
    if (!motif.trim()) return alert('Merci de préciser un motif.');

    setLoading(true);
    const body = {
      id_ticket_original: ticketOriginal.ticket.id_ticket,
      articles_origine: ticketOriginal.objets,
      articles_correction: corrections,
      utilisateur: 'admin', // ou ton système d’auth
      motif
    };

    try {
      const res = await fetch('http://localhost:3001/api/correction', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      const result = await res.json();
      if (result.success) {
        alert('Correction enregistrée.');
        onSuccess();
      } else {
        alert('Erreur lors de la correction.');
      }
    } catch (err) {
      console.error(err);
      alert('Erreur réseau.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-3 border bg-white rounded">
      <h5>Corriger le ticket #{ticketOriginal.ticket.id_ticket}</h5>

      <div className="mb-3">
        {corrections.map((art, index) => (
          <div className="d-flex gap-2 mb-2" key={index}>
            <input className="form-control" value={art.nom} disabled />
            <input
              className="form-control"
              type="number"
              value={art.nbr}
              onChange={(e) => handleChange(index, 'nbr', e.target.value)}
            />
            <input
              className="form-control"
              type="number"
              value={art.prix}
              onChange={(e) => handleChange(index, 'prix', e.target.value)}
            />
            <input className="form-control" value={art.categorie} disabled />
          </div>
        ))}
      </div>

      <textarea
        className="form-control mb-2"
        placeholder="Motif de la correction"
        value={motif}
        onChange={(e) => setMotif(e.target.value)}
      />

      <button className="btn btn-warning w-100" onClick={envoyerCorrection} disabled={loading}>
        {loading ? 'Correction en cours...' : 'Envoyer la correction'}
      </button>
    </div>
  );
}

export default CorrectionForm;
