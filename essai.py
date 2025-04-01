import os
from datetime import datetime

def parcourir_dossier_et_classer_par_date(dossier, fichier_resultat):
    # Liste pour stocker les informations des fichiers
    fichiers = []

    # Parcourir le dossier et les sous-dossiers
    for root, dirs, files in os.walk(dossier):
        for file in files:
            chemin_complet = os.path.join(root, file)
            # Récupérer la date de modification du fichier
            date_modification = os.path.getmtime(chemin_complet)
            fichiers.append((chemin_complet, date_modification))

    # Trier les fichiers par date de modification (ordre croissant)
    fichiers_tries = sorted(fichiers, key=lambda x: x[1])

    # Écrire les résultats dans un fichier texte
    with open(fichier_resultat, 'w', encoding='utf-8') as f:
        f.write("Fichiers classés par ordre chronologique (date de modification) :\n\n")
        for chemin, timestamp in fichiers_tries:
            date_humaine = datetime.fromtimestamp(timestamp).strftime('%Y-%m-%d %H:%M:%S')
            f.write(f"{date_humaine} - {chemin}\n")

    print(f"Résultat enregistré dans : {fichier_resultat}")

# Chemin du dossier à parcourir
dossier_a_parcourir = r"C:/Users/sebas/iCloudDrive/Documents"
# Chemin du fichier résultat
fichier_resultat = os.path.join(dossier_a_parcourir, "resultat.txt")

# Appeler la fonction
parcourir_dossier_et_classer_par_date(dossier_a_parcourir, fichier_resultat)