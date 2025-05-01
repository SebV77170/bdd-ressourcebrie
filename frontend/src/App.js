import React from 'react';
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom';
import Caisse from './components/Caisse';
import BilanTickets from './pages/BilanTickets';

function App() {
  return (
    <Router>
      <nav className="navbar navbar-expand navbar-dark bg-dark px-3">
        <Link className="navbar-brand" to="/">Caisse</Link>
        <Link className="nav-link text-white" to="/bilan">Bilan tickets</Link>
      </nav>

      <Routes>
        <Route path="/" element={<Caisse />} />
        <Route path="/bilan" element={<BilanTickets />} />
      </Routes>
    </Router>
  );
}

export default App;
