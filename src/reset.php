<?php
session_start();

$_SESSION['guesses'] = [];
unset($_SESSION['found']);

header('Location: /');
