(() => {
  const storedTheme = localStorage.getItem('schoolhub-theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', storedTheme);

  document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('schoolhub-theme', next);
  });

  document.querySelector('[data-toggle-sidebar]')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
  });

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || 'Continue?')) {
        event.preventDefault();
      }
    });
  });

  const chart = document.getElementById('dashboardChart');
  if (chart && window.Chart) {
    new Chart(chart, {
      type: 'bar',
      data: {
        labels: ['Students', 'Pending registrations', 'Verified fees / 1000'],
        datasets: [{
          label: 'Current Snapshot',
          data: [
            Number(chart.dataset.students || 0),
            Number(chart.dataset.pending || 0),
            Math.round(Number(chart.dataset.fees || 0) / 1000)
          ],
          backgroundColor: ['#2563eb', '#16a34a', '#0891b2', '#f59e0b'],
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }
})();
