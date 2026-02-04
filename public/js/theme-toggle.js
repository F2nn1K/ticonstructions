/**
 * SIGO - Theme Toggle (Dark/Light Mode)
 * Controla a alternância entre modo claro e escuro
 * Versão: 1.0
 */

(function() {
    'use strict';

    // Chave para armazenar preferência no localStorage
    const THEME_KEY = 'sigo_theme';
    
    // Detectar tema salvo ou usar preferência do sistema
    function getPreferredTheme() {
        const savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme) {
            return savedTheme;
        }
        // Se não há tema salvo, usar preferência do sistema
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    // Aplicar tema ao documento
    function applyTheme(theme) {
        const html = document.documentElement;
        
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            updateThemeColor('#1e293b');
        } else {
            html.removeAttribute('data-theme');
            updateThemeColor('#007bff');
        }
        
        // Atualizar ícone do botão se existir
        updateToggleButton(theme);
    }

    // Atualizar meta tag theme-color para PWA
    function updateThemeColor(color) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', color);
        }
    }

    // Atualizar ícone e texto do botão de toggle
    function updateToggleButton(theme) {
        const btn = document.getElementById('themeToggleBtn');
        if (!btn) return;

        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');

        if (theme === 'dark') {
            if (icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
            if (text) text.textContent = 'Claro';
            btn.title = 'Mudar para modo claro';
        } else {
            if (icon) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
            if (text) text.textContent = 'Escuro';
            btn.title = 'Mudar para modo escuro';
        }
    }

    // Alternar entre temas
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        localStorage.setItem(THEME_KEY, newTheme);
        applyTheme(newTheme);
    }

    // Criar botão de toggle e inserir no navbar
    function createToggleButton() {
        // Verificar se o botão já existe
        if (document.getElementById('themeToggleBtn')) return;

        // Encontrar a navbar direita
        const navbarRight = document.querySelector('.navbar-nav.ml-auto') || 
                           document.querySelector('.main-header .navbar-nav:last-child') ||
                           document.querySelector('.main-header nav .navbar-nav');

        if (!navbarRight) return;

        // Determinar tema atual
        const currentTheme = getPreferredTheme();
        const iconClass = currentTheme === 'dark' ? 'fa-sun' : 'fa-moon';
        const text = currentTheme === 'dark' ? 'Claro' : 'Escuro';
        const title = currentTheme === 'dark' ? 'Mudar para modo claro' : 'Mudar para modo escuro';

        // Criar o item de lista do navbar
        const navItem = document.createElement('li');
        navItem.className = 'nav-item d-flex align-items-center mr-2';

        // Criar o botão
        const button = document.createElement('button');
        button.id = 'themeToggleBtn';
        button.className = 'theme-toggle-btn';
        button.title = title;
        button.type = 'button';
        button.innerHTML = '<i class="fas ' + iconClass + '"></i><span>' + text + '</span>';
        button.addEventListener('click', toggleTheme);

        // Inserir no navbar
        navItem.appendChild(button);
        
        // Inserir como primeiro item da navbar direita
        navbarRight.insertBefore(navItem, navbarRight.firstChild);
    }

    // Aplicar tema imediatamente (antes do DOM carregar para evitar flash)
    applyTheme(getPreferredTheme());

    // Quando o DOM estiver pronto, criar o botão
    document.addEventListener('DOMContentLoaded', function() {
        createToggleButton();
    });

    // Fallback caso DOMContentLoaded já tenha disparado
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(createToggleButton, 1);
    }

    // Escutar mudanças na preferência do sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        // Só aplicar se o usuário não tiver escolhido manualmente
        if (!localStorage.getItem(THEME_KEY)) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    // Expor função globalmente para uso manual se necessário
    window.sigoToggleTheme = toggleTheme;
})();
