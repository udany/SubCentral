<?php

Permission::GetDataAccess()->CreateTable(true);
UserGroupPermission::GetDataAccess()->CreateTable()->CreateConstraints();