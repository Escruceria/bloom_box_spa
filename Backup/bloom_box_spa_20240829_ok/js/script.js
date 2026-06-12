// js/script.js — Bloom Box Spa
// -------------------------------------------------------
// Registro (POST a registro.php) + Juego (POST a juego.php)
// Sesión/CSRF: usa cookie de sesión y meta[name=csrf-token] / input[name=csrf]
// -------------------------------------------------------

'use strict';

let PARTICIPANT_ID = null;
let SUBMITTING = false; // evita doble submit

// ========================
// Utilidades UI
// ========================
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
(function bindModal() {
  document.getElementById('close-modal')?.addEventListener('click', hideModal);
  window.addEventListener('click', (e) => {
    const modal = document.getElementById('message-modal');
    if (modal && e.target === modal) hideModal();
  });
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hideModal();
  });
})();

function isValidEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/i.test(email); }
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.content || '';
}

// Lazy-load de la librería de celebración
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
      confetti: true, balloons: true, streamers: true, sound: true, duration: 8000
    });
  } catch { /* sin lib, no pasa nada */ }
}

// ========================
// REGISTRO
// ========================
async function handleRegisterSubmit(e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  if (SUBMITTING) return false;
  SUBMITTING = true;

  const form = document.getElementById('registration-form');
  if (!form) { SUBMITTING = false; return false; }

  // Validaciones amigables
  const nombre   = document.getElementById('name')?.value.trim()  || '';
  const email    = document.getElementById('email')?.value.trim() || '';
  const telefono = document.getElementById('phone')?.value.trim() || '';

  if (!nombre || !email || !telefono) {
    showModal('Todos los campos son obligatorios.');
    SUBMITTING = false; return false;
  }
  if (!isValidEmail(email)) {
    showModal('Por favor, ingresa un email válido.');
    SUBMITTING = false; return false;
  }
  if (telefono.replace(/\D+/g,'').length < 7) {
    showModal('Por favor, ingresa un número de teléfono válido.');
    SUBMITTING = false; return false;
  }

  // FormData del formulario (ya incluye input[name="csrf"] si existe)
  const fd = new FormData(form);
  // Normalizamos nombres esperados en el backend
  fd.set('nombre',   nombre);
  fd.set('email',    email);
  fd.set('telefono', telefono);

  // Si no hubiera input hidden CSRF por alguna razón, añade el meta token
  if (!fd.get('csrf')) {
    const tk = getCsrfToken();
    if (tk) fd.set('csrf', tk);
  }

  try {
    const res  = await fetch('registro.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
    });
    const text = await res.text();
    let j = null; try { j = JSON.parse(text); } catch {}

    if (!res.ok || !j || j.ok !== true) {
      console.error('registro.php ->', res.status, text);
      showModal((j && j.msg) || 'Error de red en el registro.');
      SUBMITTING = false;
      return false;
    }

    // Éxito
    PARTICIPANT_ID = j.participant_id || PARTICIPANT_ID;
    window.PARTICIPANT_ID = PARTICIPANT_ID;

    showModal(j.dup
      ? 'Ya estabas registrado. ¡Puedes jugar ahora mismo!'
      : '¡Registro exitoso! Ahora puedes jugar.'
    );

    // Muestra la sección de juego y hace scroll
    const game = document.getElementById('game');
    if (game) {
      game.style.display = 'block';
      setTimeout(() => game.scrollIntoView({ behavior: 'smooth' }), 600);
    }
  } catch (err) {
    console.error(err);
    showModal('Error de red en el registro.');
  } finally {
    SUBMITTING = false;
  }
  return false; // nunca navegar
}

// ========================
// JUEGO
// ========================
function disableAllBoxes(){ document.querySelectorAll('.box').forEach(b => b.style.pointerEvents='none'); }
function enableAllBoxes(){ document.querySelectorAll('.box').forEach(b => b.style.pointerEvents='auto'); }

async function handleBoxClick(e) {
  const el = e.currentTarget;
  const imagen_id = parseInt(el.getAttribute('data-id'), 10);

  if (!window.PARTICIPANT_ID) { showModal('Primero completa el registro.'); return; }
  if (!imagen_id)             { showModal('Caja inválida.'); return; }

  el.classList.add('opened');
  disableAllBoxes();

  try {
    const fd = new FormData();
    fd.append('participante_id', String(window.PARTICIPANT_ID)); // el backend también usa la sesión
    fd.append('imagen_id', String(imagen_id));

    // CSRF para juego.php si lo requiere
    const tk = getCsrfToken();
    if (tk) fd.append('csrf', tk);

    const res  = await fetch('juego.php', { method: 'POST', credentials: 'same-origin', body: fd });
    const text = await res.text();
    let j = null; try { j = JSON.parse(text); } catch {}

    if (!res.ok || !j || j.ok !== true) {
      console.error('juego.php ->', res.status, text);
      throw new Error((j && j.msg) || 'Error de red al jugar.');
    }

    const premio = (j.prize && (j.prize.premio || j.prize.name)) || 'Premio';
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

// ========================
// Binds y failsafes
// ========================
document.addEventListener('DOMContentLoaded', () => {
  // Si index.php puso un hidden con el id en sesión, úsalo
  const hid = document.getElementById('participant_id');
  if (hid?.value) {
    PARTICIPANT_ID = parseInt(hid.value, 10) || null;
    window.PARTICIPANT_ID = PARTICIPANT_ID;
  }

  // Asegura que cada caja tenga data-idx (para el número en CSS) y bind de click
  document.querySelectorAll('.box').forEach((box, idx) => {
    if (!box.dataset.idx && box.dataset.id) box.dataset.idx = box.dataset.id;
    else if (!box.dataset.idx) box.dataset.idx = String(idx + 1);

    if (box.dataset.bound !== '1') {
      box.dataset.bound = '1';
      box.addEventListener('click', handleBoxClick);
      // Evitar enfoque/submit accidental si es <button>
      box.setAttribute('type','button');
    }
  });

  // Failsafe: bind al submit si alguien quita el onsubmit inline
  const form = document.getElementById('registration-form');
  if (form && !form.dataset.bound) {
    form.dataset.bound = '1';
    form.addEventListener('submit', handleRegisterSubmit, { capture: true });
  }

  console.log('Bloom Box Spa JS listo');
});

// Último salvavidas: captura global del submit del formulario de registro
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
