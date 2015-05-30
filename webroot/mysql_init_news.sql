USE yourdatabase;

SET NAMES 'utf8';
--
-- Create table for Content
--
DROP TABLE IF EXISTS Content;
CREATE TABLE Content
(
  id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  slug CHAR(80) UNIQUE,
  url CHAR(80) UNIQUE,
 
  TYPE CHAR(80),
  title VARCHAR(80),
  DATA TEXT,
  FILTER CHAR(80),
  category CHAR(20),
 
  published DATETIME,
  created DATETIME,
  updated DATETIME,
  deleted DATETIME
 
) ENGINE INNODB CHARACTER SET utf8;

