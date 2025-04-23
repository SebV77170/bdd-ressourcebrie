import React from 'react';

function CategorieSelector({ categories, active, onSelect }) {
  return (
    <div>
      <h5>Catégories</h5>
      <ul className="nav flex-column">
        {categories.map((cat, i) => (
          <li key={i} className="nav-item">
            <button
              className={`nav-link btn btn-link ${active === cat ? 'fw-bold text-primary' : ''}`}
              onClick={() => onSelect(cat)}
            >
              {cat}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}

export default CategorieSelector;