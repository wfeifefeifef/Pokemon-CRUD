BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "personajes" (
	"id"	INTEGER,
	"nombre"	VARCHAR(100) NOT NULL,
	"color"	VARCHAR(50),
	"tipo"	VARCHAR(50),
	"nivel"	INTEGER,
	"foto"	VARCHAR(255),
	PRIMARY KEY("id" AUTOINCREMENT)
);
INSERT INTO "personajes" VALUES (19,'Bulbasaur','Verde','Veneno',3245,'uploads/4c6b5ccf12b623213c9a6b49712ca068.png');
INSERT INTO "personajes" VALUES (20,'Charmander','Rojo','Fuego',1987,'uploads/77061476a445faac563ceef4418d5479.png');
INSERT INTO "personajes" VALUES (21,'Squirtle','Azul','Agua',4560,'uploads/25283490ddd13052a8e1d2450ba44b2b.png');
INSERT INTO "personajes" VALUES (22,'Pikachu','Amarillo','Eléctrico',2134,'uploads/8c146f3451790c5a140bd6731acc3c6b.png');
INSERT INTO "personajes" VALUES (23,'Jigglypuff','Rosa','Hada',3876,'uploads/970960f1fec848e83e4b326075487a2c.png');
INSERT INTO "personajes" VALUES (24,'Meowth','Amarillo','Normal',1654,'uploads/7e29cf631c278519f89f48ba62ca2be2.png');
INSERT INTO "personajes" VALUES (25,'Magmar','Rojo','Fuego',2678,'uploads/54818e56f904f0ccb7316565c8111027.png');
INSERT INTO "personajes" VALUES (26,'Mew','Rosa','Hada',3693,'uploads/57d952ed882adf986eaacb30318e26bf.png');
INSERT INTO "personajes" VALUES (29,'Keury Ramirez','Azul','Eléctrico',3693,'uploads/3569ca3f09000dd37937f19e582ff61c.jpg');
COMMIT;
