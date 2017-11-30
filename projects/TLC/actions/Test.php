<?php
/**
 * Created by PhpStorm.
 * User: andra
 * Date: 17/09/2017
 * Time: 20:57
 */

function Test() {
    $s = new Song();
    $s->Title = "I Will Always Love You";
    $s->Link = "https://www.youtube.com/watch?v=3JWTaaS7LdU";
    $s->Length = 274;

    $s->Artist = new Artist(1);
    $s->Genre = new Genre(5);
    $s->User = User::LoggedUser();

    $s->Save();

    return $s->Serialize();
}