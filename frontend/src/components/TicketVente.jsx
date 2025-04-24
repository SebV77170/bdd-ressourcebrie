import React, { useState } from 'react';



function TicketVente({ ticket, modifs, onChange, onDelete, onSave }) {
  const total = ticket.reduce((sum, item) => sum + (item.prixt || 0), 0);
  const [localPrix, setLocalPrix] = useState({});

  const handleSavePrix = (id) => {
    const raw = localPrix[id];
    if (!raw || raw.trim() === '') return;
  
    const parsed = parseFloat(raw.replace(',', '.'));
    if (!isNaN(parsed)) {
      const prixCents = Math.round(parsed * 100);
      onChange(id, 'prix', prixCents);
      onSave(id);
    }
  
    setLocalPrix(prev => {
      const copy = { ...prev };
      delete copy[id];
      return copy;
    });
  };
  

  return (
    <>
      <h5>Ticket</h5>
      <ul className="list-group">
        {ticket.map(item => (
          <li key={item.id} className="list-group-item">
            <div className="d-flex justify-content-between align-items-center">
              <div>
                <button className="btn btn-sm btn-outline-danger me-2" onClick={() => onDelete(item.id)}>🗑️</button>
                <strong>{item.nom}</strong>
              </div>
              <div className="d-flex align-items-center">
                <input
                  type="number"
                  value={item.nbr}
                  onChange={(e) => onChange(item.id, 'nbr', parseInt(e.target.value))}
                  onKeyDown={(e) => e.key === 'Enter' && onSave(item.id)}
                  className="form-control form-control-sm mx-1"
                  style={{ width: "50px" }}
                />
                <input
                    type="text"
                    value={localPrix[item.id] ?? (item.prix / 100).toFixed(2).replace('.', ',')}
                    onChange={(e) => setLocalPrix(prev => ({ ...prev, [item.id]: e.target.value }))}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') handleSavePrix(item.id);
                    }}
                />

                <span className="ms-2">{(item.prixt / 100).toFixed(2)} €</span>
              </div>
            </div>
          </li>
        ))}
      </ul>
      <h5 className="mt-3">Total: {(total / 100).toFixed(2)} €</h5>
    </>
  );
}

export default TicketVente;
