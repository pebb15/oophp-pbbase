USE yourdatabase;

SET NAMES 'utf8';

-- 
-- Drop all tables in the right order. 
-- 
DROP VIEW IF EXISTS VMovie;
DROP TABLE IF EXISTS Movie2Genre; 
DROP TABLE IF EXISTS Genre; 
DROP TABLE IF EXISTS Movie; 

--
-- Skapa en tabell för filmer
--
CREATE TABLE Movie
(
  id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  title VARCHAR(100) NOT NULL,
  director VARCHAR(100),
  length INT DEFAULT NULL, -- Filmlängd i minuter
  year INT NOT NULL DEFAULT 1900,
  plot TEXT, -- Kort filmintro
  image VARCHAR(100) DEFAULT NULL, -- Länk till en bild
  subtext CHAR(3) DEFAULT NULL, -- språk på subtext, swe, en, ...
  speech CHAR(3) DEFAULT NULL, -- talspråk, swe, en, ...
  quality CHAR(3) DEFAULT NULL,
  format CHAR(3) DEFAULT NULL, -- mp4, divx, ...
  link_imdb varchar(200) NOT NULL,
  trailer varchar(100) NOT NULL,
  price INT NOT NULL DEFAULT 0
) ENGINE INNODB CHARACTER SET utf8;
 

 
CREATE TABLE Movie2Genre
(
  idMovie INT NOT NULL,
  idGenre INT NOT NULL,
 
  FOREIGN KEY (idMovie) REFERENCES Movie (id),
  FOREIGN KEY (idGenre) REFERENCES Genre (id),
 
  PRIMARY KEY (idMovie, idGenre)
) ENGINE INNODB;
 
 

 
CREATE VIEW VMovie
AS
SELECT 
  M.*,
  GROUP_CONCAT(G.name) AS genre
FROM Movie AS M
  LEFT OUTER JOIN Movie2Genre AS M2G
    ON M.id = M2G.idMovie
  LEFT OUTER JOIN Genre AS G
    ON M2G.idGenre = G.id
GROUP BY M.id
;
 
-- SELECT * FROM VMovie;

