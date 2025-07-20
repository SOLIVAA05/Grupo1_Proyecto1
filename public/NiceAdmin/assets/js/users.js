document.addEventListener("DOMContentLoaded", function() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        window.location.href = 'pages-login.html';
        return;
    }

    const API_URL = 'http://localhost/Proyecto1_Grupo1/public';
    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    };

    const userTableBody = document.getElementById('users-table-body');
    const userModal = new bootstrap.Modal(document.getElementById('user-modal'));
    const userForm = document.getElementById('user-form');
    const modalTitle = document.getElementById('modal-title');
    const userIdInput = document.getElementById('userId');
    const passwordField = document.getElementById('password-field');
    const addUserButton = document.querySelector('button[data-bs-target="#user-modal"]');

    let allUsersData = []; // Para guardar los datos de los usuarios y poder editarlos

    // Función para cargar los selects de Roles y Departamentos
    async function loadSelectOptions() {
        try {
            // Simulación de obtención de roles y departamentos.
            // En un futuro, esto vendría de sus propios endpoints en la API.
            const roles = [{ id: 1, nombre: 'Administrador' }, { id: 2, nombre: 'Empleado' }];
            const departamentosResponse = await fetch(`${API_URL}/departamento`, { headers });
            const departamentosResult = await departamentosResponse.json();
            const departamentos = departamentosResult.data;

            const rolSelect = document.getElementById('idRol');
            const deptoSelect = document.getElementById('idDepartamento');

            rolSelect.innerHTML = roles.map(rol => `<option value="${rol.id}">${rol.nombre}</option>`).join('');
            deptoSelect.innerHTML = departamentos.map(depto => `<option value="${depto.idDepartamento}">${depto.nombre}</option>`).join('');

        } catch (error) {
            console.error('Error cargando opciones de select:', error);
        }
    }

    // Función para cargar usuarios en la tabla
    async function loadUsers() {
        try {
            const response = await fetch(`${API_URL}/user`, { headers });
            if (!response.ok) {
                if (response.status === 401) window.location.href = 'pages-login.html';
                throw new Error('Error al cargar usuarios');
            }
            const result = await response.json();
            allUsersData = result.data; // Guardamos los datos
            
            userTableBody.innerHTML = ''; // Limpiar tabla
            allUsersData.forEach(user => {
                const row = `
                    <tr>
                        <th scope="row">${user.idUsuario}</th>
                        <td>${user.nombre}</td>
                        <td>${user.user_name}</td>
                        <td>${user.nombreRol}</td>
                        <td>${user.nombreDepartamento}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="${user.idUsuario}" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${user.idUsuario}" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                userTableBody.innerHTML += row;
            });
        } catch (error) {
            console.error('Error cargando usuarios:', error);
        }
    }

    // Configurar el modal para "Añadir Usuario"
    addUserButton.addEventListener('click', () => {
        modalTitle.textContent = 'Añadir Nuevo Usuario';
        userForm.reset();
        userIdInput.value = '';
        passwordField.style.display = 'block'; // Muestra el campo de contraseña
        userModal.show();
    });

    // Delegación de eventos para los botones de Editar y Eliminar
    userTableBody.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;

        const id = target.dataset.id;
        
        if (target.classList.contains('edit-btn')) {
            // Lógica para Editar
            const userToEdit = allUsersData.find(user => user.idUsuario == id);
            if (userToEdit) {
                modalTitle.textContent = 'Editar Usuario';
                userIdInput.value = userToEdit.idUsuario;
                document.getElementById('nombre').value = userToEdit.nombre;
                document.getElementById('user_name').value = userToEdit.user_name;
                // No podemos saber el idRol o idDepartamento desde la tabla, así que los dejamos por defecto
                // En una aplicación real, el GET /user debería devolver también los IDs.
                // document.getElementById('idRol').value = userToEdit.idRol; 
                // document.getElementById('idDepartamento').value = userToEdit.idDepartamento;

                passwordField.style.display = 'none'; // Oculta el campo de contraseña al editar
                userModal.show();
            }
        }

        if (target.classList.contains('delete-btn')) {
            // Lógica para Eliminar
            if (confirm(`¿Estás seguro de que quieres eliminar al usuario con ID ${id}?`)) {
                fetch(`${API_URL}/user/${id}`, { method: 'DELETE', headers })
                    .then(response => {
                        if (response.ok) {
                            loadUsers(); // Recargar la tabla
                        } else {
                            alert('Error al eliminar el usuario.');
                        }
                    })
                    .catch(error => console.error('Error al eliminar:', error));
            }
        }
    });

    // Evento para el formulario (Crear o Actualizar)
    userForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const userId = userIdInput.value;
        const isEditing = userId !== '';

        const userData = {
            nombre: document.getElementById('nombre').value,
            user_name: document.getElementById('user_name').value,
            idRol: document.getElementById('idRol').value,
            idDepartamento: document.getElementById('idDepartamento').value,
        };
        
        if (!isEditing) {
            userData.password = document.getElementById('password').value;
        }

        const url = isEditing ? `${API_URL}/user/${userId}` : `${API_URL}/user`;
        const method = isEditing ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers,
                body: JSON.stringify(userData)
            });
            if (response.ok) {
                userModal.hide();
                loadUsers();
            } else {
                alert('Error al guardar el usuario.');
            }
        } catch (error) {
            console.error('Error al guardar:', error);
        }
    });

    // Cargar datos iniciales
    loadSelectOptions();
    loadUsers();
});