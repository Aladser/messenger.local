<div class='container text-center'>
    <h3 class='mt-4 mb-4'>Войти</h3>
    
    <form class='login-form' method="POST" action='' id='login-form'>
        <input type="hidden" name="login">
        
        <div class='position-relative w-25 mx-auto'>
            <input type="email" class="w-100 mb-2" id="login-form__email-input" name='email' placeholder="email">
        </div>

        <div class='position-relative w-25 mx-auto'>
            <input type="password" class="w-100 mb-2" id="login-form__password-input" name='password' placeholder="пароль">
        </div>

        <div>
            <input type="submit" class='btn btn-success w-25 mb-2' value="Войти" disabled id='login-form__login-btn'>
            <br>
            <input type="button" class='btn btn-success w-25 mb-2' value="Назад" id='login-form__back-btn' >
        </div>
    </form>

    <p class='w-25 mx-auto text-danger fw-bolder hidden' id='login-error'>Пользователь уже существует</p>
</div>

<script type='text/javascript' src="application/js/validation.js"></script>