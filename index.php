<?php
session_start();
require_once ('lib/config.php');

#connect file db->file
require_once 'lib/auth.php';

require_once ('lib/main.php');

require_once ('lib/post.php');

require_once ('lib/comment.php');
#Роутинг - страницы перехода
$output = '';
$url = $_GET['page'];
if($url == 'registration'){
    if(!isset($_SESSION['user_id']))
        $output = Registration($db);
    else
        $output ='<div class="error">Невозможно зарегистрироваться!</div>';
}
else if($url == 'authorization'){
    if(!isset($_SESSION['user_id']))
        $output = Authorization($db);
    else
        $output ='<div class="error">Невозможно еще раз авторизироваться!</div>';
}
else if($url == 'logout'){
    if(isset($_SESSION['user_id']))
        logout();
    else
        $output ='<div class="error">Невозможно выйти из аккаунта!</div>';
}
else if($url == 'editAccount'){
    if(isset($_SESSION['user_id'])){
        $output = main();
        $output = Update($db);
    }
    else
        $output ='<div class="error">403 - Нет доступа!</div>';
}
else if($url == 'addPost'){
    if(isset($_SESSION['user_id'])){
        $output = main();
        $output = createOrUpdatePost($db);
    }
    else
        $output ='<div class="error">403 - Нет доступа!</div>';
}
else if($url =='editPosts'){
    if (isset($_SESSION['user_id'])){
        $output = main();
        if (isset($_GET['id']))
            $output .= createOrUpdatePost($db, true);
        else
            $output .=getPostAll($db, true);
    }
    else
        $output = '<div class="error"> 403 - Нет доступа</div>';
}
else if ($url == 'firstPost'){
    $output = main();
    $output .= firstPost($db);
    $output .= createOrDeleteComment($db);
}
else if($url == 'deleteComment'){
    if(isset($_SESSION['user_id'])){
        $output = main();
        $output .= delete($db);
    }
}
else{
    $output = main();
    $output .=getPostAll($db);
}

?>
<!doctype html>
<html lang="RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Blog beta</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<?= $output ?>
</body>
</html>
