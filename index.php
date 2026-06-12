<?php
/**
 * Bloom Box Spa — index.php
 * Página pública con Home, Servicios, Sorteo (formulario) y Juego (cajas).
 * Conectada a backend via registro.php y juego.php (POST con fetch/FormData desde js/script.js).
 */

require_once __DIR__ . '/includes/functions.php';

/* ──────────────────────────────────────────────────────────────────────────
   1) Arranque de sesión y modo desarrollo
   ────────────────────────────────────────────────────────────────────────── */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('IS_DEVELOPMENT', ($_SERVER['SERVER_NAME'] ?? '') === 'localhost');

if (IS_DEVELOPMENT) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

/* ──────────────────────────────────────────────────────────────────────────
   2) Estado del participante en sesión (para mostrar/ocultar formulario/juego)
   - Si existe participant_id, consultamos si ya es ganador.
   - $can_play = true  → mostramos juego y ocultamos formulario
   - $can_play = false → mostramos formulario y ocultamos juego
   ────────────────────────────────────────────────────────────────────────── */
$participantId = isset($_SESSION['participant_id']) ? (int)$_SESSION['participant_id'] : 0;
$can_play = false;

if ($participantId > 0) {
    try {
        $db = pdo();
        $st = $db->prepare("SELECT ganador FROM participantes WHERE id = :id LIMIT 1");
        $st->execute([':id' => $participantId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        // Puede jugar si existe y AÚN NO es ganador
        $can_play = $row ? ((int)$row['ganador'] === 0) : false;
    } catch (Throwable $e) {
        // Si falla la consulta, asumimos que NO puede jugar (no rompemos la página pública)
        $can_play = false;
    }
}

/* ──────────────────────────────────────────────────────────────────────────
   3) Utilidades
   - csrf_token(): asumimos definida en includes/functions.php
   - esc(): para escapar atributos/HTML si la tienes definida; si no, usa htmlspecialchars
   ────────────────────────────────────────────────────────────────────────── */
if (!function_exists('esc')) {
    function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Metas básicas -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Open Graph (para compartir) -->
  <meta property="og:image" content="/bloom_box_spa/images/logo_circular.png">

  <!-- Título -->
  <title>Bloom Box Spa - Tratamientos de Belleza y Rifas</title>

  <!-- CSRF (si tu backend lo usa; js/script.js intentará leerlo si existe) -->
  <meta name="csrf-token" content="<?= esc(csrf_token()) ?>">

  <!-- Base de URLs del sitio (ajústalo a tu despliegue) -->
  <base href="/bloom_box_spa/">

  <!-- Estilos e íconos -->
  <link rel="stylesheet" href="css/style.css?v=9">
  <link rel="icon" href="images/logo_circular.png" sizes="any">
  <link rel="apple-touch-icon" href="images/logo_circular.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ====================== ENCABEZADO / MENÚ ====================== -->
<header>
  <div class="container">
    <div class="header-content">
      <div class="logo">
        <a href="#home" class="brand" aria-label="Bloom Box Spa">
          <picture>
            <!-- En móviles mostramos el logo horizontal -->
            <source srcset="images/logo_horizontal.png" media="(max-width: 768px)">
            <!-- En desktop, el circular -->
            <img src="images/logo_circular.png" alt="Bloom Box Spa">
          </picture>
        </a>
      </div>
      <nav>
        <ul>
          <li><a href="#home">Inicio</a></li>
          <li><a href="#services">Servicios</a></li>
          <li><a href="#giveaway">Sorteo</a></li>
          <li><a href="#winners">Ganadores</a></li>
          <li><a href="admin/index.php">Admin</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<!-- ====================== HERO / HOME ====================== -->
<section id="home" class="hero">
  <div class="container">
    <div class="hero-content">
      <h1>Bloom Box Spa</h1>
      <p>Descubre nuestros tratamientos exclusivos y participa en increíbles sorteos para vivir una experiencia de bienestar única.</p>
      <a href="#giveaway" class="btn">Participar en Sorteo</a>
      <a href="#services" class="btn btn-primary">Nuestros Servicios</a>
    </div>
  </div>
</section>

<!-- ====================== SERVICIOS ====================== -->
<section id="services" class="services">
  <div class="container">
    <div class="section-title">
      <h2>Nuestros Tratamientos</h2>
      <p>Descubre nuestra amplia gama de tratamientos de spa diseñados para tu bienestar y relajación.</p>
    </div>

    <!-- Cards estáticas de ejemplo; se pueden parametrizar desde BD si lo deseas -->
    <div class="service-grid">
      <div class="service-card">
        <div class="service-icon"><i class="fa-solid fa-spa" aria-hidden="true"></i></div>
        <h3>Masaje Relajante</h3>
        <p>Un masaje suave que alivia la tensión muscular y promueve la relajación profunda.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa-solid fa-face-smile" aria-hidden="true"></i></div>
        <h3>Limpieza Facial Profunda</h3>
        <p>Tratamiento que rejuvenece y revitaliza tu piel, eliminando impurezas.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa-solid fa-syringe" aria-hidden="true"></i></div>
        <h3>Mesoterapia</h3>
        <p>Tratamiento rejuvenecedor con microinyecciones de vitaminas y nutrientes.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fas fa-pills" aria-hidden="true"></i></div>
        <h3>Vitaminas Inyectadas</h3>
        <p>Aporte directo de vitaminas para revitalizar tu organismo y mejorar tu energía.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fas fa-hand-sparkles" aria-hidden="true"></i></div>
        <h3>Tratamiento de Glúteos</h3>
        <p>Mejora la apariencia y tonicidad de la zona con nuestros tratamientos especializados.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fas fa-lightbulb" aria-hidden="true"></i></div>
        <h3>Láser</h3>
        <p>Sesiones de láser para diversos tratamientos estéticos y de rejuvenecimiento.</p>
      </div>
    </div>
  </div>
</section>

<!-- ====================== SORTEO (FORMULARIO) ====================== 
     Mostrar formulario si NO puede jugar (no hay sesión o ya jugó).
     name="nombre|email|telefono" para que FormData sea compatible con registro.php
     action="#" para que el JS intercepte y NO navegue a registro.php.
-->
<section id="giveaway" class="giveaway" style="<?= $can_play ? 'display:none;' : '' ?>">
  <div class="container">
    <div class="section-title">
      <h2>Participa en Nuestro Sorteo</h2>
      <p>Regístrate para tener la oportunidad de ganar tratamientos exclusivos de Bloom Box Spa.</p>
    </div>

    <div class="giveaway-box">
      <h3>¡Sorteo Mensual de Tratamientos!</h3>
      <p>Completa el formulario para participar en nuestro sorteo mensual. ¡Cada mes sorteamos tratamientos exclusivos entre nuestros participantes!</p>
      <form id="registration-form" action="#" method="post" novalidate
          onsubmit="return handleRegisterSubmit(event)">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="name">Nombre Completo *</label>
          <input type="text" id="name" name="nombre" required>
        </div>
        <div class="form-group">
          <label for="email">Correo Electrónico *</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="phone">Teléfono *</label>
          <input type="tel" id="phone" name="telefono" required>
        </div>
        <button type="submit" id="participar-btn" class="btn">Participar en el Sorteo</button>
      </form>
    </div>
  </div>
</section>

<!-- ====================== JUEGO (CAJAS) ====================== 
     Mostrar juego si PUEDE jugar (hay participante en sesión y no es ganador).
     Se manda participante_id + imagen_id desde js/script.js a juego.php
-->
<section id="game" class="game-section" style="<?= $can_play ? '' : 'display:none;' ?>">
  <div class="container">
    <div class="game-container">
      <h2 class="game-title">¡Bienvenido a Bloom Box Spa! - ¡Descubre tu regalo!</h2>
      <p class="game-instructions">
        Da clic sobre el número de un regalo para intentar ganar, descubre tu sorpresa especial.
        Cada una guarda un tratamiento único pensado para ti. Solo puedes jugar una vez.
      </p>

      <!-- Si ya hay participante en sesión, lo exponemos para el JS -->
      <?php if ($participantId > 0): ?>
        <input type="hidden" id="participant_id" value="<?= (int)$participantId ?>">
      <?php endif; ?>

      <!-- 12 cajas (data-id = 1..12) -->
      <div class="boxes-grid gifts-grid">
        <?php for ($i = 1; $i <= 12; $i++): ?>
          <button class="box gift" data-id="<?= $i ?>" data-img="images/regalo<?= $i ?>.png" type="button">
            <img src="images/caja.png" alt="Regalo <?= $i ?>">
            <span class="gift-num"><?= $i ?></span>
          </button>
        <?php endfor; ?>
      </div>

      <!-- Contenedor del resultado (rellena js/script.js) -->
      <div class="result-container" id="result-container" style="display:none;">
        <h3 id="result-title">¡Felicidades!</h3>
        <p id="result-text">Has desbloqueado tu premio de <strong>Bloom Box Spa</strong>:</p>
        <div class="winner-prize" id="prize-name"></div>
        <p>Nos pondremos en contacto contigo para coordinar tu tratamiento.</p>
      </div>
    </div>
  </div>
</section>

<!-- ====================== GANADORES (tabla pública) ====================== -->
<section id="winners" class="winners">
  <div class="container">
    <div class="section-title">
      <h2>Últimos Ganadores</h2>
      <p>Conoce a las personas afortunadas que han ganado nuestros tratamientos exclusivos.</p>
    </div>

    <div class="table-responsive">
      <table class="winners-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Premio</th>
          </tr>
        </thead>
        <tbody>
          <?php include __DIR__ . '/ganadores.php'; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- ====================== FOOTER ====================== -->
<footer>
  <div class="container">
    <div class="footer-content">
      <div class="footer-section">
        <h3>Acerca de Bloom Box Spa</h3>
        <p>Ofrecemos tratamientos de bienestar y sorteos emocionantes para nuestros clientes, brindando experiencias únicas de relax y rejuvenecimiento.</p>
      </div>
      <div class="footer-section">
        <h3>Contáctanos</h3>
        <p><i class="fas fa-map-marker-alt"></i> Cra 29 # 69-57 Barrio Palermo, Manizales - Caldas - Colombia</p>
        <p><i class="fas fa-phone"></i> +57 313-6830234</p>
        <p><i class="fas fa-envelope"></i> antoniojoseescruceria@hotmail.com</p>
      </div>
      <div class="footer-section">
        <h3>Enlaces Rápidos</h3>
        <ul>
          <li><a href="#home">Inicio</a></li>
          <li><a href="#services">Servicios</a></li>
          <li><a href="#giveaway">Sorteo</a></li>
          <li><a href="#winners">Ganadores</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Síguenos</h3>
        <div class="social-icons">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>
    <div class="copyright">
      <p>&copy; <?= date('Y') ?> Bloom Box Spa. Desarrollado por Antonio José Escrucería Uribe - Todos los derechos reservados.</p>
    </div>
  </div>
</footer>

<!-- ====================== MODAL MENSAJES ====================== -->
<div class="modal" id="message-modal" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" id="close-modal">&times;</span>
    <div id="modal-message"></div>
  </div>
</div>

<!-- ====================== SCRIPTS ======================
     confetti.js: animaciones de celebración (cargador simple).
     script.js: lógica de registro/juego (usa FormData → registro.php / juego.php).
     Usa "defer" para asegurar que el DOM exista cuando se enganchan los listeners.
-->
<script src="js/confetti.js" defer></script>
<script src="js/script.js?v=5" defer></script>

<!-- Utilidades visuales (tu mismo script de offsets y smooth-scroll) -->
<script>
(function () {
  const header = document.querySelector('header');
  function headerH(){ return (header ? header.offsetHeight : 0) + 16; }
  function applyOffsets(){
    const h = headerH();
    document.documentElement.style.setProperty('--header-h', h + 'px');
    document.querySelectorAll('section[id]').forEach(s => s.style.scrollMarginTop = h + 'px');
  }
  function enableSmoothScroll(){
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const id = a.getAttribute('href').slice(1);
        const t = document.getElementById(id);
        if (!t) return;
        e.preventDefault();
        const y = t.getBoundingClientRect().top + window.pageYOffset - headerH();
        window.scrollTo({ top: y, behavior: 'smooth' });
        history.pushState(null, '', '#' + id);
      });
    });
  }
  function handlePrettyPaths(){
    const path = location.pathname.replace(/\/+$/,'').split('/').pop();
    const map = { 'servicios':'services', 'sorteo':'giveaway', 'ganadores':'winners', 'premiados':'winners' };
    if (map[path]) {
      const id = map[path];
      const t = document.getElementById(id);
      if (t) {
        const y = t.getBoundingClientRect().top + window.pageYOffset - headerH();
        window.scrollTo({ top: y, behavior: 'auto' });
        history.replaceState(null, '', '#' + id);
      }
    }
  }
  applyOffsets();
  enableSmoothScroll();
  handlePrettyPaths();
  addEventListener('load', applyOffsets);
  addEventListener('resize', applyOffsets);

  // Cerrar modal
  document.getElementById('close-modal')?.addEventListener('click', () => {
    const m = document.getElementById('message-modal');
    if (m) m.style.display = 'none';
  });
})();
</script>
</body>
</html>