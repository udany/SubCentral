<?php
/** @var Title $title */
if (isset($_GET['parent'])){
    $parent = (new Title($_GET['parent']))->Serialize();
}else{
    $parent = null;
}

View::SetGlobalVal('parent', $parent);
View::SetGlobalVal('pageTitle', 'New Title - ');