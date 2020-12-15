<?php
#Авторизация регистрация

#Table name
const TABLE_USER = PREFIX . 'users' . POSTFIX;

#Create table
$query = 'CREATE TABLE IF NOT EXISTS ' . TABLE_USER . '(
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `fullname` VARCHAR(256),
    `email` VARCHAR(130),
    `password` VARCHAR(40),
    `nickname` VARCHAR(64),
    `birthday` DATE
);';

$db->query($query)
or die('Error create table' . TABLE_USER);

function Registration($db){
    # сохранение данных
    $content = '';
    $error = '';
    $success = false;

    #очистка данных
    $fullname = '';
    $email = '';
    $nickname = '';
    $birthday = '';

    #Click button
    if(isset($_POST['send'])){
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rpassword = $_POST['rpassword'];
        $nickname = $_POST['nickname'];
        $birthday = $_POST['birthday'];

        if(isset($fullname) && isset($email) && isset($password) && isset($rpassword) && isset($nickname) && isset($birthday)){
            if(!empty($fullname) && !empty($email) && !empty($password) && !empty($rpassword) && !empty($nickname) && !empty($birthday)){
                #проверка на совпадение пароля
                if($password == $rpassword) {
                    #проверка что в пароле есть 1 заглавная
                    $lowchars = preg_match("@[A-Z]@", $password);
                    #проверка что в пароле есть 1 прописная
                    $highchars = preg_match("@[a-z]@", $password);
                    #проверка что в пароле есть 1 цифра
                    $numberchars = preg_match("@[0-9]@", $password);
                    #проверка на символы и цифры и длинну пароля
                    if ($lowchars && $highchars && $numberchars && strlen($password) >= 6 && strlen($password) <= 40) {
                        #Запрос на существования данных
                        $email = $db->real_escape_string(trim($email));
                        $query = "SELECT * FROM " . TABLE_USER . " WHERE `email` LIKE '$email'";
                        $result = $db->query($query) or die('Error select security account');
                        if ($result->num_rows == 0) {
                            $fullname = $db->real_escape_string(trim($fullname));
                            $password = $db->real_escape_string(trim($password));
                            $nickname = $db->real_escape_string(trim($nickname));
                            $birthday = $db->real_escape_string(trim($birthday));

                            #запрос на создание записи в таблице
                            $query = "INSERT INTO " . TABLE_USER . "(`fullname`, `email`, `password`, `nickname`, `birthday`)
                         VALUES('$fullname', '$email', SHA1('$password'), '$nickname', '$birthday')";

                            $db->query($query)
                            or die('Error insert table' . TABLE_USER . $query);
                            #операция успешно выполнена
                            $success = true;
                        } else $error = 'Ошибка! пользователь с таким email уже зарегистрирован!';
                    }
                    else $error = 'Ошибка! Пароль должен быть больше 6 и меньше 40 символов. 1 прописную, 1 заглавную букву и 1 цифру!';
                }
                else $error = 'Ошибка! Пароли не совпали!';
            }
            else $error = 'Ошибка! Не все данные заполненны!';
        }
        else $error = 'Ошибка! Перезагрузите страницу!';
    }
    if($success) {
        header("Refresh: 1; url=?");
        $content = "<div class='success'>Регистрация выполнена успешно. Вас перенаправят на главную страницу!</div>";
    }
    else $content = "<form action='' method='post' class='registration'>
                    <h2>Регистрация</h2>
                    <div class='error'>{$error}</div>
                    <input type='text' name='fullname' placeholder='Ваше ФИО:' value='{$fullname}' required>
                    <input type='email' name='email' placeholder='Email:' value='{$email}' required>
                    <input type='password' name='password' placeholder='Придумайте пароль: ' minlength=\"6\" maxlength=\"40\" required>
                    <input type='password' name='rpassword' placeholder='Повторите пароль:' required>
                    <input type='text' name='nickname' placeholder='Придумайте Ник:' value='{$nickname}' required>
                    <input type='date' name='birthday' value='{$birthday}' required>
                    <input type='submit' name='send' value='Зарегистрироваться'> 
                    <a href='?page=authorization'>Авторизироваться?</a>
                    <a href='?'>Главная страница</a>
            </form>";

    return $content;
}

function Authorization($db){
    # HTML FORM
    $content = '';
    $error = '';
    $success = '';

    if(isset($_POST['send'])){
        $email = $_POST['email'];
        $password = $_POST['password'];
        if(isset($email) && isset($password)) {
            if (!empty($email) && !empty($password)) {
                $email = $db->real_escape_string(trim($email));
                $password = $db->real_escape_string(trim($password));

                $query = "SELECT * FROM " . TABLE_USER . " WHERE `email` = '{$email}' AND `password` = SHA1('{$password}')";
                $result = $db->query($query)
                or die('Error select user table' . TABLE_USER);
                #Проверяем что записей в таблице 1 штука
                if($result->num_rows == 1){
                    $row = $result->fetch_array();
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['fullname'] = $row['fullname'];
                    $_SESSION['nickname'] = $row['nickname'];
                    $success = true;
                } else $error = 'Ошибка! Данные введены не верно!';
            } else $error = 'Ошибка! Не все данные заполненны!';
        } else $error = 'Ошибка! Перезагрузите страницу!';
    }
    if($success) {
        header("Refresh: 2; url=?");
        $content = '<div class="success">Вы успешно авторизировались!</div>';
    }
    else $content = "<form action='' method='post' class='authorization'>
                    <h2>Авторизация</h2>
                    <div class='error'>{$error}</div>
                    <input type='email' name='email' placeholder='Email/Login:' required>
                    <input type='password' name='password' placeholder='Введите пароль: ' required>
                    <input type='submit' name='send' value='Вход'> 
                    <a href='?page=registration'>Зарегистрироваться?</a>
                    <a href='?'>Главная страница</a>
            </form>";

    return $content;
}

function logout(){
    #Выход из сессии
    session_destroy();
    header("Refresh: 2; url=?");
    return  '<div class="success">Выход успешно выполнен!</div>';
}

#Функция редактирования аккаунта
function Update($db){
    $output ='';
    $success = false;
    $errorParams = '';
    $errorPassword = '';
    $query = "SELECT * FROM " . TABLE_USER . " WHERE `id` = '{$_SESSION['user_id']}'";
    $result = $db->query($query) or die('Error first account');

    if($result->num_rows != 1){
        session_destroy();
        header('Refresh: 2; url=?page=authorization');
        return '<div class="success">Ошибка! данные не найдены. Переавторизируйтесь!</div>';
    }
    $row = $result->fetch_array();
    $fullname = $row['fullname'];
    $nickname = $row['nickname'];
    $birthday = $row['birthday'];

    if(isset($_POST['save'])){
        $fullname = $_POST['fullname'];
        $nickname = $_POST['nickname'];
        $birthday = $_POST['birthday'];
        if(isset($fullname) && isset($nickname) && isset($birthday)){
            if(!empty($fullname) && !empty($nickname) && !empty($birthday)){
                $fullname = $db->real_escape_string(trim($fullname));
                $nickname = $db->real_escape_string(trim($nickname));
                $birthday = $db->real_escape_string(trim($birthday));

                $query = "UPDATE " . TABLE_USER . " SET `fullname` = '$fullname', `nickname` = '$nickname', `birthday` = '$birthday'
                    WHERE `id` = '{$_SESSION['user_id']}'";
                $db->query($query)
                or die('Error save account parameters');
                $success = true;
            }
            else $errorParams = 'Ошибка! Не все данные введены!';
        }
        else $errorParams = 'Ошибка! Перезагрузите страницу!';
    }
    if(isset($_POST['savePassword'])){
        $oldpassword = $_POST['oldpassword'];
        $password = $_POST['password'];
        $rpassword = $_POST['rpassword'];

        if(isset($oldpassword) && isset($rpassword) && isset($password)){
            if(!empty($oldpassword) && !empty($rpassword) && !empty($password)){
                if($password == $rpassword){
                    $oldpassword = $db->real_escape_string(trim($oldpassword));
                    $password = $db->real_escape_string(trim($password));


                    $query = "SELECT * FROM " . TABLE_USER .
                        " WHERE `id` = '{$_SESSION['user_id']}' AND `password` = SHA1('$oldpassword')";
                    $result = $db->query($query) or die('Error security password account');
                    if($result->num_rows == 1){
                        $query = "UPDATE " . TABLE_USER . " SET `password` = SHA1('$oldpassword') WHERE `id` = '{$_SESSION['user_id']}'";
                        $db->query($query)
                        or die('Error save operation password');
                        $success = true;
                    }
                }
                else $errorPassword = 'Ошибка! Пароли не совпали!';
            }
            else $errorPassword = 'Ошибка! Не все данные введены!';
        }
        else $errorPassword = 'Ошибка! Перезагрузите страницу!';
    }
    if($success){
        header('Refresh: 10; url=?page=editAccount');
        return '<div class="success">Операция выполнена успешно!</div>';
    }
    $output = "<form action='' method='post'>
        <h2>Изменение основных данных</h2>
        <div class='error'>{$errorParams}</div>
        <input type='text' name='fullname' required placeholder='Ваше ФИО' value='$fullname'> 
        <input type='text' name='nickname' required placeholder='Ваше ник' value='$nickname'> 
        <input type='date' name='birthday' required  value='$birthday'> 
        <input type='submit' name='save' value='Сохранить изменения'>
    </form>
    <form action='' method='post'>
        <h2>Изменение пароля</h2>
        <div class='error'>{$errorPassword}</div>
        <input type='password' name='oldpassword' required placeholder='Введите текщий пароль'>
        <input type='password' name='password' required placeholder='Введите новый пароль'>
        <input type='password' name='rpassword' required placeholder='Введите повторно новый пароль'>
        <input type='submit' name='savePassword' value='Сохранить изменения'>
    </form>";

    return $output;
}
?>