import os

def rechercher_fichiers_icloud(dossier_depart, fichier_resultat):
    # Liste pour stocker les chemins des fichiers liés à iCloud
    fichiers_icloud = []

    # Parcourir le dossier et les sous-dossiers
    for root, dirs, files in os.walk(dossier_depart):
        for file in files:
            chemin_complet = os.path.join(root, file)
            # Vérifier si le chemin ou le nom du fichier contient des mots-clés liés à iCloud
            if "icloud" in chemin_complet.lower() or "icloud drive" in chemin_complet.lower():
                fichiers_icloud.append(chemin_complet)

    # Écrire les résultats dans un fichier texte
    with open(fichier_resultat, 'w', encoding='utf-8') as f:
        f.write("Fichiers liés à iCloud Drive trouvés :\n\n")
        for chemin in fichiers_icloud:
            f.write(f"{chemin}\n")

    print(f"Résultat enregistré dans : {fichier_resultat}")

# Chemin de départ pour la recherche (par exemple, le disque C:)
dossier_depart = r"C:/"
# Chemin du fichier résultat
fichier_resultat = r"C:/Users/sebas/OneDrive/Documentos/sites/bdd-ressource-brie/icloud_files.txt"

# Appeler la fonction
rechercher_fichiers_icloud(dossier_depart, fichier_resultat)