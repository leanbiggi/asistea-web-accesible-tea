<style>
    /* Oculta el enlace por defecto fuera de la pantalla */
.skip-link {
    position: absolute;
    top: 8px;
    left: 8px;
    background-color: #00796b;
    color: white;
    padding: 8px 16px;
    font-weight: bold;
    text-decoration: none;
    transform: translateY(-200%);
    transition: transform 0.3s ease;
    z-index: 1000;
}

.skip-link:focus,
.skip-link:focus-visible {
    transform: translateY(0);
}

</style>

<a href="#contenido-principal" class="skip-link">Ir al contenido principal</a>

<!-- IMPORTANTE: el enlace apunta al id="contenido-principal"  Porl o tanto, debe haber una caja (mejor un main) con este id en cada pagina
que se quiera usar este codigo   

Tambien puedo agregar un main en panel_padres.php-->