// Toggle password visibility for main password field
function togglePassword() {
    var x = document.getElementById("password");
    var icon = document.getElementById("toggleIcon");
    if (x.type === "password") {
        x.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    } else {
        x.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
}

// Toggle password visibility for confirmation field
function togglePasswordConfirm() {
    var x = document.getElementById("password_confirmation");
    var icon = document.getElementById("toggleIconConfirm");
    if (x.type === "password") {
        x.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    } else {
        x.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
}

// Bootstrap validation
(function() {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
