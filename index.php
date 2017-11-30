<?php
    require_once("setup.php");
    if (file_exists(GetProjectDirectory().'index.php')){
        include(GetProjectDirectory().'index.php');
        die(0);
    }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>[404] Ooooops...</title>
        <style type="text/css">
            *{
                font-family: arial, sans-serif;
            }
        </style>
    </head>
    <body>
        <div style="max-width: 640px; text-align: center; color: #700; background: #FDD; border: dashed #A00 1px; margin: 75px auto;">
           <h1 style="color: #900;">Oooops... Page not found...</h1>
           <span>Sadly we couldn't find the page you wanted to get to...<br />Maybe you can still find it if you go to the <a href="<?PHP echo SITE_URL; ?>">home page</a>, good luck!</span>
        </div>
    </body>
</html>