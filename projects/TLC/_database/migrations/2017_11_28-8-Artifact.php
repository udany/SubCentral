<?php
Artifact::GetDataAccess()->CreateTable();
ArtifactGroup::GetDataAccess()->CreateTable();
ArtifactUser::GetDataAccess()->CreateTable();
ArtifactRating::GetDataAccess()->CreateTable();
ArtifactCorrection::GetDataAccess()->CreateTable();

Artifact::GetDataAccess()->CreateConstraints();
ArtifactGroup::GetDataAccess()->CreateConstraints();
ArtifactUser::GetDataAccess()->CreateConstraints();
ArtifactRating::GetDataAccess()->CreateConstraints();
ArtifactCorrection::GetDataAccess()->CreateConstraints();