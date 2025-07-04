import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast, ToastContainer } from 'react-toastify';
import CompteEspeces from '../components/compteEspeces';
import AffichageEcarts from '../components/AffichageEcarts';

function FermetureCaisse() {
  const [fondInitial, setFondInitial] = useState(null);
  const [montantReel, setMontantReel] = useState('');
  const [attendu, setAttendu] = useState(null);
  const [montantReelCarte, setMontantReelCarte] = useState('');
  const [montantReelCheque, setMontantReelCheque] = useState('');
  const [montantReelVirement, setMontantReelVirement] = useState('');
  const [commentaire, setCommentaire] = useState('');
  const [responsablePseudo, setResponsablePseudo] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!montantReel || !responsablePseudo || !motDePasse) {
      toast.error('Tous les champs obligatoires doivent être remplis');
      return;
    }

    try {
      const res = await fetch('http://localhost:3001/api/caisse/fermeture', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          montant_reel: parseFloat(montantReel)*100,
          montant_reel_carte: parseFloat(montantReelCarte)*100,
          montant_reel_cheque: parseFloat(montantReelCheque)*100,
          montant_reel_virement: parseFloat(montantReelVirement)*100,
          commentaire,
          responsable_pseudo: responsablePseudo,
          mot_de_passe: motDePasse
        })
      });

      const data = await res.json();

      if (data.success) {
        toast.success('✅ Caisse fermée avec succès');
        setTimeout(() => {
          navigate('/');
        }, 1000);
      } else {
        toast.error(data.error || 'Erreur lors de la fermeture');
      }
    } catch (err) {
      console.error(err);
      toast.error('Erreur de communication avec le serveur');
    }
  };

 useEffect(() => {
    fetch('http://localhost:3001/api/caisse/fermeture/fond_initial')
      .then(res => res.json())
      .then(data => {
        if (data.fond_initial !== undefined) {
          setFondInitial(data.fond_initial/100);
        } else {
          console.error('Fond initial introuvable');
        }
      })
      .catch(err => {
        console.error('Erreur de récupération du fond initial :', err);
      });
  }, []);


   useEffect(() => {
    // Récupération des montants attendus depuis la table "bilan"
    fetch('http://localhost:3001/api/bilan/jour')
      .then(res => res.json())
      .then(data => {
        setAttendu({
          espece: data.prix_total_espece ?? 0,
          carte: data.prix_total_carte ?? 0,
          cheque: data.prix_total_cheque ?? 0,
          virement: data.prix_total_virement ?? 0
        });
      })
      .catch((err) => {
        console.error('Erreur lors du chargement du bilan :', err);
        setAttendu({
          espece: 0,
          carte: 0,
          cheque: 0,
          virement: 0
        });
      });
  }, []);

  return (
    <div style={{ padding: 20, maxWidth: 500, margin: 'auto' }}>
      <h2>Fermeture de caisse</h2>
       <div>
          <label>Fond Initial déclaré (€) :</label><br />
          <div> 
           {fondInitial}
          </div>
        </div>
      <form onSubmit={handleSubmit}>
        {/* ✅ Intégration du tableau des espèces */}
        <CompteEspeces onChangeTotal={(total) => setMontantReel(total)} />
        <div></div>
        <div>
          <label>Montant réel dans la caisse (€) :</label><br />
          <input
            type="number"
            value={montantReel}
            onChange={(e) => setMontantReel(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Montant réel des transactions Sumup (€) :</label><br />
          <input
            type="number"
            value={montantReelCarte}
            onChange={(e) => setMontantReelCarte(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Montant réel des chèques (€) :</label><br />
          <input
            type="number"
            value={montantReelCheque}
            onChange={(e) => setMontantReelCheque(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Montant réel des virement (€) :</label><br />
          <input
            type="number"
            value={montantReelVirement}
            onChange={(e) => setMontantReelVirement(e.target.value)}
            required
          />
        </div>
        <AffichageEcarts
            attendu={{
                espece: (attendu?.espece + fondInitial*100 ?? 0),
                carte: (attendu?.carte ?? 0),
                cheque: (attendu?.cheque ?? 0),
                virement: (attendu?.virement ?? 0),
            }}
            reel={{
                espece: montantReel*100,           // en centimes
                carte: montantReelCarte*100,
                cheque: montantReelCheque*100,
                virement: montantReelVirement*100
            }}
            fondInitial={fondInitial*100}
            />
        <div>
          <label>Commentaire (facultatif) :</label><br />
          <textarea
            value={commentaire}
            onChange={(e) => setCommentaire(e.target.value)}
          />
        </div>
        <div>
          <label>Pseudo du responsable :</label><br />
          <input
            type="text"
            value={responsablePseudo}
            onChange={(e) => setResponsablePseudo(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Mot de passe du responsable :</label><br />
          <input
            type="password"
            value={motDePasse}
            onChange={(e) => setMotDePasse(e.target.value)}
            required
          />
        </div>
        <button type="submit" style={{ marginTop: 10 }}>Fermer la caisse</button>
      </form>

      <ToastContainer position="top-center" autoClose={3000} />
    </div>
  );
}

export default FermetureCaisse;
