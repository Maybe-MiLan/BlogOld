<?php

const  TABLE_COMMENT = PREFIX . 'comment' . POSTFIX;

$query = 'CREATE TABLE IF NOT EXISTS ' . TABLE_COMMENT .'(
            `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
            `user_id` INT(11),
            `post_id` INT(11),
            `comment` VARCHAR(512),
            FOREIGN KEY(`user_id`) REFERENCES ' . TABLE_USER . ' (`id`) ON DELETE CASCADE,
            FOREIGN KEY(`post_id`) REFERENCES ' . TABLE_POST . ' (`id`) ON DELETE CASCADE
);';

$db->query($query)
    or die("Ошибка при создании таблицы = " . TABLE_COMMENT);


function createOrDeleteComment($db){
    #Проверка на авторизацию пользователя
    $isUser = (isset($_SESSION['user_id']) ? true : false);

    #Проверка на существование id
    if(!isset($_GET['id']))
        return "<div class='error'>Не передан идентификатор поста</div>";
    $idPost = $db->real_escape_string(trim($_GET['id']));

    if (isset($_POST['sendComment']) && $isUser){
        if (isset($_POST['comment'])){
            if (!empty($_POST['comment'])){
                $comment = $db->real_escape_string(trim($_POST['comment']));

                $query = 'INSERT INTO ' . TABLE_COMMENT . '(`user_id`, `post_id`, `comment`)' .
                    " VALUES('{$_SESSION['user_id']}', '$idPost', '$comment')";
                $db->query($query) or die ('Error create new comment');
            }
        }
    }

    #Выгрузка коментарие
    $query = "SELECT `t1` . `id` as `id_comment`,
              `user` . `fullname` , 
              `user` . `id` as `user_id`,
              `comment` FROM " . TABLE_COMMENT . " as `t1`
            LEFT JOIN " . TABLE_USER . " as `user` ON `user_id` = `user` . `id` AND `post_id` = '$idPost' ORDER BY `id_comment` DESC";
    $result = $db->query($query)
        or die('Error select table LEFT JOIN');

    $comment = '';

    if($isUser)
        $comment .= "<form action='' method='post'>
                    <h2>Новый коментарий</h2>
                    <textarea name='comment' maxlength='512' placeholder='Ваш коментарий' required></textarea>
                    <input type='submit' name='sendComment' value='Создать комментарий'>
                    </form>";
    else
        $comment .= "<div class='warning'>Для того чтобы оставить комментарий
                     <a href='?page=authorization'>Авторизируйтесь</a>
                     <a href='?page=registration'>Зарегистрируйтесь</a>
                     </div>";
    while($row = $result->fetch_array())
        if($isUser){
            $comment .= "<div class='comment'>
            <div class='name'>{$row['fullname']}</div>
            <div class='textcomment'>{$row['comment']}</div>";
            if($row['user_id'] == $_SESSION['user_id'])
                $comment .= "<a href='?page=deleteComment&id={$row['id_comment']}'>Удалить коментарий</a>";
            $comment .= "</div>";
        }



    return $comment;
}
function delete($db)
{
    if (!isset($_GET['id']))
        return "<div class='error'>Не передан идентификатор комментария</div>";
    $idComment = $db->real_escape_string(trim($_GET['id']));

    $query = "DELETE FROM " . TABLE_COMMENT . " WHERE `id` = '$idComment' AND `user_id` = '{$_SESSION['user_id']}'";
    $db->query($query) or die('Error delete comment');
    header('Refresh: 2; url=?');
    return '<div class="success">Коментарий удален</div>';
}
?>