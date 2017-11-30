<?php
if (!User::LoggedUser()->HasPermission("admin_panel")) {
    die();
}

View::SetGlobalVal('pageTitle', 'Admin - ');