/**
 * Fix: Logo escondida quando menu lateral minimizado
 * Este script garante que a logo BRS seja escondida corretamente 
 * quando a sidebar está minimizada e apareça quando expandida
 */
(function() {
    'use strict';
    
    function updateLogoVisibility() {
        var brandImage = document.querySelector('.brand-link .brand-image');
        var brandText = document.querySelector('.brand-link .brand-text');
        var isCollapsed = document.body.classList.contains('sidebar-collapse');
        
        if (brandImage) {
            if (isCollapsed) {
                brandImage.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important; width: 0 !important; height: 0 !important;';
            } else {
                brandImage.style.cssText = 'display: inline-block; visibility: visible; opacity: 1; max-height: 33px;';
            }
        }
        
        if (brandText) {
            brandText.style.display = isCollapsed ? 'none' : '';
        }
    }
    
    // Executar na carga
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateLogoVisibility);
    } else {
        updateLogoVisibility();
    }
    
    // Observer para detectar mudanças na classe do body
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                updateLogoVisibility();
            }
        });
    });
    
    // Iniciar observer quando body estiver disponível
    function startObserver() {
        observer.observe(document.body, { attributes: true });
        updateLogoVisibility();
    }
    
    if (document.body) {
        startObserver();
    } else {
        document.addEventListener('DOMContentLoaded', startObserver);
    }
    
    // Mostrar logo ao passar o mouse na sidebar minimizada
    var sidebar = document.querySelector('.main-sidebar');
    if (sidebar) {
        sidebar.addEventListener('mouseenter', function() {
            if (document.body.classList.contains('sidebar-collapse')) {
                var brandImage = document.querySelector('.brand-link .brand-image');
                var brandText = document.querySelector('.brand-link .brand-text');
                if (brandImage) {
                    brandImage.style.cssText = 'display: inline-block; visibility: visible; opacity: 1; max-height: 33px;';
                }
                if (brandText) {
                    brandText.style.display = '';
                }
            }
        });
        
        sidebar.addEventListener('mouseleave', function() {
            updateLogoVisibility();
        });
    } else {
        // Sidebar não existe ainda, aguardar DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.querySelector('.main-sidebar');
            if (sidebar) {
                sidebar.addEventListener('mouseenter', function() {
                    if (document.body.classList.contains('sidebar-collapse')) {
                        var brandImage = document.querySelector('.brand-link .brand-image');
                        var brandText = document.querySelector('.brand-link .brand-text');
                        if (brandImage) {
                            brandImage.style.cssText = 'display: inline-block; visibility: visible; opacity: 1; max-height: 33px;';
                        }
                        if (brandText) {
                            brandText.style.display = '';
                        }
                    }
                });
                
                sidebar.addEventListener('mouseleave', function() {
                    updateLogoVisibility();
                });
            }
        });
    }
})();

