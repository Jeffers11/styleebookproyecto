document.addEventListener("DOMContentLoaded", () => {

    const formularios = document.querySelectorAll(
        ".needs-validation"
    );

    formularios.forEach(formulario => {

        formulario.addEventListener("submit", event => {

            if (!formulario.checkValidity()) {

                event.preventDefault();
                event.stopPropagation();
            }

            formulario.classList.add("was-validated");
        });
    });

    // Validar coincidencia de contraseñas.
    const password = document.getElementById("password");

    const confirmar = document.getElementById(
        "confirmar_password"
    );

    if (password && confirmar) {

        const validarPassword = () => {

            if (password.value !== confirmar.value) {

                confirmar.setCustomValidity(
                    "Las contraseñas no coinciden."
                );

            } else {

                confirmar.setCustomValidity("");
            }
        };

        password.addEventListener("input", validarPassword);

        confirmar.addEventListener("input", validarPassword);
    }

    // Validar horario de una reserva.
    const fechaHora = document.getElementById("fecha_hora");

    if (fechaHora) {

        const validarHorario = () => {

            const valor = fechaHora.value;

            if (!valor) {
                fechaHora.setCustomValidity("");
                return;
            }

            const hora = Number(valor.slice(11, 13));
            const minuto = Number(valor.slice(14, 16));

            const horarioValido =
                hora >= 9 &&
                hora < 18 &&
                (minuto === 0 || minuto === 30);

            if (!horarioValido) {

                fechaHora.setCustomValidity(
                    "Selecciona un horario entre 09:00 y 17:30."
                );

            } else {

                fechaHora.setCustomValidity("");
            }

        };

        fechaHora.addEventListener("input", validarHorario);
        validarHorario();
    }
});
