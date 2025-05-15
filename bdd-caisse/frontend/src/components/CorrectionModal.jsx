import React, { useState } from 'react';
import { Modal, Button, Form } from 'react-bootstrap';

function CorrectionModal({ show, onHide, ticketOriginal, onSuccess }) {
  const [corrections, setCorrections] = useState(
    (ticketOriginal.objets || []).map(obj => ({ ...obj }))
  );
  
  const [motif, setMotif] = useState('');
  const [loading, setLoading] = useState(false);
  const [moyenPaiement, setMoyenPaiement] = useState(ticketOriginal.ticket.moyen_paiement || '');
  const [reductionType, setReductionType] = useState('');
  const [paiements, setPaiements] = useState({
    "espèces": 0,
    "carte": 0,
    "chèque": 0,
    "virement": 0
  });

  const handleChange = (index, field, value) => {
    const updated = [...corrections];
    if (field === 'nbr') {
      updated[index][field] = parseInt(value);
    } else if (field === 'prix') {
      updated[index][field] = Math.round(parseFloat(value.replace(',', '.')) * 100); // euros -> centimes
    } else {
      updated[index][field] = value;
    }
    setCorrections(updated);
  };
  

  const calculerTotalCorrige = () => {
    let total = corrections.reduce((sum, art) => sum + art.prix * art.nbr, 0);
    if (reductionType === 'trueClient') total -= 500;
    else if (reductionType === 'trueBene') total -= 1000;
    else if (reductionType === 'trueGrosPanierClient') total = Math.round(total * 0.9);
    else if (reductionType === 'trueGrosPanierBene') total = Math.round(total * 0.8);
    return total < 0 ? 0 : total;
  };

  const envoyerCorrection = async () => {
    if (!motif.trim()) return alert('Merci de préciser un motif.');
    if (!moyenPaiement) return alert('Merci de choisir un mode de paiement.');

    const totalCorrige = calculerTotalCorrige();

    if (moyenPaiement === 'mixte') {
      const totalMixte = Object.values(paiements).reduce((sum, val) => sum + (parseInt(val) || 0), 0);
      if (totalMixte !== totalCorrige) {
        return alert(
          `Le total mixte (${(totalMixte / 100).toFixed(2)} €) ne correspond pas au total corrigé du ticket (${(totalCorrige / 100).toFixed(2)} €).`
        );
      }
    }

    const body = {
      id_ticket_original: ticketOriginal.ticket.id_ticket,
      articles_origine: ticketOriginal.objets, // <- version intacte
      articles_correction: corrections,
      motif,
      moyen_paiement: moyenPaiement,
      reductionType
    };

    if (moyenPaiement === 'mixte') {
      body.paiements = Object.entries(paiements).map(([moyen, montant]) => ({
        moyen,
        montant: parseInt(montant) || 0
      }));
    }

    setLoading(true);
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

  const estUneReduction = (article) => {
    return article.nom.toLowerCase().includes('réduction');
  };
  

  return (
    <Modal show={show} onHide={onHide} size="lg" backdrop="static">
      <Modal.Header closeButton>
        <Modal.Title>Corriger le ticket #{ticketOriginal.ticket.id_ticket}</Modal.Title>
      </Modal.Header>
      <Modal.Body>
      {corrections.map((art, i) => {
  const isReduction = estUneReduction(art);
  return (
    <div className="d-flex gap-2 mb-2" key={i}>
      <Form.Control value={art.nom} disabled />
      <Form.Control
        type="number"
        value={art.nbr}
        onChange={(e) => handleChange(i, 'nbr', e.target.value)}
        disabled={isReduction}
      />
      <Form.Control
        type="number"
        step="0.01"
        value={(art.prix / 100).toFixed(2)}
        onChange={(e) => handleChange(i, 'prix', e.target.value)}
        disabled={isReduction}
      />
      <Form.Control value={art.categorie} disabled />
    </div>
  );
})}


        <Form.Group className="mt-3">
          <Form.Label>Mode de paiement</Form.Label>
          <Form.Select value={moyenPaiement} onChange={(e) => setMoyenPaiement(e.target.value)}>
            <option value="">-- Choisir --</option>
            <option value="espèces">Espèces</option>
            <option value="carte">Carte</option>
            <option value="chèque">Chèque</option>
            <option value="virement">Virement</option>
            <option value="mixte">Mixte</option>
          </Form.Select>
        </Form.Group>

        {moyenPaiement === 'mixte' && (
          <div className="mt-3">
            <Form.Label>Détail des montants par mode :</Form.Label>
            <div className="d-flex flex-wrap gap-2">
              {['espèces', 'carte', 'chèque', 'virement'].map((moyen) => (
                <Form.Control
                  type="number"
                  min={0}
                  step="0.01"
                  value={(paiements[moyen] / 100).toFixed(2)}
                  onChange={(e) =>
                    setPaiements({
                      ...paiements,
                      [moyen]: Math.round(parseFloat(e.target.value.replace(',', '.')) * 100) || 0
                    })
                  }
                />
              
              ))}
            </div>
          </div>
        )}

        <Form.Group className="mt-3">
          <Form.Label>Type de réduction</Form.Label>
          <Form.Select value={reductionType} onChange={(e) => setReductionType(e.target.value)}>
            <option value="">Aucune</option>
            <option value="trueClient">Fidélité client (-5€)</option>
            <option value="trueBene">Fidélité bénévole (-10€)</option>
            <option value="trueGrosPanierClient">Gros panier client (-10%)</option>
            <option value="trueGrosPanierBene">Gros panier bénévole (-20%)</option>
          </Form.Select>
        </Form.Group>

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
