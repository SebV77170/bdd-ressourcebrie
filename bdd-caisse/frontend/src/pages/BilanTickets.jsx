import { io } from 'socket.io-client';
import React, { useEffect, useState } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import './BilanTickets.css';
import TicketDetail from '../components/TicketDetail';
import CorrectionModal from '../components/CorrectionModal';
import { Button } from 'react-bootstrap';

const socket = io('http://localhost:3001');

const BilanTickets = () => {
  const [tickets, setTickets] = useState([]);
  const [filtreDate, setFiltreDate] = useState(new Date());
  const [datesDisponibles, setDatesDisponibles] = useState([]);
  const [details, setDetails] = useState({});
  const [ticketActif, setTicketActif] = useState(null);
  const [showCorrection, setShowCorrection] = useState(false);
  const [ticketPourCorrection, setTicketPourCorrection] = useState(null);

  useEffect(() => {
    const fetchBilan = () => {
    fetch('http://localhost:3001/api/bilan')
      .then(res => res.json())
      .then(data => {
        setTickets(data);
        const dates = Array.from(
          new Set(data.map(ticket => new Date(ticket.date_achat_dt).toDateString()))
        ).map(d => new Date(d));
        setDatesDisponibles(dates);
      })
      .catch(err => console.error('Erreur chargement tickets :', err));
    }
    fetchBilan();

      socket.on('ticketsmisajour', () => {
        fetchBilan();
      });
    
      return () => socket.off('ticketsmisajour');

  }, []);

  const aReduction = (ticket) => {
    return ticket.reducbene || ticket.reduclient || ticket.reducgrospanierclient || ticket.reducgrospanierbene;
  };

  const isSameDay = (d1, d2) =>
    d1.getFullYear() === d2.getFullYear() &&
    d1.getMonth() === d2.getMonth() &&
    d1.getDate() === d2.getDate();

  const ticketsFiltres = tickets.filter(ticket =>
    isSameDay(new Date(ticket.date_achat_dt), filtreDate)
  );

  const chargerObjets = (id_ticket) => {
    if (details[id_ticket]) {
      setTicketActif(ticketActif === id_ticket ? null : id_ticket);
      return;
    }

    fetch(`http://localhost:3001/api/bilan/${id_ticket}/details`)
      .then(res => res.json())
      .then(data => {
        setDetails(prev => ({ ...prev, [id_ticket]: data }));
        setTicketActif(id_ticket);
      })
      .catch(err => console.error('Erreur chargement détails ticket:', err));
  };

  return (
    <div className="container mt-4">
      <h2>Bilan des tickets de caisse</h2>

      <div className="my-3">
        <label className="form-label">Filtrer par date :</label>
        <DatePicker
          selected={filtreDate}
          onChange={(date) => setFiltreDate(date)}
          highlightDates={[{ 'react-datepicker__day--highlighted-custom': datesDisponibles }]}
          inline
        />
      </div>

      {ticketsFiltres.length === 0 ? (
        <div className="alert alert-info mt-4">Aucun ticket pour la date sélectionnée.</div>
      ) : (
        <table className="table table-striped mt-3">
          <thead>
            <tr>
              <th>#</th>
              <th>Vendeur</th>
              <th>Date</th>
              <th>Mode Paiement</th>
              <th>Total</th>
              <th>Réduction</th>
              <th>Type</th>
            </tr>
          </thead>
          <tbody>
            {ticketsFiltres.map((ticket) => (
              <React.Fragment key={ticket.id_ticket}>
                <tr
                  onClick={() => chargerObjets(ticket.id_ticket)}
                  style={{ cursor: 'pointer' }}
                  className={ticketActif === ticket.id_ticket ? 'table-active' : ''}
                >
                  <td>{ticket.id_ticket}</td>
                  <td>{ticket.nom_vendeur || '—'}</td>
                  <td>{new Date(ticket.date_achat_dt).toLocaleString()}</td>
                  <td>{ticket.moyen_paiement || '—'}</td>
                  <td>
                    {typeof ticket.prix_total === 'number'
                      ? `${(ticket.prix_total / 100).toFixed(2)} €`
                      : '—'}
                  </td>


                  <td>{aReduction(ticket) ? '✅' : '—'}</td>
                  <td>
                    {ticket.flag_correction ? (
                      <span className="badge bg-danger">Annulation</span>
                    ) : ticket.correction_de ? (
                      <span className="badge bg-warning text-dark">Correctif</span>
                    ) : ticket.corrige_le_ticket ? (
                      <span className="badge bg-info text-dark">Correction</span>
                    ) : (
                      <span className="text-muted">—</span>
                    )}
                  </td>

                </tr>

                {ticketActif === ticket.id_ticket && details[ticket.id_ticket] && (
                  <tr>
                    <td colSpan="7">
                      <TicketDetail id_ticket={ticket.id_ticket} />
                      {!ticket.flag_correction && !ticket.ticket_corrige && (
                        <Button
                          variant="outline-warning"
                          size="sm"
                          className="mt-2"
                          onClick={() => {
                            setTicketPourCorrection(details[ticket.id_ticket]);
                            setShowCorrection(true);
                          }}
                        >
                          Corriger
                        </Button>
                      )}

                    </td>
                  </tr>
                )}
              </React.Fragment>
            ))}
          </tbody>
        </table>
      )}

      {ticketPourCorrection && (
        <CorrectionModal
          show={showCorrection}
          onHide={() => setShowCorrection(false)}
          ticketOriginal={ticketPourCorrection}
          onSuccess={() => {
            setDetails({});
            setTicketActif(null);
          }}
        />
      )}
    </div>
  );
};

export default BilanTickets;
