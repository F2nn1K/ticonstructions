(function(){
	// Dark mode removido: limpar classe e remover botão, mantendo idempotência
	document.addEventListener('DOMContentLoaded', function(){
		document.body.classList.remove('dark-mode');
		var btn = document.getElementById('brs-darkmode-toggle');
		if(btn && btn.parentNode){ btn.parentNode.removeChild(btn); }
		try { localStorage.removeItem('brs_dark_mode'); } catch(e) {}
	});

	document.addEventListener('livewire:navigated', function(){
		document.body.classList.remove('dark-mode');
		var btn = document.getElementById('brs-darkmode-toggle');
		if(btn && btn.parentNode){ btn.parentNode.removeChild(btn); }
	});
})();
