<?php

/** @var Title $title */
$title = (new Title($_GET['id']))->LoadRelationships();

View::SetGlobalVal('title', $title);
View::SetGlobalVal('pageTitle', $title->Name . ' - ');