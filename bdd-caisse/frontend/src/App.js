import React from 'react';
import { BrowserRouter as Router, Routes, Route, Link, useNavigate } from 'react-router-dom';
import Caisse from './pages/Caisse';
import BilanTickets from './pages/BilanTickets';
import LoginPage from './pages/LoginPage';
import RequireSession from './components/RequireSession';

function App() {
  const vendeur = JSON.parse(localStorage.getItem('vendeur') || '{}');

  const handleLogout = () => {
    localStorage.removeItem('vendeur');
    fetch('http://localhost:3001/api/session', { method: 'DELETE' });
    window.location.href = '/login';
  };

  return (
    <Router>
      <nav className="navbar navbar-expand navbar-dark bg-dark px-3">
        <Link className="navbar-brand" to="/">Caisse</Link>
        <Link className="nav-link text-white" to="/bilan">Bilan tickets</Link>
        <div className="ms-auto text-white">
          {vendeur.nom && (
            <span className="me-3">
              Bienvenue, <strong>{vendeur.nom}</strong>
            </span>
          )}
          <button className="btn btn-sm btn-outline-light" onClick={handleLogout}>
            Déconnexion
          </button>
        </div>
      </nav>

      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/" element={<RequireSession><Caisse /></RequireSession>} />
        <Route path="/bilan" element={<RequireSession><BilanTickets /></RequireSession>} />
      </Routes>
    </Router>
  );
}

export default App;
