<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Transferencia</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="{{ asset('assets/fontawesome-free-6.6.0-web/css/all.min.css') }}" rel="stylesheet">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    @endif
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'figtree', sans-serif;
        }

        .dark {
            background-color: #1a202c;
            color: #e2e8f0;
        }

        .dark a {
            color: #e2e8f0;
        }

        .dark a:hover {
            color: #e2e8f0;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</head>

<body class="font-sans antialiased dark:bg-black dark:text-white/50">
    <div class="container mx-auto p-4">
        <h1 class="text-4xl font-bold text-center">Transferencia de Archivos</h1>
        <div class="flex justify-center mt-8">
            <div class="container mx-auto p-4 text-center">
                <p>Carpeta Linux</p>
                <img src="{{ asset('img/icon_linux.png') }}" alt="Transferencia" class="w-1/2 mx-auto cursor-pointer"
                    onclick="document.getElementById('fileInputLinux').click();">

                <input type="file" id="fileInputLinux" style="display: none;"
                    onchange="subirArchivo(event, 'Linux')">
                <ul id="filesListLinux">
                    <!-- Los archivos serán listados aquí -->
                </ul>
            </div>

            <div class="container mx-auto p-4 text-center">
                <p>Carpeta Windows</p>
                <img src="{{ asset('img/icon_windows.png') }}" alt="Transferencia" class="w-1/2 mx-auto cursor-pointer"
                    onclick="document.getElementById('fileInputWindows').click();">

                <input type="file" id="fileInputWindows" style="display: none;"
                    onchange="subirArchivo(event, 'Windows')">
                <ul id="filesListWindows">
                    <!-- Los archivos serán listados aquí -->
                </ul>
            </div>
        </div>
    </div>

    <button id="openModalBtn" data-bs-toggle="modal" data-bs-target="#exampleModal" style="display: none;">Abrir
        Modal</button>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content dark">
                <div class="modal-header dark">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body dark">
                    <p>Respuesta del servidor:</p>
                    <pre id="modalBody"></pre> <!-- Aquí debería mostrarse el mensaje -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let modalBody = document.getElementById('modalBody');


        function subirArchivo(event, sistema) {
            closeModal(); // Cierra el modal antes de enviar el archivo
            const archivo = event.target.files[0];

            if (!archivo) return;

            // Comprobar si el archivo tiene una extensión comprimida
            const archivoExtension = archivo.name.split('.').pop().toLowerCase();
            const extensionesComprimidas = ['zip', 'rar', 'tar', 'gz', '7z', 'deb', 'rpm', 'pkg'];

            if (extensionesComprimidas.includes(archivoExtension)) {
                modalBody.textContent = 'Los archivos comprimidos no están permitidos.';
                openModal();
                return;
            }



            // Crear un FormData para enviar el archivo y el sistema
            const formData = new FormData();
            formData.append('archivo', archivo);
            formData.append('sistema', sistema);

            // Configurar el token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Realizar la solicitud POST a la ruta
            fetch('/submit', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                })
                .then(response => {
                    // Verificar si la respuesta es JSON antes de parsearla
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        return response.json(); // Parsear como JSON si el contenido es JSON
                    } else {
                        return response.text(); // Leer como texto en otros casos
                    }
                }).then(data => {
                    // Verifica la respuesta
                    console.log('Respuesta del servidor:', data);
                    modalBody.textContent = data.message ||
                        data.error; // Asegúrate de que el campo "message" esté correctamente definido

                    // Espera a que el contenido esté actualizado antes de abrir el modal
                    setTimeout(() => {
                        openModal(); // Abre el modal después de actualizar el contenido
                    }, 100); // Un pequeño retraso para asegurarse de que el contenido se cargue
                    obtenerArchivos('Linux');
                    obtenerArchivos('Windows');
                })
                .catch(error => {
                    console.error('Error al enviar el archivo:', error);
                    modalBody.textContent = 'Error al enviar el archivo';
                    // Espera a que el contenido esté actualizado antes de abrir el modal
                    setTimeout(() => {
                        openModal(); // Abre el modal después de actualizar el contenido
                    }, 100); // Un pequeño retraso para asegurarse de que el contenido se cargue
                });
        }

        function openModal() {
            // Simula un clic en el botón que abre el modal
            document.getElementById('openModalBtn').click();
        }

        function closeModal() {
            // Simula un clic en el botón de cerrar el modal
            document.querySelector('[data-bs-dismiss="modal"]').click();
            modalBody.textContent = '';

        }

        // Función para obtener los archivos de la carpeta
        function obtenerArchivos(sistema) {
            fetch('/get-files?sistema=' + sistema) // Enviar el parámetro 'sistema' en la URL
                .then(response => response.json())
                .then(data => {
                    const files = data.files;
                    const filesList = document.getElementById('filesList' +
                    sistema); // Contenedor en tu HTML para mostrar los archivos

                    filesList.innerHTML = ''; // Limpiar antes de agregar nuevos archivos

                    // Recorrer los archivos y agregarlos a la lista en el frontend
                    files.forEach(file => {
                        const listItem = document.createElement('li');
                        listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between',
                            'align-items-center', 'my-1'); // Clases de Bootstrap

                        // Crear el icono basado en la extensión
                        const fileExtension = file.split('.').pop()
                    .toLowerCase(); // Obtener la extensión del archivo
                        const icon = getIconForExtension(fileExtension); // Función para obtener el ícono

                        // Crear el ícono de la extensión
                        const iconElement = document.createElement('i');
                        iconElement.classList.add('fas', icon); // Añadir la clase de Font Awesome al ícono

                        // Crear el texto para mostrar el nombre del archivo
                        const fileName = document.createTextNode(file); // Nombre del archivo

                        // Añadir el ícono y el nombre del archivo al item
                        const iconContainer = document.createElement('span');
                        iconContainer.classList.add('me-2'); // Margen derecho para el ícono
                        iconContainer.appendChild(iconElement);

                        listItem.appendChild(iconContainer);
                        listItem.appendChild(fileName);

                        // Crear el enlace para descargar el archivo
                        const downloadLink = document.createElement('a');
                        downloadLink.href = '/download/' + sistema + '/' + file; // Ruta para descargar el archivo
                        downloadLink.classList.add('btn', 'btn-primary',
                        'btn-sm'); // Clases para el botón de descarga
                        downloadLink.textContent = 'Descargar';

                        // Agregar el enlace de descarga al item
                        listItem.appendChild(downloadLink);

                        // Añadir el item a la lista
                        filesList.appendChild(listItem);
                    });
                })
                .catch(error => console.error('Error al obtener los archivos:', error));
        }

        // Función para devolver el ícono adecuado según la extensión
        function getIconForExtension(extension) {
            const icons = {
                'pdf': 'fa-file-pdf',
                'zip': 'fa-file-archive',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image',
                'txt': 'fa-file-alt',
                'mp4': 'fa-file-video',
                'mp3': 'fa-file-audio',
                'exe': 'fa-file-executable',
                'ppt': 'fa-file-powerpoint',
                'pptx': 'fa-file-powerpoint',
                'csv': 'fa-file-csv',
                'html': 'fa-file-code',
                'js': 'fa-file-code',
                'css': 'fa-file-code',
                'json': 'fa-file-code',
                'php': 'fa-file-code',
                'xlsx': 'fa-file-excel',
                'xls': 'fa-file-excel',
                'default': 'fa-file'
            };

            // Si la extensión existe en el objeto, devuelve el ícono; de lo contrario, usa un ícono genérico
            return icons[extension] || icons['default'];
        }


        // Llama a la función para obtener los archivos al cargar la página, por ejemplo, para el sistema Linux
        window.onload = function() {
            obtenerArchivos('Linux');
            obtenerArchivos('Windows');
        };
    </script>
    @vite('resources/js/app.js')
</body>

</html>
