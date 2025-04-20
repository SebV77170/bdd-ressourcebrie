import React, { useEffect, useState } from "react";

function BoutonsCaisse() {
  const [boutons, setBoutons] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("http://localhost:3001/api/produits/organises")
      .then((res) => res.json())
      .then((data) => {
        setBoutons(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Erreur lors de la récupération des boutons :", err);
        setLoading(false);
      });
  }, []);

  if (loading) return <p>Chargement...</p>;

  return (
    <div className="col-7">
      {/* Menu de navigation des catégories */}
      <nav
        id="navbar-category"
        className="navbar navbar-light bg-light px-3 d-none d-md-block"
      >
        <ul className="nav nav-pills">
          {Object.keys(boutons).map((cat, i) => (
            <li className="nav-item" key={i}>
              <a className="nav-link" href={`#scrollspyHeading${i}`}>
                {cat}
              </a>
            </li>
          ))}
        </ul>
      </nav>

      {/* Liste des boutons organisés par catégories */}
      <div
        style={{ height: "450px", overflowY: "scroll" }}
        data-bs-spy="scroll"
        data-bs-target="#navbar-category"
        className="scrollspy-example"
        tabIndex="0"
      >
        {Object.entries(boutons).map(([cat, sousCats], i) => (
          <div key={i}>
            <h3 id={`scrollspyHeading${i}`}>{cat}</h3>
            {Object.entries(sousCats).map(([sousCat, produits], j) => (
              <div key={j} className="mb-3">
                {sousCat !== cat && <p className="sous-cat">{sousCat}</p>}
                <div className="row row-cols-5">
                  {produits.map((produit) => {
                    const prix = (produit.prix / 100).toFixed(2);
                    return (
                      <button
                        key={produit.id_bouton}
                        className={`col btn btn-${produit.color || "secondary"} border-dark m-1 rounded-3`}
                        onClick={() => console.log("Ajout au ticket :", produit)}
                      >
                        {prix}€ - {produit.nom}
                      </button>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        ))}
      </div>
    </div>
  );
}

export default BoutonsCaisse;
