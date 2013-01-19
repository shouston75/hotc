<?php
$TABLE[] = "CREATE TABLE iarcade_chal (
  chalid int NOT NULL IDENTITY,
  chalfrom_id int NOT NULL,
  chalto_id int NOT NULL,
  chalfrom_score int NOT NULL,
  chalto_score int NOT NULL,
  g1 int NOT NULL,
  PRIMARY KEY ( chalid )
);";

$TABLE[] = "CREATE TABLE iarcade_gamecats (
  catname varchar(max) NOT NULL
);";

$TABLE[] = "CREATE TABLE iarcade_games (
  id int NOT NULL IDENTITY,
  swf varchar(max) NOT NULL,
  height varchar(max) NOT NULL,
  width varchar(max) NOT NULL,
  f1 int NOT NULL,
  description varchar(max) NOT NULL,
  name varchar(max) NOT NULL,
  cat varchar(max) NULL,
  playcount int NOT NULL,
  imgname varchar(max) NOT NULL,
  gname varchar(max) NOT NULL,
  savemethod varchar(max) NOT NULL,
  PRIMARY KEY ( id )
);";

$TABLE[] = "CREATE TABLE iarcade_scores (
  gname varchar(max) NOT NULL,
  member varchar(max) NOT NULL,
  score int NOT NULL,
  time int NOT NULL,
  ip int NOT NULL
);";

$TABLE[] = "CREATE TABLE iarcade_comments (
  id int NOT NULL IDENTITY,
  username varchar(max) NOT NULL,
  post varchar(max) NOT NULL,
  [time] varchar(max) NOT NULL,
  game varchar(max) NOT NULL,
  ip int NOT NULL,
  PRIMARY KEY  ( id )
);";

$TABLE[] = "CREATE TABLE iarcade_favs (
  member varchar(max) NOT NULL,
  gameid int NOT NULL,
  gname varchar(max) NOT NULL,
  name varchar(max) NOT NULL
);";


$TABLE[] = "CREATE TABLE iarcade_tars (
  tarfile_name varchar(50) NOT NULL,
  tarfile varchar(50) NOT NULL,
  added int NOT NULL,
  [timestamp] int NOT NULL,
  UNIQUE ( tarfile_name )
);";


?>