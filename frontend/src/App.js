import React from "react";
import BoutonsCaisse from "./components/BoutonsCaisse";
import 'bootstrap/dist/css/bootstrap.min.css';

function App() {
  return (
    <div className="container mt-4">
      <h1 className="mb-4">Interface de Caisse</h1>
      <BoutonsCaisse />
    </div>
  );
}

export default App;
