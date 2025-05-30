

// Helper para escapar HTML
function escapeHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
  // AJAX Chat
  const form = document.querySelector('.message-form');
  const messagesContainer = document.querySelector('.messages-container');
  if (form && messagesContainer) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(form);
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'X-Requested-With': 'XMLHttpRequest'},
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          const div = document.createElement('div');
          div.classList.add('message', 'sent');
          div.innerHTML = `
            <p><strong>Você:</strong> ${escapeHtml(data.content)}</p>
            <span class="timestamp">${escapeHtml(data.sent_at)}</span>
          `;
          messagesContainer.appendChild(div);
          form.reset();
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } else {
          alert(data.error || 'Erro ao enviar mensagem.');
        }
      } catch (err) {
        console.error('Fetch error:', err);
        alert('Erro na requisição de mensagem.');
      }
    });
  }

  // Slider para galeria
  document.querySelectorAll('[data-slider]').forEach(slider => {
    const track = slider.querySelector('.slider-track');
    const slides = Array.from(track.children);
    let idx = 0;

    const update = () => {
      const offset = idx * track.clientWidth;
      track.style.transform = `translateX(${-offset}px)`;
    };

    slider.querySelector('[data-prev]').addEventListener('click', () => {
      idx = (idx - 1 + slides.length) % slides.length;
      update();
    });
    slider.querySelector('[data-next]').addEventListener('click', () => {
      idx = (idx + 1) % slides.length;
      update();
    });
    window.addEventListener('resize', update);

    track.style.display = 'flex';
    track.style.transition = 'transform 0.3s ease';
    slides.forEach(slide => {
      slide.style.minWidth = '100%';
      slide.style.boxSizing = 'border-box';
    });
    update();
  });
});
