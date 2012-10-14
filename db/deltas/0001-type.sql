alter table Anturit add column type varchar(20);
update Anturit set type = 'temperature';
alter table Mittaukset modify column Lampotila decimal(7,2);
alter table Anturit add column unit varchar(20);
update Anturit set unit = '°C';
