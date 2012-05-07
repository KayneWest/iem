--- $ createdb iem
--- $ psql -f /usr/pgsql-9.0/share/contrib/postgis-1.5/postgis.sql iem
--- $ psql -f /usr/pgsql-9.0/share/contrib/postgis-1.5/spatial_ref_sys.sql iem

---
--- Quasi synced from mesosite database
---
CREATE TABLE stations(
	id varchar(20),
	synop int,
	name varchar(64),
	state char(2),
	country char(2),
	elevation real,
	network varchar(20),
	online boolean,
	params varchar(300),
	county varchar(50),
	plot_name varchar(64),
	climate_site varchar(6),
	remote_id int,
	wfo char(3),
	archive_begin timestamp with time zone,
	archive_end timestamp with time zone,
	tzname varchar(32),
	modified timestamp with time zone,
	iemid int PRIMARY KEY
	);
SELECT AddGeometryColumn('stations', 'geom', 4326, 'POINT', 2);
GRANT SELECT on stations to nobody,apache;


CREATE TABLE current (
    iemid int REFERENCES stations(iemid),
    tmpf real,
    dwpf real,
    drct real,
    sknt real,
    indoor_tmpf real,
    tsf0 real,
    tsf1 real,
    tsf2 real,
    tsf3 real,
    rwis_subf real,
    scond0 character varying,
    scond1 character varying,
    scond2 character varying,
    scond3 character varying,
    valid timestamp with time zone DEFAULT '1980-01-01 00:00:00-06'::timestamp with time zone,
    pday real,
    c1smv real,
    c2smv real,
    c3smv real,
    c4smv real,
    c5smv real,
    c1tmpf real,
    c2tmpf real,
    c3tmpf real,
    c4tmpf real,
    c5tmpf real,
    pres real,
    relh real,
    srad real,
    vsby real,
    phour real DEFAULT (-99),
    gust real,
    raw character varying(256),
    alti real,
    mslp real,
    qc_tmpf character(1),
    qc_dwpf character(1),
    rstage real,
    ozone real,
    co2 real,
    pmonth real,
    skyc1 character(3),
    skyc2 character(3),
    skyc3 character(3),
    skyl1 integer,
    skyl2 integer,
    skyl3 integer,
    skyc4 character(3),
    skyl4 integer,
    pcounter real,
    discharge real,
    p03i real,
    p06i real,
    p24i real,
    max_tmpf_6hr real,
    min_tmpf_6hr real,
    max_tmpf_24hr real,
    min_tmpf_24hr real
);
CREATE UNIQUE index current_iemid_idx on current(iemid);

CREATE TABLE current_log (
    iemid int REFERENCES stations(iemid),
    tmpf real,
    dwpf real,
    drct real,
    sknt real,
    indoor_tmpf real,
    tsf0 real,
    tsf1 real,
    tsf2 real,
    tsf3 real,
    rwis_subf real,
    scond0 character varying,
    scond1 character varying,
    scond2 character varying,
    scond3 character varying,
    valid timestamp with time zone DEFAULT '1980-01-01 00:00:00-06'::timestamp with time zone,
    pday real,
    c1smv real,
    c2smv real,
    c3smv real,
    c4smv real,
    c5smv real,
    c1tmpf real,
    c2tmpf real,
    c3tmpf real,
    c4tmpf real,
    c5tmpf real,
    pres real,
    relh real,
    srad real,
    vsby real,
    phour real DEFAULT (-99),
    gust real,
    raw character varying(256),
    alti real,
    mslp real,
    qc_tmpf character(1),
    qc_dwpf character(1),
    rstage real,
    ozone real,
    co2 real,
    pmonth real,
    skyc1 character(3),
    skyc2 character(3),
    skyc3 character(3),
    skyl1 integer,
    skyl2 integer,
    skyl3 integer,
    skyc4 character(3),
    skyl4 integer,
    pcounter real,
    discharge real,
    p03i real,
    p06i real,
    p24i real,
    max_tmpf_6hr real,
    min_tmpf_6hr real,
    max_tmpf_24hr real,
    min_tmpf_24hr real
);

CREATE OR REPLACE FUNCTION current_update_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
  BEGIN
   IF (NEW.valid != OLD.valid) THEN
     INSERT into current_log SELECT * from current WHERE iemid = NEW.iemid;
   END IF;
   RETURN NEW;
  END
 $$;

CREATE TRIGGER current_update_tigger AFTER UPDATE ON current 
FOR EACH ROW EXECUTE PROCEDURE current_update_log();


CREATE TABLE summary (
	iemid int REFERENCES stations(iemid),
    max_tmpf real,
    min_tmpf real,
    day date,
    max_sknt real,
    max_gust real,
    max_sknt_ts timestamp with time zone,
    max_gust_ts timestamp with time zone,
    max_dwpf real,
    min_dwpf real,
    pday real,
    pmonth real,
    snow real,
    snowd real,
    max_tmpf_qc character(1),
    min_tmpf_qc character(1),
    pday_qc character(1),
    snow_qc character(1),
    snoww real,
    max_drct real,
    max_srad smallint,
    coop_tmpf real,
    coop_valid timestamp with time zone
);

CREATE TABLE summary_2011() inherits (summary);

CREATE TABLE trend_15m(
	iemid int REFERENCES stations(iemid),
	updated timestamp with time zone,
	alti_15m real
);
GRANT SELECT on trend_15m to nobody,apache;

CREATE TABLE trend_1h(
	iemid int REFERENCES stations(iemid),
	updated timestamp with time zone,
	alti_1h real
);
GRANT SELECT on trend_1h to nobody,apache;

CREATE TABLE rwis_locations(
  id smallint UNIQUE,
  nwsli char(5)
);

--
-- RWIS Deep Soil Probe Data
--
CREATE TABLE rwis_soil_data(
  location_id smallint references rwis_locations(id),
  sensor_id smallint,
  valid timestamp with time zone,
  temp real,
  moisture real
);
CREATE TABLE rwis_soil_data_log(
  location_id smallint references rwis_locations(id),
  sensor_id smallint,
  valid timestamp with time zone,
  temp real,
  moisture real
);

GRANT select on rwis_soil_data to apache,nobody;

CREATE FUNCTION rwis_soil_update_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
  BEGIN
   IF (NEW.valid != OLD.valid) THEN
     INSERT into rwis_soil_data_log 
        SELECT * from rwis_soil_data WHERE sensor_id = NEW.sensor_id
        and location_id = NEW.location_id;
   END IF;
   RETURN NEW;
  END
 $$;

CREATE TRIGGER rwis_soil_update_tigger
    AFTER UPDATE ON rwis_soil_data
    FOR EACH ROW
    EXECUTE PROCEDURE rwis_soil_update_log();

--
-- RWIS Traffic Data Storage
-- 
CREATE TABLE rwis_traffic_sensors(
  id SERIAL UNIQUE,
  location_id smallint references rwis_locations(id),
  lane_id smallint,
  name varchar(64)
);

CREATE OR REPLACE view rwis_traffic_meta AS 
  SELECT l.id as location_id, l.nwsli as nwsli, s.id as sensor_id,
  s.lane_id as lane_id
  FROM rwis_locations l, rwis_traffic_sensors s WHERE
  l.id = s.location_id;


CREATE TABLE rwis_traffic_data(
  sensor_id int references rwis_traffic_sensors(id),
  valid timestamp with time zone,
  avg_speed real,
  avg_headway real,
  normal_vol real,
  long_vol real,
  occupancy real
);

CREATE TABLE rwis_traffic_data_log(
  sensor_id int references rwis_traffic_sensors(id),
  valid timestamp with time zone,
  avg_speed real,
  avg_headway real,
  normal_vol real,
  long_vol real,
  occupancy real
);

CREATE FUNCTION rwis_traffic_update_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
  BEGIN
   IF (NEW.valid != OLD.valid) THEN
     INSERT into rwis_traffic_data_log 
        SELECT * from rwis_traffic_data WHERE sensor_id = NEW.sensor_id;
   END IF;
   RETURN NEW;
  END
 $$;

CREATE TRIGGER rwis_traffic_update_tigger
    AFTER UPDATE ON rwis_traffic_data
    FOR EACH ROW
    EXECUTE PROCEDURE rwis_traffic_update_log();


CREATE VIEW rwis_traffic AS 
  SELECT * from 
  rwis_traffic_sensors s, rwis_traffic_data d
  WHERE d.sensor_id = s.id;

GRANT SELECT on rwis_traffic_data to apache,nobody;
GRANT SELECT on rwis_traffic_data_log to apache,nobody;
GRANT SELECT on rwis_traffic_sensors to apache,nobody;
GRANT SELECT on rwis_traffic to apache,nobody;

INSERT into rwis_locations values (58, 'RPFI4');
INSERT into rwis_locations values (30, 'RMCI4');
INSERT into rwis_locations values (54, 'RSYI4');
INSERT into rwis_locations values (42, 'RSPI4');
INSERT into rwis_locations values (48, 'RWBI4');
INSERT into rwis_locations values (22, 'RGRI4');
INSERT into rwis_locations values (45, 'RURI4');
INSERT into rwis_locations values (43, 'RSLI4');
INSERT into rwis_locations values (60, 'RDNI4');
INSERT into rwis_locations values (61, 'RQCI4');
INSERT into rwis_locations values (57, 'RTMI4');
INSERT into rwis_locations values (49, 'RHAI4');
INSERT into rwis_locations values (52, 'RCRI4');
INSERT into rwis_locations values (53, 'RCFI4');
INSERT into rwis_locations values (02, 'RTNI4');
INSERT into rwis_locations values (03, 'RTOI4');
INSERT into rwis_locations values (00, 'RDAI4');
INSERT into rwis_locations values (01, 'RALI4');
INSERT into rwis_locations values (06, 'RAVI4');
INSERT into rwis_locations values (07, 'RBUI4');
INSERT into rwis_locations values (04, 'RAMI4');
INSERT into rwis_locations values (05, 'RAKI4');
INSERT into rwis_locations values (46, 'RWLI4');
INSERT into rwis_locations values (47, 'RWII4');
INSERT into rwis_locations values (08, 'RCAI4');
INSERT into rwis_locations values (09, 'RCDI4');
INSERT into rwis_locations values (28, 'RMQI4');
INSERT into rwis_locations values (29, 'RMTI4');
INSERT into rwis_locations values (40, 'RSGI4');
INSERT into rwis_locations values (41, 'RSCI4');
INSERT into rwis_locations values (59, 'RCTI4');
INSERT into rwis_locations values (51, 'RIGI4');
INSERT into rwis_locations values (24, 'RIOI4');
INSERT into rwis_locations values (56, 'RDYI4');
INSERT into rwis_locations values (25, 'RJFI4');
INSERT into rwis_locations values (39, 'RSDI4');
INSERT into rwis_locations values (26, 'RLEI4');
INSERT into rwis_locations values (27, 'RMNI4');
INSERT into rwis_locations values (20, 'RDBI4');
INSERT into rwis_locations values (38, 'RROI4');
INSERT into rwis_locations values (21, 'RFDI4');
INSERT into rwis_locations values (11, 'RCNI4');
INSERT into rwis_locations values (10, 'RCII4');
INSERT into rwis_locations values (13, 'RCEI4');
INSERT into rwis_locations values (12, 'RCBI4');
INSERT into rwis_locations values (15, 'RDCI4');
INSERT into rwis_locations values (14, 'RDVI4');
INSERT into rwis_locations values (17, 'RDMI4');
INSERT into rwis_locations values (16, 'RDSI4');
INSERT into rwis_locations values (19, 'RDWI4');
INSERT into rwis_locations values (18, 'RDEI4');
INSERT into rwis_locations values (31, 'RMVI4');
INSERT into rwis_locations values (23, 'RIAI4');
INSERT into rwis_locations values (37, 'RPLI4');
INSERT into rwis_locations values (36, 'ROTI4');
INSERT into rwis_locations values (35, 'ROSI4');
INSERT into rwis_locations values (34, 'RONI4');
INSERT into rwis_locations values (33, 'RNHI4');
INSERT into rwis_locations values (55, 'RBFI4');
INSERT into rwis_locations values (32, 'RMPI4');
INSERT into rwis_locations values (44, 'RTPI4');
INSERT into rwis_locations values (50, 'RSBI4');
