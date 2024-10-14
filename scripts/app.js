document.addEventListener('DOMContentLoaded', () => {
    fetchAsesores();
    fetchCategorias();
    document.getElementById('filtroForm').addEventListener('change', updateResumen);
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

function updateResumen() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const asesor = document.getElementById('asesor').options[document.getElementById('asesor').selectedIndex].text;
    const sede = document.getElementById('sede').options[document.getElementById('sede').selectedIndex].text;
    const categoria = document.getElementById('categoria').options[document.getElementById('categoria').selectedIndex].text;

    document.getElementById('resumen').innerHTML = `
        <strong>Filtros seleccionados:</strong>
        <ul>
            <li>Fecha Inicio: ${fechaInicio}</li>
            <li>Fecha Fin: ${fechaFin}</li>
            <li>Asesor: ${asesor}</li>
            <li>Sede: ${sede}</li>
            <li>Categoría: ${categoria}</li>
        </ul>
    `;
}