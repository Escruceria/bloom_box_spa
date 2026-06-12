// js/confetti.js
// Biblioteca de efectos de confeti y celebración para Bloom Box Spa

class Confetti {
    constructor() {
        this.confettiColors = ['#ff0', '#f0f', '#0ff', '#4caf50', '#e91e63', '#ff5722', '#ff9800', '#00bcd4'];
        this.confettiCount = 300;
        this.confettiSpeed = 2;
        this.confettiSize = 5;
        this.confetti = [];
        this.animationId = null;
        
        this.init();
    }

    init() {
        this.createCanvas();
        this.createConfetti();
        this.animate();
    }

    createCanvas() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
        
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        
        this.canvas.style.position = 'fixed';
        this.canvas.style.top = '0';
        this.canvas.style.left = '0';
        this.canvas.style.pointerEvents = 'none';
        this.canvas.style.zIndex = '9999';
        
        document.body.appendChild(this.canvas);

        // Redimensionar canvas cuando cambie el tamaño de la ventana
        window.addEventListener('resize', () => {
            this.canvas.width = window.innerWidth;
            this.canvas.height = window.innerHeight;
        });
    }

    createConfetti() {
        for (let i = 0; i < this.confettiCount; i++) {
            this.confetti.push({
                x: Math.random() * this.canvas.width,
                y: -Math.random() * this.canvas.height,
                width: this.confettiSize + Math.random() * 10,
                height: this.confettiSize + Math.random() * 5,
                speed: this.confettiSpeed + Math.random() * 3,
                rotation: Math.random() * 360,
                rotationSpeed: Math.random() * 5 - 2.5,
                color: this.confettiColors[Math.floor(Math.random() * this.confettiColors.length)],
                shape: Math.random() > 0.5 ? 'rect' : 'circle',
                opacity: 1
            });
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        for (let i = 0; i < this.confetti.length; i++) {
            const c = this.confetti[i];

            // Actualizar posición
            c.y += c.speed;
            c.x += Math.sin(c.y * 0.01) * 0.5;
            c.rotation += c.rotationSpeed;

            // Dibujar confeti
            this.ctx.save();
            this.ctx.translate(c.x + c.width / 2, c.y + c.height / 2);
            this.ctx.rotate(c.rotation * Math.PI / 180);
            this.ctx.globalAlpha = c.opacity;

            this.ctx.fillStyle = c.color;

            if (c.shape === 'circle') {
                this.ctx.beginPath();
                this.ctx.arc(-c.width / 2, -c.height / 2, c.width / 2, 0, Math.PI * 2);
                this.ctx.fill();
            } else {
                this.ctx.fillRect(-c.width / 2, -c.height / 2, c.width, c.height);
            }

            this.ctx.restore();

            // Reiniciar confeti cuando sale de la pantalla
            if (c.y > this.canvas.height) {
                c.y = -20;
                c.x = Math.random() * this.canvas.width;
            }
        }

        this.animationId = requestAnimationFrame(() => this.animate());
    }

    stop() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }

        // Animación de desvanecimiento
        const fadeOut = () => {
            let allInvisible = true;

            for (let i = 0; i < this.confetti.length; i++) {
                this.confetti[i].opacity -= 0.02;
                if (this.confetti[i].opacity > 0) {
                    allInvisible = false;
                }
            }

            if (!allInvisible) {
                requestAnimationFrame(fadeOut);
            } else {
                if (this.canvas && document.body.contains(this.canvas)) {
                    document.body.removeChild(this.canvas);
                }
            }
        };

        fadeOut();
    }
}

// Efectos de celebración globales
class CelebrationEffects {
    constructor() {
        this.effects = {
            confetti: null,
            balloons: [],
            streamers: []
        };
    }

    // Efecto de confeti
    launchConfetti(options = {}) {
        const {
            duration = 5000,
            colors = ['#ff0', '#f0f', '#0ff', '#4caf50', '#e91e63', '#ff5722']
        } = options;

        this.effects.confetti = new Confetti();
        
        if (duration > 0) {
            setTimeout(() => {
                this.stopConfetti();
            }, duration);
        }
    }

    stopConfetti() {
        if (this.effects.confetti) {
            this.effects.confetti.stop();
            this.effects.confetti = null;
        }
    }

    // Efecto de globos
    launchBalloons(count = 20) {
        for (let i = 0; i < count; i++) {
            this.createBalloon();
        }
    }

    createBalloon() {
        const balloon = document.createElement('div');
        balloon.innerHTML = '🎈';
        balloon.style.position = 'fixed';
        balloon.style.left = Math.random() * 100 + 'vw';
        balloon.style.bottom = '-50px';
        balloon.style.fontSize = (Math.random() * 40 + 30) + 'px';
        balloon.style.zIndex = '9998';
        balloon.style.userSelect = 'none';
        balloon.style.pointerEvents = 'none';
        
        document.body.appendChild(balloon);

        // Animación
        const animationDuration = 5 + Math.random() * 5;
        const horizontalMovement = (Math.random() - 0.5) * 20;

        balloon.animate([
            { 
                transform: 'translateY(0) translateX(0)',
                opacity: 1
            },
            { 
                transform: `translateY(-120vh) translateX(${horizontalMovement}vw)`,
                opacity: 0
            }
        ], {
            duration: animationDuration * 1000,
            easing: 'linear'
        });

        // Eliminar después de la animación
        setTimeout(() => {
            if (document.body.contains(balloon)) {
                document.body.removeChild(balloon);
            }
        }, animationDuration * 1000);

        this.effects.balloons.push(balloon);
    }

    // Efecto de serpentinas
    launchStreamers(count = 40) {
        for (let i = 0; i < count; i++) {
            this.createStreamer();
        }
    }

    createStreamer() {
        const streamer = document.createElement('div');
        streamer.style.position = 'fixed';
        streamer.style.width = (5 + Math.random() * 10) + 'px';
        streamer.style.height = '2px';
        streamer.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
        streamer.style.left = Math.random() * 100 + 'vw';
        streamer.style.top = '-10px';
        streamer.style.transform = `rotate(${Math.random() * 360}deg)`;
        streamer.style.opacity = '0.8';
        streamer.style.zIndex = '9997';
        streamer.style.pointerEvents = 'none';
        
        document.body.appendChild(streamer);

        // Animación
        const animationDuration = 3 + Math.random() * 4;

        streamer.animate([
            { 
                transform: 'translateY(0) rotate(0deg)',
                opacity: 1
            },
            { 
                transform: `translateY(120vh) rotate(${Math.random() * 360}deg)`,
                opacity: 0
            }
        ], {
            duration: animationDuration * 1000,
            easing: 'linear'
        });

        // Eliminar después de la animación
        setTimeout(() => {
            if (document.body.contains(streamer)) {
                document.body.removeChild(streamer);
            }
        }, animationDuration * 1000);

        this.effects.streamers.push(streamer);
    }

    // Sonido de celebración
    playCelebrationSound() {
        try {
            // Crear un sonido simple usando el Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Configurar el sonido
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(880, audioContext.currentTime + 0.5);

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1);

            oscillator.start();
            oscillator.stop(audioContext.currentTime + 1);

            // Sonido de aplausos (usando osciladores)
            setTimeout(() => {
                this.playApplauseSound(audioContext);
            }, 300);

        } catch (error) {
            console.log('Audio no disponible:', error);
        }
    }

    playApplauseSound(audioContext) {
        // Simular aplausos con múltiples osciladores
        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.type = 'noise';
                oscillator.frequency.setValueAtTime(100 + Math.random() * 900, audioContext.currentTime);

                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2 + Math.random() * 0.3);

                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.5 + Math.random());
            }, i * 50);
        }
    }

    // Efecto completo de celebración
    launchFullCelebration(options = {}) {
        const {
            confetti = true,
            balloons = true,
            streamers = true,
            sound = true,
            duration = 5000
        } = options;

        if (confetti) {
            this.launchConfetti({ duration });
        }

        if (balloons) {
            this.launchBalloons(20);
        }

        if (streamers) {
            this.launchStreamers(40);
        }

        if (sound) {
            this.playCelebrationSound();
        }
    }

    // Detener todos los efectos
    stopAllEffects() {
        this.stopConfetti();
        
        // Eliminar globos
        this.effects.balloons.forEach(balloon => {
            if (document.body.contains(balloon)) {
                document.body.removeChild(balloon);
            }
        });
        this.effects.balloons = [];

        // Eliminar serpentinas
        this.effects.streamers.forEach(streamer => {
            if (document.body.contains(streamer)) {
                document.body.removeChild(streamer);
            }
        });
        this.effects.streamers = [];
    }
}

// API global para efectos de celebración
window.BloomCelebration = new CelebrationEffects();

// Función global para lanzar animación (compatibilidad con código existente)
window.lanzarAnimacionFinal = function() {
    window.BloomCelebration.launchFullCelebration({
        confetti: true,
        balloons: true,
        streamers: true,
        sound: true,
        duration: 8000
    });
};

// Función simple de confeti (alternativa minimalista)
window.simpleConfetti = function() {
    const confetti = new Confetti();
    setTimeout(() => confetti.stop(), 5000);
};

// Auto-inicialización cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Agregar estilos CSS para animaciones
    const style = document.createElement('style');
    style.textContent = `
        @keyframes balloonRise {
            0% { transform: translateY(0) translateX(0); opacity: 1; }
            100% { transform: translateY(-120vh) translateX(var(--balloon-drift, 0)); opacity: 0; }
        }
        
        @keyframes streamerFall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(120vh) rotate(360deg); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
});

// Exportar para módulos (si se usa import/export)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Confetti, CelebrationEffects };
}