<?php

Group::GetDataAccess()->CreateTable(true);
GroupMember::GetDataAccess()->CreateTable()->CreateConstraints();