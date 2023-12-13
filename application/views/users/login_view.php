<div class='container text-center mt-4'>
    <form class='login-form mx-auto' method="POST" action='' id='login-form'>
        <input type="hidden" name="login">
        <input type="hidden" name="csrf" value=<?php echo $data['csrfToken']; ?>>

        <label for="login-form__email-input" class='text-start btn-width p-1 fw-bolder'>Почта</label>
        <input type="email" class="d-block mx-auto btn-width mb-2 p-2" id="login-form__email-input" name='email' required>

        <label for="login-form__password-input" class='text-start btn-width p-1 fw-bolder'>Пароль</label>
        <input type="password" class="d-block mx-auto btn-width mb-3 p-2" id="login-form__password-input" name='password' required>
        
        <input type="submit" class='d-block mx-auto btn-width btn mb-2 btn-bg-C4C4C4 text-white p-3' value="Войти" id='login-form__login-btn'>
        <a href="/"><p class='d-block btn-width mx-auto btn-bg-C4C4C4 text-white p-3'>Назад</p></a>
    </form>
    <p class='w-50 mx-auto fw-bolder text-dark-red d-none pt-2 mb-0' id='login-error'>Пользователь уже существует</p>
</div>
