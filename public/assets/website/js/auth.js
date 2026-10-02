(() => {
  'use strict';
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const wrap = button.closest('.auth-input');
      const input = wrap?.querySelector('[data-password-input]');
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.innerHTML = show ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
    });
  });
  const targets = [...document.querySelectorAll('[data-auth-reveal]')];
  targets.forEach((el) => el.style.setProperty('--auth-delay', `${Number(el.dataset.authDelay || 0)}ms`));
  if (reduced || !('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-visible'));
    return;
  }
  const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
    if (!entry.isIntersecting) return;
    entry.target.classList.add('is-visible');
    io.unobserve(entry.target);
  }), { threshold: .12 });
  targets.forEach((el) => io.observe(el));
})();
