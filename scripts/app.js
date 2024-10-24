document.addEventListener('DOMContentLoaded', () => {
    fetchAsesores();
    fetchCategorias();

    const filtroForm = document.getElementById('filtroForm');
    const inputs = filtroForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            if (areDatesSet()) {
                updateResumen();
                fetchResumen();
                fetchResultados();
                fetchCategoriasResultados();
                fetchAsesoresResultados();
            } else {
                clearTabs();
            }
        });
    });

    filtroForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (areDatesSet()) {
            updateResumen();
            fetchResumen();
            fetchResultados();
            fetchCategoriasResultados();
            fetchAsesoresResultados();
        } else {
            alert('Por favor, selecciona una fecha de inicio y una fecha de fin.');
        }
    });

    filtroForm.addEventListener('reset', function() {
        setTimeout(() => {
            updateResumen();
            fetchResumen();
            clearTabs();
        }, 0);
    });

    if (areDatesSet()) {
        fetchResumen();
    }
});

function areDatesSet() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    return fechaInicio && fechaFin;
}

function buildFormData() {
    const form = document.getElementById('filtroForm');
    const formData = new FormData();

    formData.append('fechaInicio', form.fechaInicio.value);
    formData.append('fechaFin', form.fechaFin.value);

    const asesoresSelect = form['asesor'];
    for (let option of asesoresSelect.selectedOptions) {
        formData.append('asesor[]', option.value);
    }

    const sedesSelect = form['sede'];
    for (let option of sedesSelect.selectedOptions) {
        formData.append('sede[]', option.value);
    }

    const categoriasSelect = form['categoria'];
    for (let option of categoriasSelect.selectedOptions) {
        formData.append('categoria[]', option.value);
    }

    return formData;
}

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
    const asesoresSelect = document.getElementById('asesor');
    const sedesSelect = document.getElementById('sede');
    const categoriasSelect = document.getElementById('categoria');

    if (!fechaInicio || !fechaFin) {
        document.getElementById('resumen').innerHTML = '<p>Por favor, selecciona una fecha de inicio y una fecha de fin.</p>';
        return;
    }

    const asesores = Array.from(asesoresSelect.selectedOptions).map(option => ({ id: option.value, text: option.text }));
    const sedes = Array.from(sedesSelect.selectedOptions).map(option => ({ id: option.value, text: option.text }));
    const categorias = Array.from(categoriasSelect.selectedOptions).map(option => ({ id: option.value, text: option.text }));

    let resumenHTML = '<strong>Filtros seleccionados:</strong><ul>';
    resumenHTML += `<li>Fecha Inicio: ${fechaInicio || 'N/A'}</li>`;
    resumenHTML += `<li>Fecha Fin: ${fechaFin || 'N/A'}</li>`;

    resumenHTML += '<li>Asesores: <ul>';
    asesores.forEach(asesor => {
        resumenHTML += `<li>${asesor.text} <button class="remove-filter" data-type="asesor" data-id="${asesor.id}">X</button></li>`;
    });
    resumenHTML += '</ul></li>';

    resumenHTML += '<li>Sedes: <ul>';
    sedes.forEach(sede => {
        resumenHTML += `<li>${sede.text} <button class="remove-filter" data-type="sede" data-id="${sede.id}">X</button></li>`;
    });
    resumenHTML += '</ul></li>';

    resumenHTML += '<li>Categorías: <ul>';
    categorias.forEach(categoria => {
        resumenHTML += `<li>${categoria.text} <button class="remove-filter" data-type="categoria" data-id="${categoria.id}">X</button></li>`;
    });
    resumenHTML += '</ul></li>';

    resumenHTML += '</ul>';

    document.getElementById('resumen').innerHTML = resumenHTML;

    // Agregar event listeners a los botones de eliminar filtro
    const removeButtons = document.querySelectorAll('.remove-filter');
    removeButtons.forEach(button => {
        button.addEventListener('click', removeFilter);
    });
}

function removeFilter(event) {
    const type = event.target.getAttribute('data-type');
    const id = event.target.getAttribute('data-id');

    const selectElement = document.getElementById(type);
    for (let option of selectElement.options) {
        if (option.value === id) {
            option.selected = false;
            break;
        }
    }

    updateResumen();
    fetchResumen();
    fetchResultados();
    fetchCategoriasResultados();
    fetchAsesoresResultados();
}

function fetchResumen() {
    const formData = buildFormData();
    fetch('php/obtener_resumen.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        displayResumen(data);
    });
}

function displayResumen(data) {
    const cintaResumen = document.getElementById('cintaResumen');
    cintaResumen.innerHTML = `
        <div>
            <strong>Sesiones:</strong> ${data.sesiones}
        </div>
        <div>
            <strong>Total Hrs. Alumnos:</strong> ${data.totalHorasAlumnos.toFixed(2)}
        </div>
        <div>
            <strong>Duración media de sesión:</strong> ${data.duracionMediaSesion.toFixed(2)} mins
        </div>
        <div>
            <strong>Total Hrs. Talent:</strong> ${data.totalHorasTalent.toFixed(2)}
        </div>
        <div>
            <strong>Alumnos:</strong> ${data.profesores}
        </div>
    `;
}

function fetchResultados() {
    const formData = buildFormData();
    fetch('php/filtrar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('resultadosTab').innerHTML = data;
    });
}

function fetchCategoriasResultados() {
    const formData = buildFormData();
    fetch('php/filtrar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('categoriasTab').innerHTML = data;
    });
}

function fetchAsesoresResultados() {
    const formData = buildFormData();
    fetch('php/filtrar_asesores.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('asesoresTab').innerHTML = data;
    });
}

function openTab(tabId, element) {
    var tabs = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }

    var buttons = document.getElementsByClassName('tab-button');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove('active');
    }

    document.getElementById(tabId).classList.add('active');
    element.classList.add('active');
}

function clearTabs() {
    document.getElementById('resultadosTab').innerHTML = '';
    document.getElementById('categoriasTab').innerHTML = '';
    document.getElementById('asesoresTab').innerHTML = '';
    document.getElementById('resumen').innerHTML = '';
}