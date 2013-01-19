<?php
$TABLE[] = "CREATE TABLE iarcade_chal (
  chalid int(11) NOT NULL auto_increment,
  chalfrom_id int(11) NOT NULL,
  chalto_id int(11) NOT NULL,
  chalfrom_score int(11) NOT NULL,
  chalto_score int(11) NOT NULL,
  g1 int(11) NOT NULL,
  PRIMARY KEY  (`chalid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$TABLE[] = "CREATE TABLE iarcade_gamecats (
  catname text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$TABLE[] = "CREATE TABLE iarcade_games (
  id int(11) NOT NULL auto_increment,
  swf text NOT NULL,
  height text NOT NULL,
  width text NOT NULL,
  f1 int(11) NOT NULL,
  description text NOT NULL,
  name text NOT NULL,
  cat text NOT NULL,
  playcount int(9) NOT NULL,
  imgname text NOT NULL,
  gname text NOT NULL,
  savemethod text NOT NULL,
  UNIQUE KEY id (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$TABLE[] = "CREATE TABLE iarcade_scores (
  gname text NOT NULL,
  member text NOT NULL,
  score int(11) NOT NULL,
  time int(11) NOT NULL,
  ip int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$TABLE[] = "CREATE TABLE iarcade_comments (
  id int(9) NOT NULL auto_increment,
  username text NOT NULL,
  post text NOT NULL,
  time text NOT NULL,
  game text NOT NULL,
  ip int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$TABLE[] = "CREATE TABLE iarcade_favs (
  member text NOT NULL,
  gameid int(11) NOT NULL,
  gname text NOT NULL,
  name text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$TABLE[] = "CREATE TABLE iarcade_tars (
  tarfile_name varchar(50) NOT NULL,
  tarfile varchar(50) NOT NULL,
  added mediumint(1) NOT NULL,
  timestamp int(11) NOT NULL,
  UNIQUE KEY `tarfile_name` (`tarfile_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


?>