<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- меты -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (!empty($head)) {
        echo $head;
    } ?>

    <title><?php echo $page_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="icon" href="http://<?php echo $app_name; ?>/application/images/icon.png">
    <link rel="stylesheet" href="http://<?php echo $app_name; ?>/application/css/reset_styles.css">
    <link rel="stylesheet" href="http://<?php echo $app_name; ?>/application/css/template.css">
    <?php if (!empty($content_css)) { ?>
        <link rel="stylesheet" href="http://<?php echo $app_name; ?>/application/css/<?php echo $content_css; ?>">
    <?php } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <?php if (!empty($content_js)) { ?>
        <script type='text/javascript' src="http://<?php echo $app_name; ?>/application/js/<?php echo $content_js; ?>" defer></script>
    <?php } ?>
</head>

<body>
<header>
    <?php if (!is_null($data)) { ?>
        <?php if (isset($data['publicUsername'])) { ?>
            <h3 class='text-center p-4 text-white bg-c4c4c4'>
                <?php echo $page_name; ?>
                <span id='clientuser' data-clientuser-publicname=<?php echo $data['publicUsername']; ?>>
                    <?php echo $data['user-email']; ?>
                </span>
            </h3>
        <?php } elseif (isset($data['user-email'])) { ?>
            <h3 class='text-center p-4 text-white bg-c4c4c4'>
                <?php echo $page_name; ?><span id='userhost'> <?php echo $data['user-email']; ?></span>
            </h3>
        <?php } elseif (isset($data['csrfToken'])) { ?>
            <h3 class='text-center p-4 text-white bg-c4c4c4'><?php echo $page_name; ?></h3>
        <?php } ?>
    <?php } else { ?>
        <h3 class='text-center p-4 text-white bg-c4c4c4'><?php echo $page_name; ?></h3>
    <?php } ?>
</header>

<?php require_once $content_view; ?>

</body>
</html>
