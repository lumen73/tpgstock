/*
 * FICHIER : css/app.js
 * RÔLE    : interactions JavaScript communes aux pages connectées.
 *
 * Le premier bloc ouvre et ferme le menu sur mobile.
 * Le second bloc permet de fermer les messages et les masque automatiquement
 * après quelques secondes.
 */

// Ouvre ou ferme le menu latéral lorsque l'utilisateur clique sur le bouton mobile.
document.querySelector('.menu-toggle')?.addEventListener('click', () => {
    document.querySelector('.sidebar')?.classList.toggle('open');
});

// Parcourt tous les messages flash affichés par PHP.
document.querySelectorAll('.toast').forEach((toast) => {
    // Supprime le message de la page.
    const close = () => toast.remove();

    // Le bouton « fermer » permet une suppression immédiate.
    toast.querySelector('button')?.addEventListener('click', close);

    // Suppression automatique après 4,2 secondes.
    setTimeout(close, 4200);
});
