document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault();}));
const menuButton=document.querySelector('[data-menu-toggle]');
function closeMenu(){document.body.classList.remove('menu-open');menuButton?.setAttribute('aria-expanded','false');}
menuButton?.addEventListener('click',()=>{const open=document.body.classList.toggle('menu-open');menuButton.setAttribute('aria-expanded',String(open));});
document.querySelector('[data-close-menu]')?.addEventListener('click',closeMenu);
document.addEventListener('keydown',event=>{if(event.key==='Escape')closeMenu();});
document.querySelectorAll('[data-job-view]').forEach(button=>button.addEventListener('click',()=>{const grid=document.querySelector('[data-job-results]');if(!grid)return;grid.classList.toggle('list-mode',button.dataset.jobView==='list');document.querySelectorAll('[data-job-view]').forEach(item=>{item.classList.toggle('active',item===button);item.setAttribute('aria-pressed',String(item===button));});}));
