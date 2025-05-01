import React, { useState } from 'react';
import { Modal, Button, Form } from 'react-bootstrap';

function CorrectionModal({ show, onHide, ticketOriginal, onSuccess }) {
  const [corrections, setCorrections] = useState(ticketOriginal.objets || []);
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
      utilisateur: 'admin', // TODO: remplacer par user actif
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
        onHide();
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
    <Modal show={show} onHide={onHide} size="lg" backdrop="static">
      <Modal.Header closeButton>
        <Modal.Title>Corriger le ticket #{ticketOriginal.ticket.id_ticket}</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        {corrections.map((art, i) => (
          <div className="d-flex gap-2 mb-2" key={i}>
            <Form.Control value={art.nom} disabled />
            <Form.Control
              type="number"
              value={art.nbr}
              onChange={(e) => handleChange(i, 'nbr', e.target.value)}
            />
            <Form.Control
              type="number"
              value={art.prix}
              onChange={(e) => handleChange(i, 'prix', e.target.value)}
            />
            <Form.Control value={art.categorie} disabled />
          </div>
        ))}
        <Form.Group className="mt-3">
          <Form.Label>Motif de correction</Form.Label>
          <Form.Control
            as="textarea"
            rows={2}
            value={motif}
            onChange={(e) => setMotif(e.target.value)}
            placeholder="Exemple : erreur de quantité saisie par le bénévole"
          />
        </Form.Group>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={onHide}>
          Annuler
        </Button>
        <Button variant="warning" onClick={envoyerCorrection} disabled={loading}>
          {loading ? 'Correction en cours...' : 'Valider la correction'}
        </Button>
      </Modal.Footer>
    </Modal>
  );
}

export default CorrectionModal;
