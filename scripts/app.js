document.addEventListener('DOMContentLoaded', () => {
    fetchAsesores();
    fetchCategorias();

    const filtroForm = document.getElementById('filtroForm');
    const inputs = filtroForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            updateResumen();
            fetchResultados();
            fetchCategoriasResultados();
            fetchAsesoresResultados();
        });
    });

    filtroForm.addEventListener('submit', function(e) {
        e.preventDefault();
        updateResumen();
        fetchResultados();
        fetchCategoriasResultados();
        fetchAsesoresResultados();
    });

    filtroForm.addEventListener('reset', function() {
        setTimeout(() => {
            updateResumen();
            fetchResultados();
            fetchCategoriasResultados();
            fetchAsesoresResultados();
        }, 0);
    });
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
    const asesoresSelect = document.getElementById('asesor');
    const sedesSelect = document.getElementById('sede');
    const categoriasSelect = document.getElementById('categoria');

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
    fetchResultados();
    fetchCategoriasResultados();
    fetchAsesoresResultados();
}

function fetchResultados() {
    const formData = new FormData(document.getElementById('filtroForm'));
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
    const formData = new FormData(document.getElementById('filtroForm'));
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
    const formData = new FormData(document.getElementById('filtroForm'));
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