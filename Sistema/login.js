const $submit = document.getElementById("submit"),
      $password = document.getElementById("password"),
      $username = document.getElementById("username"),
      $visible = document.getElementById("visible");

// Función para manejar la visibilidad de la contraseña
document.addEventListener("change", (e) => {
    if(e.target === $visible) {
        if($visible.checked === false) $password.type = "password";
        else $password.type = "text";
    }
});

// Manejar el envío del formulario
document.addEventListener("submit", (e) => {
    e.preventDefault();
    
    if($password.value !== "" && $username.value !== "") {
        // Aquí podrías agregar tu lógica de autenticación
        
        // Obtener la ventana original (la que abrió este login)
        const originWindow = window.opener;
        
        if(originWindow) {
            // Redirigir la ventana original al index
            originWindow.location.href = "index.html";
            // Cerrar la ventana de login
            window.close();
        } else {
            // Si no hay ventana original, redirigir en la misma ventana
            window.location.href = "index.html";
        }
    }
});

// Agregar validación básica
$username.addEventListener("input", validateForm);
$password.addEventListener("input", validateForm);

function validateForm() {
    const isValid = $username.value.length > 0 && $password.value.length > 0;
    $submit.disabled = !isValid;
    $submit.style.opacity = isValid ? "1" : "0.5";
}

// Inicializar el estado del botón
validateForm();