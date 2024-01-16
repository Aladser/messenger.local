<div class='container text-center mt-4'>
    <form class='login-form mx-auto' method="POST" action='user/auth' id='login-form'>
        <input type="hidden" name="CSRF" value=<?php echo $data['csrf']; ?>>
        <input type="hidden" name="login">

        <label for="login-form__email-input" class='text-start theme-width-30 p-1 fw-bolder'>Почта</label>
        <input type="email" class="d-block mx-auto theme-width-30 mb-2 p-2 border-color-theme" 
            id="login-form__email-input" name='email' placeholder='Почта' 
            required>

        <label for="login-form__password-input" class='text-start theme-width-30 p-1 fw-bolder'>Пароль</label>
        <input type="password" class="d-block mx-auto theme-width-30 mb-4 p-2 border-color-theme" 
            id="login-form__password-input" name='password' 
            placeholder='Пароль' 
            required>
        
        <input type="submit" class='d-block mx-auto theme-width-30 btn mb-2 btn-bg-theme text-white p-3 rounded' 
            value="Войти" id='login-form__login-btn'>
        <a href="/"><p class='d-block theme-width-30 mx-auto btn-bg-theme text-white p-3 rounded'>Назад</p></a>
    </form>
    <?php if (isset($data['error'])) { ?>
        <p class='w-50 mx-auto fw-bolder text-danger pt-2 mb-0' id='login-error'><?php echo $data['error']; ?></p>
    <?php } ?>
</div>