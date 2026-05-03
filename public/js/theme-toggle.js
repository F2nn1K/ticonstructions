/**
 * Theme Toggle - DESATIVADO v2.0
 * Modo escuro completamente removido.
 */
(function() {
    // Forçar modo claro sempre
    document.documentElement.removeAttribute('data-theme');
    localStorage.removeItem('sigo_theme');
    localStorage.removeItem('brs_dark_mode');

    function removeToggleBtn() {
        // Remover qualquer botão de dark mode que possa existir
        var ids = ['themeToggleBtn', 'brs-darkmode-toggle', 'darkModeToggle', 'dark-mode-toggle'];
        ids.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                var parent = el.closest('li') || el.parentNode;
                if (parent) parent.remove(); else el.remove();
            }
        });
        // Remover por classe
        document.querySelectorAll('.theme-toggle-btn, [id*="dark"], [id*="theme-toggle"]').forEach(function(el) {
            var parent = el.closest('li') || el.parentNode;
            if (parent) parent.remove(); else el.remove();
        });
    }

    document.addEventListener('DOMContentLoaded', removeToggleBtn);
    window.addEventListener('load', removeToggleBtn);

    // Fallback imediato
    if (document.readyState !== 'loading') {
        setTimeout(removeToggleBtn, 1);
        setTimeout(removeToggleBtn, 300);
    }
})();
