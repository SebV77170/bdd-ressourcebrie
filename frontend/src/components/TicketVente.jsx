import React from 'react';

function TicketVente({ ticket, modifs, onChange, onDelete, onSave }) {
  const total = ticket.reduce((sum, item) => sum + (item.prixt || 0), 0);

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
                  type="number"
                  value={(item.prix / 100).toFixed(2)}
                  onChange={(e) => onChange(item.id, 'prix', Math.round(parseFloat(e.target.value) * 100))}
                  onKeyDown={(e) => e.key === 'Enter' && onSave(item.id)}
                  className="form-control form-control-sm mx-1"
                  style={{ width: "70px" }}
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
