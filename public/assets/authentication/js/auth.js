document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const adminRegisterForm = document.getElementById('admin-register-form');
    
    const switchButtons = document.querySelectorAll('.switch-link-button');

    function hideAllForms() {
        loginForm.classList.add('hidden');
        registerForm.classList.add('hidden');
        adminRegisterForm.classList.add('hidden');
    }

    function showForm(formElement) {
        hideAllForms();
        formElement.classList.remove('hidden');
    }

    switchButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetForm = button.getAttribute('data-form');

            if (targetForm === 'login') {
                showForm(loginForm);
            } else if (targetForm === 'register') {
                showForm(registerForm);
            } else if (targetForm === 'admin-register') {
                showForm(adminRegisterForm);
            }
        });
    });

    showForm(loginForm);

    loginForm.addEventListener('submit', (e) => {
        console.log("Login form submitted");
    });
});
