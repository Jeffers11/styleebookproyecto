document.addEventListener("DOMContentLoaded", () => {

    const baseUrl = document.body.dataset.baseUrl || "";

    // Registrar Service Worker.
    if ("serviceWorker" in navigator) {

        navigator.serviceWorker
            .register(`${baseUrl}/sw.js`)
            .then(() => {
                console.log("Service Worker registrado.");
            })
            .catch(error => {
                console.error("Error de Service Worker:", error);
            });
    }

    // Instalación de la aplicación PWA.
    let eventoInstalacion = null;

    const btnInstalar = document.getElementById("btnInstalar");

    window.addEventListener("beforeinstallprompt", event => {

        event.preventDefault();

        eventoInstalacion = event;

        if (btnInstalar) {
            btnInstalar.hidden = false;
        }
    });

    if (btnInstalar) {

        btnInstalar.addEventListener("click", async () => {

            if (!eventoInstalacion) {
                return;
            }

            await eventoInstalacion.prompt();
            await eventoInstalacion.userChoice;

            eventoInstalacion = null;
            btnInstalar.hidden = true;
        });
    }

    window.addEventListener("appinstalled", () => {

        if (btnInstalar) {
            btnInstalar.hidden = true;
        }

        console.log("StyleBook instalada.");
    });

    // Búsqueda dinámica de citas.
    const buscador = document.getElementById("buscarCita");

    if (buscador) {

        buscador.addEventListener("input", () => {

            const texto = buscador.value.toLowerCase();

            document.querySelectorAll("[data-fila-cita]")
                .forEach(fila => {

                    const contenido =
                        fila.textContent.toLowerCase();

                    fila.hidden = !contenido.includes(texto);
                });
        });
    }

    // Mostrar u ocultar contraseña.
    document.querySelectorAll("[data-ver-password]")
        .forEach(boton => {

            boton.addEventListener("click", () => {

                const campo = document.getElementById(
                    boton.dataset.verPassword
                );

                if (!campo) return;

                campo.type = campo.type === "password"
                    ? "text"
                    : "password";

                boton.textContent = campo.type === "password"
                    ? "Mostrar"
                    : "Ocultar";
            });
        });

    // Actualizar el precio seleccionado.
    const servicio = document.getElementById("servicio");
    const precio = document.getElementById("precioServicio");

    if (servicio && precio) {

        const actualizarPrecio = () => {

            const opcion = servicio.options[
                servicio.selectedIndex
            ];

            const valor = Number(opcion.dataset.precio || 0);

            precio.textContent = `$${valor.toFixed(2)}`;
        };

        servicio.addEventListener("change", actualizarPrecio);

        actualizarPrecio();
    }

    // Confirmación antes de ejecutar operaciones importantes.
    document.querySelectorAll("form[data-confirmar]")
        .forEach(formulario => {

            formulario.addEventListener("submit", event => {

                if (!confirm(formulario.dataset.confirmar)) {
                    event.preventDefault();
                }
            });
        });

    // Detectar conexión a Internet.
    const aviso = document.createElement("div");

    aviso.className = "estado-conexion";
    aviso.textContent = "Sin conexión a Internet.";
    aviso.hidden = navigator.onLine;

    document.body.prepend(aviso);

    window.addEventListener("offline", () => {
        aviso.hidden = false;
    });

    window.addEventListener("online", () => {
        aviso.hidden = true;
    });
});
