<?php
#Главная страница ресурса

function main(){
    return "
    <header>
        <div class='logo'>Мой личный блог печали</div>
        <div class='menu'>"
        . (!isset($_SESSION['user_id']) ?
            "<a href='?page=authorization'>Авторизация</a>
            <a href='?page=registration'>Регистрация</a> 
            <a href='?page=editAccount'>Редактировать</a>" :
            "<div class='name'>{$_SESSION['fullname']}</div>
            <a href='?page=addPost'>Создание поста</a>
            <a href='?page=editPosts'>Редактирование поста</a>
            <a href='?page=editAccount'>Редактировать аккаунт</a>
            <a href='?page=logout'>Выйти</a>") ."
        </div>
    </header>
    ";
}
?>