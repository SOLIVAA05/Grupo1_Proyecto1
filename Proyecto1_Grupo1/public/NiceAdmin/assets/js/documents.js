document.addEventListener("DOMContentLoaded", function() {
    const token = localStorage.getItem('jwt_token');
    if (!token) { window.location.href = 'pages-login.html'; return; }

    const API_URL = 'http://localhost/Proyecto1_Grupo1/public';
    const headers = { 'Authorization': `Bearer ${token}` };

    const documentsTableBody = document.getElementById('documents-table-body');
    const uploadForm = document.getElementById('upload-form');
    const carpetaSelect = document.getElementById('idCarpeta');
    const uploadModal = new bootstrap.Modal(document.getElementById('upload-modal'));

    async function loadDocuments() { /* ... (esta función no cambia) ... */ }
    async function loadCarpetas() { /* ... (esta función no cambia) ... */ }
    uploadForm.addEventListener('submit', async function(e) { /* ... (esta función no cambia) ... */ });

    // --- LÓGICA DE IMPRESIÓN/DESCARGA ACTUALIZADA ---
    documentsTableBody.addEventListener('click', function(e) {
        const targetButton = e.target.closest('button.view-btn'); // Más específico
        if (!targetButton) return;

        const docId = targetButton.dataset.id;
        
        // ¡CAMBIO CLAVE! AÑADIMOS EL TOKEN A LA URL
        const downloadUrl = `${API_URL}/documento/${docId}?token=${token}`;
        
        // Abrimos el archivo en una nueva pestaña.
        window.open(downloadUrl, '_blank');
    });

    // CÓDIGO COMPLETO DE LAS FUNCIONES QUE NO CAMBIAN
    async function loadDocuments() {
        try {
            const response = await fetch(`${API_URL}/documento`, { headers });
            const result = await response.json();
            documentsTableBody.innerHTML = '';
            if (result.data && Array.isArray(result.data)) {
                result.data.forEach(doc => {
                    const row = `<tr>
                        <th scope="row">${doc.idArchivo}</th>
                        <td><i class="bi bi-file-earmark-text"></i> ${doc.nombre}</td>
                        <td>${doc.nombreUsuarioSube}</td>
                        <td>${new Date(doc.fechaSubida).toLocaleDateString()}</td>
                        <td>${doc.tamanoKB} KB</td>
                        <td>
                            <button class="btn btn-info btn-sm view-btn" data-id="${doc.idArchivo}" title="Ver/Imprimir">
                                <i class="bi bi-printer"></i>
                            </button>
                        </td>
                    </tr>`;
                    documentsTableBody.innerHTML += row;
                });
            }
        } catch (error) { console.error('Error cargando documentos:', error); }
    }

    async function loadCarpetas() {
        try {
            const carpetas = [ { idCarpeta: 1, nombre: 'Archivos Generales' }, { idCarpeta: 2, nombre: 'Proyectos de Tecnología' }, { idCarpeta: 3, nombre: 'Documentos de RRHH' } ];
            carpetaSelect.innerHTML = carpetas.map(c => `<option value="${c.idCarpeta}">${c.nombre}</option>`).join('');
        } catch (error) { console.error('Error cargando carpetas:', error); }
    }

    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('documentoFile');
        const idCarpeta = carpetaSelect.value;
        if (fileInput.files.length === 0) { alert('Por favor, selecciona un archivo.'); return; }
        const formData = new FormData();
        formData.append('documento', fileInput.files[0]);
        formData.append('idCarpeta', idCarpeta);
        try {
            const response = await fetch(`${API_URL}/documento`, { method: 'POST', headers: { 'Authorization': `Bearer ${token}` }, body: formData });
            if (response.ok) {
                uploadModal.hide();
                loadDocuments();
            } else {
                const error = await response.json();
                alert(`Error al subir el archivo: ${error.message}`);
            }
        } catch (error) { console.error('Error en la subida:', error); }
    });

    loadDocuments();
    loadCarpetas();
});
