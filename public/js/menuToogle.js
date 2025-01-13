document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('change', function () {
            const menu = document.getElementById('menu-mobile');
            if (menu) {
                if (this.checked) {
                    menu.classList.remove('hidden');
                } else {
                    menu.classList.add('hidden');
                }
            }
        });
    }

    // Gestion des menus déroulants
    const menuButtons = document.querySelectorAll('.relative button');
    menuButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Fermer tous les autres menus
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            allDropdowns.forEach(dropdown => {
                if (dropdown !== this.nextElementSibling) {
                    dropdown.classList.add('hidden');
                }
            });

            // Afficher/masquer le menu actuel
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    });
});