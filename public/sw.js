// La agenda y los formularios requieren conexión y datos actualizados.
self.addEventListener("fetch", event => {
    if (event.request.mode === "navigate") {
        event.respondWith(fetch(event.request));
    }
});
