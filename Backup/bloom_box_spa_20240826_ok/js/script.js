// js/script.js — Bloom Box Spa (rescate express, robusto)
// -------------------------------------------------------

let PARTICIPANT_ID = null;
let SUBMITTING = false; // evita doble submit

// --- Utilidades UI ---
function showModal(message) {
  const modal = document.getElementById('message-modal');
  const msgEl = document.getElementById('modal-message');
  if (msgEl) msgEl.innerHTML = message;
  if (modal) modal.style.display = 'flex';
  else alert(message);
}
function hideModal() {
  const modal = document.getElementById('message-modal');
  if (modal) modal.style.display = 'none';
}
document.getElementById('close-modal')?.addEventListener('click', hideModal);
window.addEventListener('click', (e) => {
  const modal = document.getElementById('message-modal');
  if (modal && e.target === modal) hideModal();
});
function isValidEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/i.test(email); }

// --- Celebración ---
function loadConfettiLibrary() {
  if (typeof window.BloomCelebration !== 'undefined') return Promise.resolve();
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = 'js/confetti.js';
    s.onload = resolve;
    s.onerror = reject;
    document.head.appendChild(s);
  });
}
async function launchCelebrationAnimation() {
  try {
    await loadConfettiLibrary();
    window.BloomCelebration?.launchFullCelebration?.({
      confetti:true, balloons:true, streamers:true, sound:true, duration:8000
    });
  } catch { /* sin lib, no pasa nada */ }
}

// --- REGISTRO: handler global llamado desde el onsubmit del <form> ---
async function handleRegisterSubmit(e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  if (SUBMITTING) return false; // evita dobles
  SUBMITTING = true;

  const form = document.getElementById('registration-form');
  if (!form) { SUBMITTING = false; return false; }

  // Validaciones amigables (con los inputs visibles)
  const nombre   = document.getElementById('name')?.value.trim()  || '';
  const email    = document.getElementById('email')?.value.trim() || '';
  const telefono = document.getElementById('phone')?.value.trim() || '';
  if (!nombre || !email || !telefono) { showModal('Todos los campos son obligatorios.'); SUBMITTING=false; return false; }
  if (!isValidEmail(email))           { showModal('Por favor, ingresa un email válido.'); SUBMITTING=false; return false; }
  if (telefono.replace(/\D+/g,'').length < 7) { showModal('Por favor, ingresa un número de teléfono válido.'); SUBMITTING=false; return false; }

  // FormData incluye el _csrf del <?= csrf_field() ?>
  const fd = new FormData(form);
  fd.set('nombre', nombre);
  fd.set('email',  email);
  fd.set('telefono', telefono);

  try {
    const res = await fetch('registro.php', { method:'POST', credentials:'same-origin', body: fd });
    const text = await res.text();
    let j=null; try { j = JSON.parse(text); } catch {}
    if (!res.ok || !j || !j.ok) {
      console.error('registro.php ->', res.status, text);
      showModal((j && j.msg) || 'Error de red en el registro.');
      SUBMITTING = false; return false;
    }

    PARTICIPANT_ID = j.participant_id || PARTICIPANT_ID;
    window.PARTICIPANT_ID = PARTICIPANT_ID;

    showModal(j.dup ? 'Ya estabas registrado. ¡Puedes jugar ahora mismo!' :
                      '¡Registro exitoso! Ahora puedes jugar.');

    const game = document.getElementById('game');
    if (game) {
      game.style.display = 'block';
      setTimeout(() => game.scrollIntoView({behavior:'smooth'}), 600);
    }
  } catch (err) {
    console.error(err);
    showModal('Error de red en el registro.');
  } finally {
    SUBMITTING = false;
  }
  return false; // clave: no navegar
}

// --- JUEGO: click en cajas ---
function disableAllBoxes(){ document.querySelectorAll('.box').forEach(b => b.style.pointerEvents='none'); }
function enableAllBoxes(){ document.querySelectorAll('.box').forEach(b => b.style.pointerEvents='auto'); }

async function handleBoxClick(e) {
  const el = e.currentTarget;
  const imagen_id = parseInt(el.getAttribute('data-id'), 10);
  if (!window.PARTICIPANT_ID) return showModal('Primero completa el registro.');
  if (!imagen_id) return showModal('Caja inválida.');

  el.classList.add('opened');
  disableAllBoxes();

  try {
    const fd = new FormData();
    fd.append('participante_id', window.PARTICIPANT_ID); // opcional, el backend usa la sesión
    fd.append('imagen_id', imagen_id);

    // 👇 AGREGA EL CSRF (juego.php lo valida con require_csrf())
    const m = document.querySelector('meta[name="csrf-token"]');
    if (m?.content) fd.append('csrf', m.content);

    const res = await fetch('juego.php', {
        method: 'POST',
        credentials: 'same-origin',   // importante para enviar la cookie de sesión
        body: fd
    });
    
    const text = await res.text();
    let j=null; try { j = JSON.parse(text); } catch {}

    if (!res.ok || !j || !j.ok) { console.error('juego.php ->', res.status, text); throw new Error((j && j.msg) || 'Error de red al jugar.'); }

    const premio = j.prize?.premio || 'Premio';
    const prizeNameEl = document.getElementById('prize-name');
    if (prizeNameEl) prizeNameEl.textContent = premio;

    const result = document.getElementById('result-container');
    if (result) result.style.display = 'block';

    await launchCelebrationAnimation();
    setTimeout(() => document.getElementById('result-container')?.scrollIntoView({behavior:'smooth'}), 500);
  } catch (err) {
    showModal(err.message || 'No fue posible jugar.');
    enableAllBoxes();
    el.classList.remove('opened');
  }
}

// --- Enganche de eventos de juego y failsafes ---
document.addEventListener('DOMContentLoaded', () => {
  // Si index.php puso un hidden con el id en sesión, úsalo
  const hid = document.getElementById('participant_id');
  if (hid?.value) {
    PARTICIPANT_ID = parseInt(hid.value, 10) || null;
    window.PARTICIPANT_ID = PARTICIPANT_ID;
  }

  // Clicks de las cajas
  document.querySelectorAll('.box').forEach(box => {
    if (box.dataset.bound === '1') return;
    box.dataset.bound = '1';
    box.addEventListener('click', handleBoxClick);
  });

  // Failsafe adicional: si por alguna razón alguien quita el onsubmit del HTML
  const form = document.getElementById('registration-form');
  if (form && !form.dataset.bound) {
    form.dataset.bound = '1';
    form.addEventListener('submit', handleRegisterSubmit, { capture:true });
  }

  console.log('Bloom Box Spa JS listo');
});

// Último salvavidas: captura global de submit (nunca navegamos)
document.addEventListener('submit', (e) => {
  const f = e.target;
  if (f && f.id === 'registration-form') {
    e.preventDefault();
    e.stopPropagation();
    handleRegisterSubmit(e);
    return false;
  }
}, true);

// Expón el handler para el onsubmit inline
window.handleRegisterSubmit = handleRegisterSubmit;
