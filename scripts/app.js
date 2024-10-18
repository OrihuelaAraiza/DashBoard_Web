// Arreglo global para almacenar los asesores seleccionados
let asesoresSeleccionados = [];

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

function fetchResultados() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const sede = document.getElementById('sede').value;
    const categoria = document.getElementById('categoria').value;

    const asesoresString = asesoresSeleccionados.map(asesor => asesor.id).join(',');

    fetch(`php/filtrar.php?fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&asesor=${asesoresString}&sede=${sede}&categoria=${categoria}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                mostrarResultados(data);
            } else {
                alert('No se encontraron resultados para los filtros aplicados.');
                document.getElementById('resultados').innerHTML = ''; // Limpiar resultados previos
            }
        })
        .catch(error => console.error('Error:', error));
}

function mostrarResultados(data) {
    const resultadosDiv = document.getElementById('resultados');
    resultadosDiv.innerHTML = "<table><tr><th>ID</th><th>Correo</th><th>Fecha</th><th>Duración</th><th>Categoría</th><th>Asesor</th></tr>";

    data.forEach(row => {
        resultadosDiv.innerHTML += `
            <tr>
                <td>${row.ID}</td>
                <td>${row.Correo}</td>
                <td>${row.Fecha}</td>
                <td>${row.Duracion}</td>
                <td>${row.Categoria}</td>
                <td>${row.Asesor}</td>
            </tr>
        `;
    });

    resultadosDiv.innerHTML += "</table>";
}

// Actualiza el resumen con asesores seleccionados y permite eliminarlos
function updateResumen() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const asesorSelect = document.getElementById('asesor');
    const sede = document.getElementById('sede').options[document.getElementById('sede').selectedIndex].text;
    const categoria = document.getElementById('categoria').options[document.getElementById('categoria').selectedIndex].text;

    // Obtener los asesores seleccionados actualmente y agregarlos al arreglo global
    Array.from(asesorSelect.selectedOptions).forEach(option => {
        const exists = asesoresSeleccionados.find(asesor => asesor.id == option.value);
        if (!exists) { // Solo agregar si no está ya en el arreglo
            asesoresSeleccionados.push({ id: option.value, name: option.text });
        }
    });

    const asesoresList = asesoresSeleccionados.map(asesor => 
        `<li>${asesor.name} <button onclick="removeAsesor(${asesor.id})">Eliminar</button></li>`
    ).join('');

    document.getElementById('resumen').innerHTML = `
        <strong>Filtros seleccionados:</strong>
        <ul>
            <li>Fecha Inicio: ${fechaInicio}</li>
            <li>Fecha Fin: ${fechaFin}</li>
            <li>Asesores: <ul>${asesoresList}</ul></li>
            <li>Sede: ${sede}</li>
            <li>Categoría: ${categoria}</li>
        </ul>
    `;

    fetchCategoriasFiltradas(); // Llamamos a esta función para que la pestaña de categorías se actualice con los filtros seleccionados
}

// Función para eliminar asesores desde el resumen
function removeAsesor(asesorId) {
    // Filtrar para quitar el asesor seleccionado
    asesoresSeleccionados = asesoresSeleccionados.filter(asesor => asesor.id != asesorId);

    // Deseleccionar en el campo de selección
    const asesorSelect = document.getElementById('asesor');
    for (let option of asesorSelect.options) {
        if (option.value == asesorId) {
            option.selected = false; // Deseleccionar asesor
            break;
        }
    }

    updateResumen(); // Actualizar el resumen para reflejar los cambios
}

// Función para obtener los resultados de la pestaña de Categorías filtrados por los parámetros
function fetchCategoriasFiltradas() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const asesoresString = asesoresSeleccionados.map(asesor => asesor.id).join(',');
    const sede = document.getElementById('sede').value;
    const categoria = document.getElementById('categoria').value;

    fetch(`php/categorias.php?fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&asesor=${asesoresString}&sede=${sede}&categoria=${categoria}`)
        .then(response => response.json())
        .then(data => {
            mostrarCategoriasFiltradas(data);
        })
        .catch(error => console.error('Error:', error));
}

// Función para mostrar los resultados de Categorías en el DOM
function mostrarCategoriasFiltradas(data) {
    const categoriasDiv = document.getElementById('categorias');
    categoriasDiv.innerHTML = "<table><tr><th>Key</th><th>Nombre</th><th>Sesiones</th><th>Profesores</th><th>Total Horas Prof</th><th>Total Horas Talent</th><th>Duración Media Prof</th><th>Duración Media Talent</th></tr>";

    data.forEach(row => {
        categoriasDiv.innerHTML += `
            <tr>
                <td>${row.Key}</td>
                <td>${row.Nombre}</td>
                <td>${row.Sesiones}</td>
                <td>${row.Profesores}</td>
                <td>${row.TotalHorasProf}</td>
                <td>${row.TotalHorasTalent}</td>
                <td>${row.DuracionMediaProf}</td>
                <td>${row.DuracionMediaTalent}</td>
            </tr>
        `;
    });

    categoriasDiv.innerHTML += "</table>";
}