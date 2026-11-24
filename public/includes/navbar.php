<nav class="navbar navbar-expand-lg navbar-dark barra-nav">

    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2"
           href="<?= BASE_URL ?>/">

            <img src="<?= BASE_URL ?>/assets/img/logo.svg"
                 width="42"
                 height="42"
                 alt="">

            <span>StyleBook</span>

        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Abrir menú">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse"
             id="menuPrincipal">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= BASE_URL ?>/">
                        Inicio
                    </a>
                </li>

                <?php if (!autenticado()): ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="<?= BASE_URL ?>/#servicios">
                            Servicios
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/#contacto">
                            Contacto
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="<?= BASE_URL ?>/auth/login.php">
                            Iniciar sesión
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-dorado ms-lg-2"
                           href="<?= BASE_URL ?>/auth/registro.php">
                            Registrarse
                        </a>
                    </li>

                <?php else: ?>

                    <?php if ($_SESSION['rol'] === 'Cliente'): ?>

                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= BASE_URL ?>/cliente/index.php">
                                Mi panel
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= BASE_URL ?>/cliente/reservar.php">
                                Reservar
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= BASE_URL ?>/cliente/mis_citas.php">
                                Mis citas
                            </a>
                        </li>

                    <?php else: ?>

                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= BASE_URL ?>/peluquero/index.php">
                                Mi panel
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= BASE_URL ?>/peluquero/citas.php">
                                Agenda
                            </a>
                        </li>

                    <?php endif; ?>

                    <li class="nav-item">
                        <span class="nav-link text-white">
                            <?= e($_SESSION['nombre']) ?>
                        </span>
                    </li>

                    <li class="nav-item">

                        <form action="<?= BASE_URL ?>/auth/logout.php"
                              method="POST">

                            <input type="hidden"
                                   name="csrf"
                                   value="<?= e(tokenCsrf()) ?>">

                            <button class="btn btn-outline-light ms-lg-2"
                                    type="submit">
                                Cerrar sesión
                            </button>

                        </form>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>
