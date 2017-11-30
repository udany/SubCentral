<?php
Title::GetDataAccess()->CreateTable();
TitleReleaseDate::GetDataAccess()->CreateTable();

Title::GetDataAccess()->CreateConstraints();
TitleReleaseDate::GetDataAccess()->CreateConstraints();