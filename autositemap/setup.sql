CREATE TABLE urls (
  `id` int(11) NOT NULL auto_increment,
  `hash` varchar(32) NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM;