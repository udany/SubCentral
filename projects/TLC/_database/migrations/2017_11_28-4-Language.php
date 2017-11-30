<?php

Language::GetDataAccess()->CreateTable(true);
UserLanguage::GetDataAccess()->CreateTable()->CreateConstraints();

$items = [
    "ENUS"=>"English, American",
    "ENUK"=>"English, British",
    "JP"=>"Japaneese",
    "PTBR"=>"Brazilian Portuguese",
];

foreach ($items as $key=>$name){
    $item = new Language();
    $item->Acronym = $key;
    $item->Name = $name;
    $item->Save();
}