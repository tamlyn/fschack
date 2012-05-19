create table sites (
	id int auto_increment, 
	lat float,
	lon float,
	title varchar(255),
	centre varchar(255),
	unique index(id),
	index(lat),
	index(lon),
	index(site)
)

create table siteAlias(
	site_id int,
	alias varchar(255)
)

create table team(
	id int auto_increment,
	startdate date,
	schoolName varchar(255),
	unique index(id)
)

create table siteInvestigations(
	id int auto_increment, 
	siteId int,
	groupId int,
	timestamp timestamp DEFAULT CURRENT_TIMESTAMP,
	unique index(id),
	index(siteId)
)

create table measurements(
	id int auto_increment,
	siteInvestigationId int,
	type varchar(255),
	investigationSeriesIndex int,
	value float,
	unique index(id),
	index (investigationSeriesIndex),
	index (type),
	index (value)	
)