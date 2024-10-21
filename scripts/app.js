// Global arrays for selected values
let asesoresSeleccionados = [];
let sedesSeleccionadas = [];
let categoriasSeleccionadas = [];

document.addEventListener('DOMContentLoaded', () => {
    fetchAsesores();
    fetchSedes();
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

function fetchSedes() {
    fetch('php/obtener_sedes.php')
        .then(response => response.json())
        .then(data => {
            const sedeSelect = document.getElementById('sede');
            data.forEach(sede => {
                const option = document.createElement('option');
                option.value = sede.ID;
                option.textContent = sede.Nombre;
                sedeSelect.appendChild(option);
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
    const asesorSelect = document.getElementById('asesor');
    const sedeSelect = document.getElementById('sede');
    const categoriaSelect = document.getElementById('categoria');

    // Manejar asesores seleccionados
    Array.from(asesorSelect.selectedOptions).forEach(option => {
        const exists = asesoresSeleccionados.find(asesor => asesor.id == option.value);
        if (!exists) { 
            asesoresSeleccionados.push({ id: option.value, name: option.text });
        }
    });

    // Manejar sedes seleccionadas
    Array.from(sedeSelect.selectedOptions).forEach(option => {
        const exists = sedesSeleccionadas.find(sede => sede.id == option.value);
        if (!exists) {
            sedesSeleccionadas.push({ id: option.value, name: option.text });
        }
    });

    // Manejar categorías seleccionadas
    Array.from(categoriaSelect.selectedOptions).forEach(option => {
        const exists = categoriasSeleccionadas.find(categoria => categoria.id == option.value);
        if (!exists) {
            categoriasSeleccionadas.push({ id: option.value, name: option.text });
        }
    });

    const asesoresList = asesoresSeleccionados.map(asesor => 
        `<li>${asesor.name} <button onclick="removeAsesor(${asesor.id})">Eliminar</button></li>`
    ).join('');
    
    const sedesList = sedesSeleccionadas.map(sede => 
        `<li>${sede.name} <button onclick="removeSede(${sede.id})">Eliminar</button></li>`
    ).join('');

    const categoriasList = categoriasSeleccionadas.map(categoria => 
        `<li>${categoria.name} <button onclick="removeCategoria(${categoria.id})">Eliminar</button></li>`
    ).join('');

    document.getElementById('resumen').innerHTML = `
        <strong>Filtros seleccionados:</strong>
        <ul>
            <li>Fecha Inicio: ${fechaInicio}</li>
            <li>Fecha Fin: ${fechaFin}</li>
            <li>Asesores: <ul>${asesoresList}</ul></li>
            <li>Sedes: <ul>${sedesList}</ul></li>
            <li>Categorías: <ul>${categoriasList}</ul></li>
        </ul>
    `;

    fetchResultados(); // Actualiza los resultados
}

// Función para eliminar asesores del resumen
function removeAsesor(asesorId) {
    asesoresSeleccionados = asesoresSeleccionados.filter(asesor => asesor.id != asesorId);

    const asesorSelect = document.getElementById('asesor');
    for (let option of asesorSelect.options) {
        if (option.value == asesorId) {
            option.selected = false;
            break;
        }
    }

    updateResumen(); 
}

// Función para eliminar sedes del resumen
function removeSede(sedeId) {
    sedesSeleccionadas = sedesSeleccionadas.filter(sede => sede.id != sedeId);

    const sedeSelect = document.getElementById('sede');
    for (let option of sedeSelect.options) {
        if (option.value == sedeId) {
            option.selected = false;
            break;
        }
    }

    updateResumen(); 
}

// Función para eliminar categorías del resumen
function removeCategoria(categoriaId) {
    categoriasSeleccionadas = categoriasSeleccionadas.filter(categoria => categoria.id != categoriaId);

    const categoriaSelect = document.getElementById('categoria');
    for (let option of categoriaSelect.options) {
        if (option.value == categoriaId) {
            option.selected = false;
            break;
        }
    }

    updateResumen(); 
}

function fetchResultados() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;

    const asesoresString = asesoresSeleccionados.map(asesor => asesor.id).join(',');
    const sedesString = sedesSeleccionadas.map(sede => sede.id).join(',');
    const categoriasString = categoriasSeleccionadas.map(categoria => categoria.id).join(',');

    fetch(`php/filtrar.php?fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&asesor=${asesoresString}&sede=${sedesString}&categoria=${categoriasString}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                mostrarResultados(data);
            } else {
                alert('No se encontraron resultados para los filtros aplicados.');
                document.getElementById('resultados').innerHTML = ''; 
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