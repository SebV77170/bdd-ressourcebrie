const path = require('path');
const fs = require('fs');
const Database = require('better-sqlite3');

let db;

// Si on est en test, on utilise une base en mémoire (isolée)
if (process.env.NODE_ENV === 'test') {
  db = new Database(':memory:');
  console.log('Connecté à SQLite en mémoire (tests isolés)');
} else {
  const dbPath = path.join(__dirname, '..', 'database', 'ressourcebrie-sqlite.db');

  if (!fs.existsSync(dbPath)) {
    throw new Error(`Base de données SQLite introuvable à : ${dbPath}`);
  }

  db = new Database(dbPath);
  console.log('Connecté à SQLite :', dbPath);
}

module.exports = db;
