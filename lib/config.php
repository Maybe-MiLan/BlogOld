<?php

const HOST = 'localhost';
const USER = 'root';
const PASS = '';
const BASE = 'test';

const PREFIX = '`blog_';
const POSTFIX = '_900`';

$db = new mysqli(HOST, USER, PASS, BASE)
or die('Error connect db');
?>
