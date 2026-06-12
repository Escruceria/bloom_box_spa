<?php require_once __DIR__ . '/includes/functions.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="/bloom_box_spa/images/logo_horizontal.png">
    <title>Bloom Box Spa - Tratamientos de Belleza y Rifas</title>
    <!-- fuerza a que TODAS las rutas relativas salgan de /bloom_box_spa/ -->
    <base href="/bloom_box_spa/">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style.css?v=4">
    <link rel="icon" href="/bloom_box_spa/images/logo_circular.png" sizes="any">
    <link rel="apple-touch-icon" href="/bloom_box_spa/images/logo_circular.png">
    <link rel="stylesheet" href="/bloom_box_spa/css/style.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/bloom_box_spa/#home" class="brand" aria-label="Bloom Box Spa">
                        <picture>
                            <source media="(min-width:768px)" srcset="/bloom_box_spa/images/logo_horizontal.png">
                            <img src="/bloom_box_spa/images/logo_circular.png" alt="Bloom Box Spa" loading="lazy">
                        </picture>
                    </a>
                    <!-- si quieres texto al lado, déjalo; si no, quítalo -->
                    <!-- <span>Bloom Box Spa</span> -->
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

    <!-- Hero Section -->
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

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Tratamientos</h2>
                <p>Descubre nuestra amplia gama de tratamientos de spa diseñados para tu bienestar y relajación.</p>
            </div>
            <div class="service-grid">
                <!-- Masaje Relajante -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fa-solid fa-spa" aria-hidden="true"></i>
                    </div>
                    <h3>Masaje Relajante</h3>
                    <p>Un masaje suave que alivia la tensión muscular y promueve la relajación profunda.</p>
                </div>
                <!-- Limpieza Facial Profunda -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fa-solid fa-face-smile" aria-hidden="true"></i>
                    </div>
                    <h3>Limpieza Facial Profunda</h3>
                    <p>Tratamiento que rejuvenece y revitaliza tu piel, eliminando impurezas.</p>
                </div>
                <!-- Mesoterapia -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fa-solid fa-syringe" aria-hidden="true"></i>
                    </div>
                    <h3>Mesoterapia</h3>
                    <p>Tratamiento rejuvenecedor con microinyecciones de vitaminas y nutrientes.</p>
                </div>
                <!-- Vitaminas Inyectadas -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-pills" aria-hidden="true"></i>
                    </div>
                    <h3>Vitaminas Inyectadas</h3>
                    <p>Aporte directo de vitaminas para revitalizar tu organismo y mejorar tu energía.</p>
                </div>
                <!-- Tratamiento de Glúteos -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-hand-sparkles" aria-hidden="true"></i>
                    </div>
                    <h3>Tratamiento de Glúteos</h3>
                    <p>Mejora la apariencia y tonicidad de la zona con nuestros tratamientos especializados.</p>
                </div>
                <!-- Láser -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-lightbulb" aria-hidden="true"></i>
                    </div>
                    <h3>Láser</h3>
                    <p>Sesiones de láser para diversos tratamientos estéticos y de rejuvenecimiento.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Giveaway Section -->
    <section id="giveaway" class="giveaway">
        <div class="container">
            <div class="section-title">
                <h2>Participa en Nuestro Sorteo</h2>
                <p>Regístrate para tener la oportunidad de ganar tratamientos exclusivos de Bloom Box Spa.</p>
            </div>
            <div class="giveaway-box">
                <h3>¡Sorteo Mensual de Tratamientos!</h3>
                <p>Completa el formulario para participar en nuestro sorteo mensual. ¡Cada mes sorteamos tratamientos exclusivos entre nuestros participantes!</p>
                
                <form id="registration-form" action="/bloom_box_spa/registro.php" method="POST">

                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="name">Nombre Completo *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Teléfono *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <button type="submit" class="btn">Participar en el Sorteo</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Game Section -->
    <section id="game" class="game-section" style="display: none;">
        <div class="container">
            <div class="game-container">
                <h2 class="game-title">¡Bienvenido a Bloom Box Spa!</h2>
                <p class="game-instructions">Da clic sobre el número que identifica cada una de las cajas y descubre tu sorpresa especial. Cada una guarda un tratamiento único pensado para ti.</p>
                
                <div class="boxes-grid">
                    <div class="box" data-id="1">1</div>
                    <div class="box" data-id="2">2</div>
                    <div class="box" data-id="3">3</div>
                    <div class="box" data-id="4">4</div>
                    <div class="box" data-id="5">5</div>
                    <div class="box" data-id="6">6</div>
                    <div class="box" data-id="7">7</div>
                    <div class="box" data-id="8">8</div>
                    <div class="box" data-id="9">9</div>
                    <div class="box" data-id="10">10</div>
                    <div class="box" data-id="11">11</div>
                    <div class="box" data-id="12">12</div>
                </div>
                
                <div class="result-container" id="result-container">
                    <h3>¡Felicidades!</h3>
                    <p>Has desbloqueado tu premio especial de <strong>Bloom Spa</strong>:</p>
                    <div class="winner-prize" id="prize-name"></div>
                    <p>Nos pondremos en contacto contigo para coordinar tu tratamiento.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Winners Section -->
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

    <!-- Footer -->
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
                <p>&copy; 2025 Bloom Box Spa. Desarrollado por Antonio José Escrucería Uribe - Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal for messages -->
    <div class="modal" id="message-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-modal">&times;</span>
            <div id="modal-message"></div>
        </div>
    </div>

    <script src="/bloom_box_spa/js/confetti.js"></script>
    <script src="/bloom_box_spa/js/script.js"></script>
</body>
</html>