// js/script.js
// Funcionalidades principales de Bloom Box Spa

// Datos de premios (simulando tu base de datos)
const prizes = [
    { id: 1, name: "Masaje relajante" },
    { id: 2, name: "Limpieza facial profunda" },
    { id: 3, name: "Mesoterapia" },
    { id: 4, name: "Vitaminas inyectadas" },
    { id: 5, name: "Tratamiento de glúteos" },
    { id: 6, name: "3 sesiones de láser" },
    { id: 7, name: "Masaje relajante" },
    { id: 8, name: "Limpieza facial profunda" },
    { id: 9, name: "Mesoterapia" },
    { id: 10, name: "Vitaminas inyectadas" },
    { id: 11, name: "Tratamiento de glúteos" },
    { id: 12, name: "3 sesiones de láser" }
];

// Variables globales para simular el sistema ScriptCase
let glo_nombre = "";
let glo_email = "";
let glo_telefono = "";

// Cargar la biblioteca de confeti
function loadConfettiLibrary() {
    // Verificar si ya está cargada
    if (typeof window.BloomCelebration !== 'undefined') {
        return Promise.resolve();
    }
    
    // Cargar dinámicamente si es necesario
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'js/confetti.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// Formulario de registro
document.getElementById('registration-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    
    // Validaciones básicas (simulando tu código PHP)
    if (!name || !email || !phone) {
        showModal("Todos los campos son obligatorios.");
        return;
    }
    
    if (!isValidEmail(email)) {
        showModal("Por favor, ingresa un email válido.");
        return;
    }
    
    if (phone.length < 7) {
        showModal("Por favor, ingresa un número de teléfono válido.");
        return;
    }
    
    // Simular verificación de participante existente
    const existingParticipants = [
        { email: "nataloc64@hotmail.com", phone: "3148944595" },
        { email: "palico.67@hotmail.com", phone: "3116302428" },
        { email: "maamparo66@gmail.com", phone: "3148944679" }
    ];
    
    const isExisting = existingParticipants.some(participant => 
        participant.email === email || participant.phone === phone
    );
    
    if (isExisting) {
        showModal("Ya participaste en nuestro sorteo. ¡Mucha suerte en la próxima!");
        return;
    }
    
    // Simular registro exitoso
    showModal("¡Registro exitoso! Ahora puedes participar en el juego para ganar tu premio.");
    
    // Guardar datos globales (simulando ScriptCase)
    glo_nombre = name;
    glo_email = email;
    glo_telefono = phone;
    
    // Mostrar sección de juego
    document.getElementById('game').style.display = 'block';
    
    // Scroll to game section
    setTimeout(() => {
        document.getElementById('game').scrollIntoView({ behavior: 'smooth' });
    }, 1500);
});

// Juego de cajas
document.querySelectorAll('.box').forEach(box => {
    box.addEventListener('click', function() {
        const boxId = this.getAttribute('data-id');
        this.classList.add('opened');
        
        // Deshabilitar todas las cajas después de hacer clic
        document.querySelectorAll('.box').forEach(b => {
            b.style.pointerEvents = 'none';
        });
        
        // Simular la selección de premio (como en tu código PHP)
        setTimeout(() => {
            const prize = prizes.find(p => p.id == boxId);
            
            if (prize) {
                document.getElementById('prize-name').textContent = prize.name;
                document.getElementById('result-container').style.display = 'block';
                
                // Lanzar animación de celebración
                launchCelebrationAnimation();
                
                // Scroll to results
                setTimeout(() => {
                    document.getElementById('result-container').scrollIntoView({ behavior: 'smooth' });
                }, 500);
            }
        }, 1000);
    });
});

// Función para lanzar animación de celebración
async function launchCelebrationAnimation() {
    try {
        // Cargar la biblioteca si no está cargada
        await loadConfettiLibrary();
        
        // Lanzar efectos de celebración
        window.BloomCelebration.launchFullCelebration({
            confetti: true,
            balloons: true,
            streamers: true,
            sound: true,
            duration: 8000
        });
    } catch (error) {
        console.error('Error loading celebration effects:', error);
        // Fallback a efectos básicos
        simpleCelebration();
    }
}

// Efectos básicos de celebración (fallback)
function simpleCelebration() {
    // Efecto de confeti simple
    const colors = ['#ff0', '#f0f', '#0ff', '#4caf50', '#e91e63', '#ff5722'];
    
    for (let i = 0; i < 30; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.borderRadius = '50%';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.zIndex = '9999';
            confetti.style.pointerEvents = 'none';
            
            document.body.appendChild(confetti);
            
            // Animación
            confetti.animate([
                { transform: 'translateY(0)', opacity: 1 },
                { transform: 'translateY(100vh)', opacity: 0 }
            ], {
                duration: 2000 + Math.random() * 3000,
                easing: 'cubic-bezier(0.1, 0.8, 0.1, 1)'
            });
            
            // Eliminar después de la animación
            setTimeout(() => {
                if (document.body.contains(confetti)) {
                    document.body.removeChild(confetti);
                }
            }, 5000);
        }, i * 100);
    }
}

// Funciones de utilidad
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showModal(message) {
    document.getElementById('modal-message').innerHTML = message;
    document.getElementById('message-modal').style.display = 'flex';
}

document.getElementById('close-modal').addEventListener('click', function() {
    document.getElementById('message-modal').style.display = 'none';
});

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('message-modal')) {
        document.getElementById('message-modal').style.display = 'none';
    }
});

// Precargar la biblioteca de confeti cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    loadConfettiLibrary().catch(error => {
        console.log('Confetti library not available, using fallback effects');
    });
});