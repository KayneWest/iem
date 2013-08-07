---
--- Stuart Smith Park Hydrology Learning Lab
---
CREATE TABLE ss_bubbler(
  valid timestamptz,
  field varchar(32),
  value real,
  units varchar(32)
);
CREATE INDEX ss_bubbler_idx on ss_bubbler(valid);
GRANT SELECT on ss_bubbler to nobody,apache;

---
--- Stuart Smith Park Hydrology Learning Lab
---
CREATE TABLE ss_logger_data(
  id int,
  site_serial int,
  valid timestamptz,
  ch1_data_p real,
  ch2_data_p real,
  ch3_data_p real,
  ch4_data_p real,
  ch1_data_t real,
  ch2_data_t real,
  ch3_data_t real,
  ch4_data_t real,
  ch1_data_c real,
  ch2_data_c real,
  ch3_data_c real,
  ch4_data_c real
);
CREATE INDEX ss_logger_data_idx on ss_logger_data(valid);
GRANT SELECT on ss_logger_data to nobody,apache;

CREATE TABLE asi_data (
  station char(7),
  valid timestamp with time zone,
  ch1avg real,
  ch1sd  real,
  ch1max real,
  ch1min real,
  ch2avg real,
  ch2sd  real,
  ch2max real,
  ch2min real,
  ch3avg real,
  ch3sd  real,
  ch3max real,
  ch3min real,
  ch4avg real,
  ch4sd  real,
  ch4max real,
  ch4min real,
  ch5avg real,
  ch5sd  real,
  ch5max real,
  ch5min real,
  ch6avg real,
  ch6sd  real,
  ch6max real,
  ch6min real,
  ch7avg real,
  ch7sd  real,
  ch7max real,
  ch7min real,
  ch8avg real,
  ch8sd  real,
  ch8max real,
  ch8min real,
  ch9avg real,
  ch9sd  real,
  ch9max real,
  ch9min real,
  ch10avg real,
  ch10sd  real,
  ch10max real,
  ch10min real,
  ch11avg real,
  ch11sd  real,
  ch11max real,
  ch11min real,
  ch12avg real,
  ch12sd  real,
  ch12max real,
  ch12min real);
CREATE unique index asi_data_idx on asi_data(station, valid);
GRANT SELECT on asi_data to nobody;
GRANT SELECT on asi_data to apache;
  

CREATE TABLE alldata (
    station character varying(6),
    valid timestamp with time zone,
    tmpf real,
    dwpf real,
    drct real,
    sknt real,
    gust real,
    relh real,
    alti real,
    pcpncnt real,
    pday real,
    pmonth real,
    srad real,
    c1tmpf real
);

CREATE TABLE t2012 (
)
INHERITS (alldata);
