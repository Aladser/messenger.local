<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение почты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="icon" href="../images/icon.png">
</head>
<body>
    <br><br>
    <?php
        require_once(dirname(__DIR__, 1).'/core/ConfigClass.php');
        require_once('UsersDBModel.php');

        $users = new UsersDBModel($CONFIG->getDBQueryClass());
        if($users->checkUserHash($_GET['email'], $_GET['hash'])){
            $users->confirmEmail($_GET['email']);
    ?>
            <p class='h4 text-center'>Электронная почта подтверждена</p>
            <br><br>
            <a class='link-success nav-link bg-success w-25 text-center text-light mx-auto' href="http://messenger.local">На главную</a>
    <?  
        }
            else
        {
    ?>
            <p class='h4 text-center'>Ссылка недействительная или некорректна</p>
            <br><br>
            <a class='link-success nav-link bg-warning w-25 text-center text-light mx-auto' class='link-danger' href="http://messenger.local">На главную</a>
    <?  
        }
    ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>