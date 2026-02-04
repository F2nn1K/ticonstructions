/**
 * Lazy Load de Imagens - Adiciona loading="lazy" automaticamente
 * Arquivo: /js/lazy-load.js
 */
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona todas as imagens que ainda não têm loading="lazy"
    const images = document.querySelectorAll('img:not([loading])');
    
    images.forEach(function(img) {
        // Ignora logo do topo e primeira imagem de cada página
        const isLogo = img.classList.contains('brand-image') || 
                       img.alt.toLowerCase().includes('logo') && 
                       img.closest('.main-header');
        
        if (!isLogo) {
            img.setAttribute('loading', 'lazy');
        }
    });
});

