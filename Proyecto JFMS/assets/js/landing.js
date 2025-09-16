
const nav = document.getElementById('nav'); const navToggle = document.getElementById('navToggle');
if (nav && navToggle) navToggle.addEventListener('click', ()=> nav.classList.toggle('open'));
const io = new IntersectionObserver((entries)=> entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('visible'); }), {threshold:.15});
document.querySelectorAll('.reveal,.reveal-stagger').forEach(el=> io.observe(el));
