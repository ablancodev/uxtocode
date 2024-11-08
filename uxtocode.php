<?php
/**
 * Plugin Name: Ux to Code
 * Description: Devuelve una respuesta JSON para una página específica.
 * Version: 1.0
 * Author: ablancodev
 */

 // creamos seccion admin
add_action('admin_menu', 'uxtocode_admin_menu');
// dentro de la se páginas
function uxtocode_admin_menu() {
    add_menu_page('UX to Code', 'UX to Code', 'manage_options', 'uxtocode', 'uxtocode_admin_page');

    // Subpage settings
    add_submenu_page('uxtocode', 'Settings', 'Settings', 'manage_options', 'uxtocode-settings', 'uxtocode_settings_page');

}

// es un formulario para subir una imagen, y con esa imagen se manda a un endpoint de chatgpt
function uxtocode_admin_page() {
    // si se ha enviado el formulario, enviamos la imagen a chatgpt
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ( !class_exists('Ablancodev_Openai') ) {
            require_once __DIR__ . '/openai/ablancodev-openai.php';
        }

        
        // if $_FILES['image'] is not empty
        if ( isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK ) {
            
            $image = $_FILES['image'];
            $tmp_name = $image['tmp_name'];
            $name = $image['name'];
            $type = $image['type'];
            $size = $image['size'];
            $image_data = file_get_contents($tmp_name);
            $image_base64 = base64_encode($image_data);

            
        } else { // desde drawing


            $image = $_FILES['drawing'];

            $tmp_name = $image['tmp_name'];
            $name = $image['name'];
            $type = $image['type'];
            $size = $image['size'];
            $image_data = file_get_contents($tmp_name);
            $image_base64 = base64_encode($image_data);

        }

        if ($size > 0) {
            $image_url = 'data:' . $type . ';base64,' . $image_base64;

            $prompt = 'Dame el content de una página wordpress basada en los bloques por defecto de WordPress, que represnete lo que veo en la imagen. Date el contenido para copiar-pegar en el editor modo código del admin. Las imágenes que necesites, usa urls de https://placehold.co/  .Devuelveme sólo el content del editor en texto plano, no verbose.';
            $api_key = get_option('uxtocode_openai_api_key');

            $response = Ablancodev_Openai::openai_api_request_image($prompt, $image_url, $api_key);

            $response = str_replace('```html', '', $response);
            $response = str_replace('```', '', $response);

            
            // creamos una página con el contenido
            $post = array(
                'post_title' => 'Página creada por UX To Code',
                'post_content' => $response,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'page'
            );

            $post_id = wp_insert_post($post);

            // mostramos el warning sucess
            if ($post_id) {
                echo '<div class="notice notice-success is-dismissible"><p>Página creada con éxito. <a href="' . get_edit_post_link($post_id) . '">Ver página</a></p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Error al crear la página.</p></div>';
            }
        }
    }
    ?>

    <div class="wrap">
    <h2>Ux to Code</h2>
    <form action="" method="post" enctype="multipart/form-data" onsubmit="submitForm(event)">
        
        <label for="image">Sube una imagen:</label>
        <input type="file" name="image">
        
        <h3>Dibuja aquí:</h3>
        <!-- canvas proporción 9:16 -->
        <canvas id="drawCanvas" width="560" height="1000" style="border: 1px solid black;"></canvas>
        
        <input type="hidden" name="drawing" id="drawing">
        <br>
        <input type="submit" value="Crear página" class="button button-primary">
    </form>
</div>

<script>
    const canvas = document.getElementById('drawCanvas');
    const ctx = canvas.getContext('2d');

    // Rellenar el fondo del canvas con blanco
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Configuración del trazo
    ctx.strokeStyle = 'red';
    ctx.lineWidth = 6;
    let isDrawing = false;

    canvas.addEventListener('mousedown', (event) => {
        isDrawing = true;
        ctx.beginPath();
        ctx.moveTo(event.offsetX, event.offsetY);
    });
    canvas.addEventListener('mouseup', () => isDrawing = false);
    canvas.addEventListener('mousemove', draw);

    function draw(event) {
        if (!isDrawing) return;
        
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.strokeStyle = 'black';
        
        ctx.lineTo(event.offsetX, event.offsetY);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(event.offsetX, event.offsetY);
    }


    function submitForm(event) {
        // Evitar el envío del formulario inmediato
        event.preventDefault();

        // enviamos el canvas como un file
        const file = canvas.toBlob((blob) => {
            const formData = new FormData();
            formData.append('drawing', blob, 'drawing.jpg');
            // si se a¡ha subido una imagen, la enviamos
            if (document.querySelector('input[type="file"]').files.length > 0) {
                formData.append('image', document.querySelector('input[type="file"]').files[0]);
            }


            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(response => response.text())
            .then(data => {
                document.body.innerHTML = data;
            });
        });
    }
</script>

    <?php
}

function uxtocode_settings_page() {
    echo '<h1>Settings</h1>';

    // form with API KEY
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        update_option('uxtocode_openai_api_key', sanitize_text_field($_POST['api_key']));
        echo '<div class="notice notice-success is-dismissible"><p>API Key guardada con éxito.</p></div>';
    }

    $api_key = get_option('uxtocode_openai_api_key');
    ?>
    <form action="" method="post">
        <label for="api_key">API Key:</label>
        <input type="text" name="api_key" value="<?php echo $api_key; ?>">
        <br>
        <input type="submit" value="Guardar" class="button button-primary">
    </form>
    <?php
}