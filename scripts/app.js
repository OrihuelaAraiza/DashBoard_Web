document.addEventListener('DOMContentLoaded', () => {
    fetchAsesores();
    fetchCategorias();
});

function fetchAsesores() {
    fetch('php/obtener_asesores.php')
        .then(response => response.json())
        .then(data => {
            const asesorSelect = document.getElementById('asesor');
            data.forEach(asesor => {
                const option = document.createElement('option');
                option.value = asesor.ID;
                option.textContent = asesor.Nombre;
                asesorSelect.appendChild(option);
            });
        });
}

function fetchCategorias() {
    fetch('php/obtener_categorias.php')
        .then(response => response.json())
        .then(data => {
            const categoriaSelect = document.getElementById('categoria');
            data.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.ID;
                option.textContent = categoria.Nombre;
                categoriaSelect.appendChild(option);
            });
        });
}