<container class='container font-roboto mt-4 position-relative' id='container-profile'>
    <div class='profile-container'>
        <div class='profile-img-block'>
            <?php
            if (is_null($data['photo'])) {
                $photo = "http://$app_name/public/images/ava_profile.png";
            } else {
                $photo = "http://$app_name/application/data/profile_photos//".$data['photo'];
            }
            ?>
            <img src="<?php echo $photo; ?>" id='profile-img' class="rounded-circle img" alt="Avatar"/>
        </div>

        <div class='p-4'>
            <table class='table'>
                <tr>
                    <td>Почта:</td>
                    <td><?php echo $data['email']; ?></td>
                </tr>
                <tr>
                    <td data-bs-toggle="tooltip">Никнейм:</td>
                    <td><input type="text" class='input-nickname' id='input-nickname' value="<?php echo is_null($data['nickname']) ? '' : $data['nickname']; ?>" disabled>
                    </td>
                </tr>
            </table>
            <div class="form-check form-switch mb-3 d-flex justify-content-center d-none" id='hide-email-input-block'>
                <input class="form-check-input" type="checkbox"
                       id="hide-email-input" <?php echo $data['hide_email'] == 1 ? 'checked' : ''; ?> >
                <label class="form-check-label" for="hide-email-input">&nbsp; скрыть почту</label>
            </div>
            <button class='btn btn-bg-theme text-white w-100 d-none' id='save-profile-settings-btn'>Сохранить</button>
        </div>

        <div class='options mb-2'>
            <form enctype="multipart/form-data" method='post' id='upload-file-form'>
                <input type="file" id="select-file-input" accept="image/*" name='image' class='d-none'>
                <input type="submit" id='upload-file-btn' class='d-none'>
                <button class='btn btn-bg-theme text-white w-100 mb-2' id='edit-photo-btn'>Изменить фото</button>
            </form>

            <button class='btn btn-bg-theme text-white w-100 mb-2' id='btn-edit-nickname'>Установить имя</button>
            <a href="\dialogs" class='text-decoration-none'>
                <div class='btn-bg-theme text-white w-100 p-2 text-center rounded'>Назад</div>
            </a>
        </div>
    </div>
    <br>
    <p class='text-center text-danger fs-5 fw-bolder d-none' id='prg-error'></p>
</container>