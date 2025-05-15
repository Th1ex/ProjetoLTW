// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
  // Exemplo: envio de formulário via fetch
  const ajaxForms = document.querySelectorAll('form[data-ajax]');
  ajaxForms.forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const url = form.action;
      const formData = new FormData(form);
      try {
        const res = await fetch(url, {
          method: form.method,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });
        const data = await res.json();
        // TODO: tratar resposta JSON (data.success, data.message, data.html…)
        console.log(data);
      } catch (err) {
        console.error('Erro AJAX:', err);
      }
    });
  });

  // Outros utilitários poderão ser adicionados aqui…
});
