<?php
#константа таблиц
const  TABLE_POST = PREFIX . 'post' . POSTFIX;

$query = 'CREATE TABLE IF NOT EXISTS ' . TABLE_POST .'(
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT(11),
    `subject` VARCHAR(128),
    `tags` VARCHAR(512),
    `description` VARCHAR(1024),
    `text` TEXT,
    `datetime` DATETIME,
    FOREIGN KEY(`user_id`) REFERENCES ' . TABLE_USER . ' (`id`) ON DELETE CASCADE
    );';

$db->query($query)
or die("Ошибка при создании таблицы = " . TABLE_POST);

# выгрузка всех публикаций
function getPostAll($db, $user = false){
    # условие создания автоматического условия
    $where = ($user ? " WHERE `user_id` = '{$_SESSION['user_id']}'" : '');

    # создание пагинации
    $query = 'SELECT COUNT(*) AS col  FROM ' . TABLE_POST;
    $result = $db->query($query) or die('Error select count ' . TABLE_POST);
    $row = $result->fetch_array();
    if($row['col'] == 0)
        return "<div class='notPosts'>Публикации не найдены</div>";
    $firstCount = 5;
    $count = (int) $row['col'] / $firstCount;
    $number = (int) $db->real_escape_string(trim($_GET['number']));
    $number = $_GET['number']; // номер страницы
    $number = ($number > $count ? $count : ($number < 0 ? 0 : $number));

    $countPage = $firstCount * $number;

    # запрос на получение данных
    $query = 'SELECT * FROM ' . TABLE_POST . $where . " LIMIT $countPage, $firstCount";

    $result = $db->query($query) or die('Error getting list of posts');
    $content = '';
    while($row = $result->fetch_array())
        $content .= "
        <div class='allPost'>
                <a href='?page=firstPost&id={$row['id']}'<h2>{$row['subject']}</h2></a>
            <div class='description'>{$row['description']}</div>
            <div class='tags'>{$row['tags']}</div>
            <div class='datetime'>{$row['datetime']}</div> "
            . (empty($where) ? '' : "<a href='?page=editPosts&id={$row['id']}'>Редактировать пост</a>") .
            "</div>";
    $pagination = '';
    for($i = 0; $i <= $count; $i++)
        if($i !=$number)
            $pagination .= "<a href='?number={$i}' class='button'>{$i}</a>";
        else
            $pagination .= "<div class='button selected'>{$i}</a>";

    $content = "<div class='posts'>$content</div>";
    $pagination = "<div class='pagination'>$pagination</div>";

    return $content . $pagination;
}

function createOrUpdatePost($db, $update = false) {
    # основные переменные для работы
    $header = ($update ? 'Обновление записи' : 'Создание записи');
    $button = ($update ? 'Сохранить изменения' : 'Опубликовать');
    $id = '';

    # переменные для вывода в html - форму
    $subject = '';
    $tags = '';
    $description = '';
    $text = '';

    $error = '';

    if($update){
        if(!isset($_GET['id'])) return "<div class='error'>Не передан идентификатор публикации</div>";

        # проверка идентификатора на sql - инъекцию
        $id = $db->real_escape_string(trim($_GET['id']));

        # запрос на получение данных о данной публикации
        $query = 'SELECT * FROM ' . TABLE_POST . " WHERE `id` = '{$id}' AND `user_id` = '{$_SESSION['user_id']}'";
        $result = $db->query($query) or die('Ошибка при выводе одной публикации');

        if($result->num_rows != 1) return "<div class='error'>Данная публикация не существует, либо у вас нет прав для ее редактирования</div>";

        # выгрузка данных в переменную row
        $row = $result->fetch_array();
        $subject = $row['subject'];
        $tags = $row['tags'];
        $description = $row['description'];
        $text = $row['text'];
    }

    # проверка на нажатие кнопки сохранения данных - send
    if(isset($_POST['send'])){
        $subject = $_POST['subject'];
        $tags = $_POST['tags'];
        $description = $_POST['description'];
        $text = $_POST['text'];

        # проверка на сущ. данных
        if(isset($subject) && isset($tags) && isset($description) && isset($text)){

            # проверка на заполненность данных
            if(!(empty($subject) && empty($tags) && empty($description) && empty($text))){

                # проверка данных на sql - инъекцию
                $subject = $db->real_escape_string(trim($subject));
                $tags = $db->real_escape_string(trim($tags));
                $description = $db->real_escape_string(trim($description));
                $text = $db->real_escape_string(trim($text));

                # проверка условия об операции
                if($update) $query = ' UPDATE '. TABLE_POST . " SET `subject` = '$subject', `tags` = '$tags', `description` = '$description', `text` = '$text' WHERE `id` = '{$id}'";
                else $query = 'INSERT INTO ' . TABLE_POST . "(`subject`,`tags`,`description`,`text`,`datetime`,`user_id`) VALUES ('{$subject}','{$tags}','{$description}','{$text}', NOW(), '{$_SESSION['user_id']}')";
                $db->query($query) or die ('Error insert table');
                header('Refresh 2; url=?');
                return "<div class='success'>Публикация успешно " . ($update ? 'обновлена' : 'создана') . "</div>";
            } else $error = 'Возникла ошибка: не все данные заполнены';
        } else $error = 'Возникла ошибка: перезагрузите страницу';
    }

    return "<form action='' method='post' class='crudPost'>
                        <h2>$header</h2>
                        <div class='error'>{$error}</div>
                    <input type='text' name='subject' maxlength='128' required placeholder='Тема публикации' value='{$subject}'/>
                    <input type='text' name='tags' maxlength='128' required placeholder='Теги' value='{$tags}'/>
                    <textarea required name='description' placeholder='Описание' maxlength='1024'>{$description}</textarea>                             
                    <textarea required name='text' placeholder='Полное описание'>{$text}</textarea>
                    <input type='submit' name='send' value='{$button}'/>                             
            </form>";
}

function firstPost($db) {
    if(!isset($_GET['id'])) return "<div class='error'>Не передан идентификатор поста</div>";
    header("Refresh: 2; url=?");
    $id = $db->real_escape_string(trim($_GET['id']));

    # запрос на получение данных из таблицы
    $query = 'SELECT * FROM ' . TABLE_POST . " WHERE `id` = '$id'";
    $result = $db->query($query) or die ('Ошибка при получении данных из таблицы');
    if($result->num_rows != 1)
        return '<div class="error404">404: публикация не обнаружена</div>';

    # получение массива данных из таблицы
    $row = $result->fetch_array();

    # получение информации об авторе публикации
    $userQuery = 'SELECT `fullname` FROM ' . TABLE_USER . " WHERE `id` = '{$row['user_id']}'";
    $userResult = $db->query($userQuery) or die ('Ошибка при получении данных об авторе публикации');
    $user = $userResult->fetch_array();


    return "<div class='firstPost'>
                    <h1>{$row['subject']}</h1>
                <div class='text'>{$row['text']}</div>
                <div class='information'>
                    <div class='tags'>{$row['tags']}</div>
                    <div class='datetime'>{$row['datetime']}</div>
                    <div class='fullname'>{$row['fullname']}</div>
                </div>                                
            </div>";
}
?>