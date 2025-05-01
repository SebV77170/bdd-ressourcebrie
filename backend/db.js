const path = require('path');
const fs = require('fs');
const Database = require('better-sqlite3');

// 👉 Définir le chemin AVANT de l’utiliser
const dbPath = path.join(__dirname, '..', 'database', 'ressourcebrie-sqlite.db');

// 👉 Vérifier l'existence après sa déclaration
if (!fs.existsSync(dbPath)) {
  throw new Error(`Base de données SQLite introuvable à : ${dbPath}`);
}

const db = new Database(dbPath);
console.log('Connecté à SQLite :', dbPath);

module.exports = db;
