document.addEventListener("DOMContentLoaded", function() {
    const token = localStorage.getItem('jwt_token');
    if (!token) { window.location.href = 'pages-login.html'; return; }

    const API_URL = 'http://localhost/Proyecto1_Grupo1/public';
    const headers = { 'Authorization': `Bearer ${token}` };

    const documentsTableBody = document.getElementById('documents-table-body');
    const uploadForm = document.getElementById('upload-form');
    const carpetaSelect = document.getElementById('idCarpeta');
    const uploadModal = new bootstrap.Modal(document.getElementById('upload-modal'));
    
    const editModal = new bootstrap.Modal(document.getElementById('edit-modal'));
    const editForm = document.getElementById('edit-form');
    let allDocumentsData = [];

    async function loadDocuments() {
        try {
            const response = await fetch(`${API_URL}/documento`, { headers });
            const result = await response.json();
            documentsTableBody.innerHTML = '';
            if (result.data && Array.isArray(result.data)) {
                allDocumentsData = result.data;
                result.data.forEach(doc => {
                    const row = `<tr>
                        <th scope="row">${doc.idArchivo}</th>
                        <td><i class="bi bi-file-earmark-text"></i> ${doc.nombre}</td>
                        <td>${doc.nombreUsuarioSube}</td>
                        <td>${new Date(doc.fechaSubida).toLocaleDateString()}</td>
                        <td>${doc.tamanoKB} KB</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="${doc.idArchivo}" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-info btn-sm view-btn" data-id="${doc.idArchivo}" title="Ver/Imprimir"><i class="bi bi-printer"></i></button>
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

    documentsTableBody.addEventListener('click', function(e) {
        const targetButton = e.target.closest('button');
        if (!targetButton) return;
        const docId = targetButton.dataset.id;
        
        if (targetButton.classList.contains('view-btn')) {
            const downloadUrl = `${API_URL}/documento/${docId}?token=${token}`;
            window.open(downloadUrl, '_blank');
        }

        if (targetButton.classList.contains('edit-btn')) {
            const docToEdit = allDocumentsData.find(doc => doc.idArchivo == docId);
            if (docToEdit) {
                document.getElementById('edit-doc-id').value = docToEdit.idArchivo;
                document.getElementById('edit-nombre').value = docToEdit.nombre;
                editForm.reset();
                editModal.show();
            }
        }
    });

    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const docId = document.getElementById('edit-doc-id').value;
        const fileInput = document.getElementById('edit-documentoFile');
        const formData = new FormData();
        formData.append('nombre', document.getElementById('edit-nombre').value);
        if (fileInput.files.length > 0) {
            formData.append('documento', fileInput.files[0]);
        }
        try {
            const response = await fetch(`${API_URL}/documento/${docId}`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` },
                body: formData
            });
            if (response.ok) {
                editModal.hide();
                loadDocuments();
            } else {
                alert('Error al actualizar el documento.');
            }
        } catch (error) { console.error('Error al actualizar:', error); }
    });
    
    loadDocuments();
    loadCarpetas();
});
