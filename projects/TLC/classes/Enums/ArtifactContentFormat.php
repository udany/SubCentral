<?php
class ArtifactContentFormat extends BaseEnum{
    const Txt = 1;
    const Srt = 2;
    const Ass = 2;

    public static $ext = [
        '',
        'txt',
        'srt',
        'ass'
    ];
}