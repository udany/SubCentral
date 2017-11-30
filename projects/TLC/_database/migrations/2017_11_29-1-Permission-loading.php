<?php

$perms = [
    "admin_panel"=>"Access Admin Panel",
    "create_title"=>"Create Titles",
    "edit_title"=>"Edit Titles",
    "submit_artifact"=>"Submit Artifacts"
];

foreach ($perms as $slug=>$name){
    if (Permission::Count(['slug'=>$slug]) == 0){
        $item = new Permission();
        $item->Slug = $slug;
        $item->Name = $name;
        $item->Save();
    }
}