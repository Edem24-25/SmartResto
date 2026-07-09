// SmartResto — Client JS
document.addEventListener('DOMContentLoaded', () => {
  // Auto-refresh KDS + plan de salle
  const auto = document.querySelector('[data-autorefresh]');
  if (auto) {
    const secs = parseInt(auto.dataset.autorefresh, 10) || 10;
    setTimeout(() => location.reload(), secs * 1000);
  }
  // Ticket timers KDS
  document.querySelectorAll('.ticket[data-sent]').forEach(t => {
    const sent = new Date(t.dataset.sent);
    const timer = t.querySelector('.timer');
    const update = () => {
      const diff = Math.floor((Date.now() - sent.getTime()) / 1000);
      const m = Math.floor(diff / 60), s = diff % 60;
      timer.textContent = `⏱ ${m}m ${s.toString().padStart(2,'0')}s`;
      if (diff > 15 * 60) t.classList.add('old');
    };
    update(); setInterval(update, 1000);
  });
  // Confirmation destructive
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });
});
