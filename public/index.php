<?php

require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Inicio';

require __DIR__ . '/includes/header.php';

$rutaReserva = 'auth/login.php';

if (autenticado()) {

    $rutaReserva = $_SESSION['rol'] === 'Cliente'
        ? 'cliente/reservar.php'
        : 'peluquero/index.php';
}

$mostrarReserva = $rutaReserva !== 'peluquero/index.php';

?>

<section class="hero">

    <div class="container">

        <div class="row align-items-center g-4 g-xl-5">

            <div class="col-lg-5">

                <h1 class="mt-3 mb-4">
                    Tu estilo comienza con una cita.
                </h1>

                <p>
                    Reserva tu próximo corte de cabello,
                    selecciona tu servicio favorito y elige
                    el horario que mejor se adapte a ti.
                </p>

                <a href="<?= BASE_URL . '/' . $rutaReserva ?>"
                   class="btn btn-dorado btn-lg mt-3">

                    Reservar ahora

                </a>

                <a href="#servicios"
                   class="btn btn-outline-light btn-lg mt-3">

                    Ver servicios

                </a>

            </div>

            <div class="col-lg-7">

                <img src="<?= BASE_URL ?>/assets/img/salon-1200.webp"
                     srcset="<?= BASE_URL ?>/assets/img/salon-640.webp 640w,
                             <?= BASE_URL ?>/assets/img/salon-1200.webp 1200w"
                     sizes="(min-width: 1400px) 750px, (min-width: 1200px) 640px, (min-width: 992px) 550px, (min-width: 768px) 696px, (min-width: 576px) 516px, calc(100vw - 24px)"
                     width="1200"
                     height="800"
                     fetchpriority="high"
                     class="foto-portada"
                     alt="Sillón de peluquería junto a un espejo arqueado en un salón iluminado">

            </div>

        </div>

    </div>

</section>

<section id="servicios" class="seccion">

    <div class="container">

        <h2 class="titulo-seccion">
            Nuestros servicios
        </h2>

        <p class="subtitulo-seccion">
            Encuentra el servicio perfecto para tu estilo.
        </p>

        <div class="row g-4 justify-content-center">

            <?php foreach (catalogoServicios() as $nombre => $servicio): ?>

                <div class="col-sm-6 col-lg-4 col-xl-3">

                    <article class="tarjeta-servicio">
                        <img src="<?= BASE_URL ?>/assets/img/<?= e($servicio['imagen']) ?>-400.webp"
                             srcset="<?= BASE_URL ?>/assets/img/<?= e($servicio['imagen']) ?>-400.webp 400w,
                                     <?= BASE_URL ?>/assets/img/<?= e($servicio['imagen']) ?>-760.webp 760w"
                             sizes="(min-width: 1400px) 306px, (min-width: 1200px) 261px, (min-width: 992px) 296px, (min-width: 768px) 336px, (min-width: 576px) 246px, calc(100vw - 24px)"
                             width="400"
                             height="267"
                             loading="lazy"
                             decoding="async"
                             class="foto-servicio"
                             alt="">
                        <div class="detalle-servicio">
                            <h3><?= e($nombre) ?></h3>
                            <p class="precio-servicio">$<?= number_format($servicio['precio'], 2) ?></p>
                            <?php if ($mostrarReserva): ?>
                                <a href="<?= BASE_URL . '/' . $rutaReserva ?>?servicio=<?= rawurlencode($nombre) ?>"
                                   class="btn btn-carbon" aria-label="Reservar <?= e($nombre) ?>">
                                    Reservar
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<section class="seccion seccion-pasos">

    <div class="container">

        <h2 class="titulo-seccion">
            ¿Cómo funciona?
        </h2>

        <p class="subtitulo-seccion">
            Reserva tu cita en tres sencillos pasos.
        </p>

        <div class="row g-4 pasos-reserva">

            <div class="col-md-4">

                <h4>Crea tu cuenta</h4>

                <p>Regístrate gratuitamente en StyleBook.</p>

            </div>

            <div class="col-md-4">

                <h4>Reserva tu cita</h4>

                <p>Selecciona peluquero, servicio y horario.</p>

            </div>

            <div class="col-md-4">

                <h4>Recibe confirmación</h4>

                <p>Consulta el estado de tu cita desde tu cuenta.</p>

            </div>

        </div>

    </div>

</section>

<section class="seccion seccion-horario">

    <div class="container text-center">

        <h2 class="titulo-seccion" id="contacto">Contacto y horario</h2>


        <dl class="datos-contacto">
            <div>
                <dt>Ubicación</dt>
                <dd>Guayaquil, Ecuador</dd>
            </div>
            <div>
                <dt>Teléfono</dt>
                <dd>+593 4 000 0000</dd>
            </div>
            <div>
                <dt>Correo electrónico</dt>
                <dd>contacto@stylebook.com</dd>
            </div>
            <div>
                <dt>Horario de atención</dt>
                <dd>Lunes a domingo, 09:00 a 18:00</dd>
                <dd>Atención con cita previa</dd>
            </div>
        </dl>

    </div>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
