<?php
RelationshipManyToOne::$AlwaysMagic = true;

$currentUser = User::LoggedUser();
View::SetGlobal('currentUser', $currentUser);

//ScriptDependency::Current()->LoadData(GetProjectDirectory().'static/Dependency.json');

ScriptDependency::Current()->LoadData(GetProjectDirectory().'static/Dependency.json');