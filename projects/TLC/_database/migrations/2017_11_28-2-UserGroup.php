<?php

UserGroup::GetDataAccess()->CreateTable(true);
UserGroupUser::GetDataAccess()->CreateTable()->CreateConstraints();

$groups = ["Admin", "Moderator", "Contributor"];

foreach ($groups as $name){
    $group = new UserGroup();
    $group->Name = $name;
    $group->Save();
}