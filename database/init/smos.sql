CREATE EXTENSION postgis;

---
--- Store grid point geometries
---
CREATE TABLE grid(
  idx int UNIQUE,
  gridx int,
  gridy int
  );
 
 SELECT AddGeometryColumn('grid', 'geom', 4326, 'POINT', 2);
 CREATE index grid_idx on grid(idx);
 GRANT SELECT on grid to apache,nobody;
 
 ---
 --- Lookup table of observation events
 ---
 CREATE TABLE obtimes(
   valid timestamp with time zone UNIQUE
 );
 GRANT SELECT on obtimes to apache,nobody;
 
 ---
 --- Store the actual data, will have partitioned tables
 --- 
 CREATE TABLE data(
   grid_idx int REFERENCES grid(idx),
   valid timestamp with time zone,
   soil_moisture real,
   optical_depth real
 );
 GRANT SELECT on data to apache,nobody;
 
 create table data_2010_01( 
  CONSTRAINT __data_2010_01_check 
  CHECK(valid >= '2010-01-01 00:00+00'::timestamptz 
        and valid < '2010-02-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_01_grid_idx on data_2010_01(grid_idx);
CREATE INDEX data_2010_01_valid_idx on data_2010_01(valid);
GRANT SELECT on data_2010_01 to nobody,apache;


 create table data_2010_02( 
  CONSTRAINT __data_2010_02_check 
  CHECK(valid >= '2010-02-01 00:00+00'::timestamptz 
        and valid < '2010-03-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_02_grid_idx on data_2010_02(grid_idx);
CREATE INDEX data_2010_02_valid_idx on data_2010_02(valid);
GRANT SELECT on data_2010_02 to nobody,apache;


 create table data_2010_03( 
  CONSTRAINT __data_2010_03_check 
  CHECK(valid >= '2010-03-01 00:00+00'::timestamptz 
        and valid < '2010-04-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_03_grid_idx on data_2010_03(grid_idx);
CREATE INDEX data_2010_03_valid_idx on data_2010_03(valid);
GRANT SELECT on data_2010_03 to nobody,apache;


 create table data_2010_04( 
  CONSTRAINT __data_2010_04_check 
  CHECK(valid >= '2010-04-01 00:00+00'::timestamptz 
        and valid < '2010-05-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_04_grid_idx on data_2010_04(grid_idx);
CREATE INDEX data_2010_04_valid_idx on data_2010_04(valid);
GRANT SELECT on data_2010_04 to nobody,apache;


 create table data_2010_05( 
  CONSTRAINT __data_2010_05_check 
  CHECK(valid >= '2010-05-01 00:00+00'::timestamptz 
        and valid < '2010-06-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_05_grid_idx on data_2010_05(grid_idx);
CREATE INDEX data_2010_05_valid_idx on data_2010_05(valid);
GRANT SELECT on data_2010_05 to nobody,apache;


 create table data_2010_06( 
  CONSTRAINT __data_2010_06_check 
  CHECK(valid >= '2010-06-01 00:00+00'::timestamptz 
        and valid < '2010-07-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_06_grid_idx on data_2010_06(grid_idx);
CREATE INDEX data_2010_06_valid_idx on data_2010_06(valid);
GRANT SELECT on data_2010_06 to nobody,apache;


 create table data_2010_07( 
  CONSTRAINT __data_2010_07_check 
  CHECK(valid >= '2010-07-01 00:00+00'::timestamptz 
        and valid < '2010-08-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_07_grid_idx on data_2010_07(grid_idx);
CREATE INDEX data_2010_07_valid_idx on data_2010_07(valid);
GRANT SELECT on data_2010_07 to nobody,apache;


 create table data_2010_08( 
  CONSTRAINT __data_2010_08_check 
  CHECK(valid >= '2010-08-01 00:00+00'::timestamptz 
        and valid < '2010-09-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_08_grid_idx on data_2010_08(grid_idx);
CREATE INDEX data_2010_08_valid_idx on data_2010_08(valid);
GRANT SELECT on data_2010_08 to nobody,apache;


 create table data_2010_09( 
  CONSTRAINT __data_2010_09_check 
  CHECK(valid >= '2010-09-01 00:00+00'::timestamptz 
        and valid < '2010-10-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_09_grid_idx on data_2010_09(grid_idx);
CREATE INDEX data_2010_09_valid_idx on data_2010_09(valid);
GRANT SELECT on data_2010_09 to nobody,apache;


 create table data_2010_10( 
  CONSTRAINT __data_2010_10_check 
  CHECK(valid >= '2010-10-01 00:00+00'::timestamptz 
        and valid < '2010-11-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_10_grid_idx on data_2010_10(grid_idx);
CREATE INDEX data_2010_10_valid_idx on data_2010_10(valid);
GRANT SELECT on data_2010_10 to nobody,apache;


 create table data_2010_11( 
  CONSTRAINT __data_2010_11_check 
  CHECK(valid >= '2010-11-01 00:00+00'::timestamptz 
        and valid < '2010-12-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_11_grid_idx on data_2010_11(grid_idx);
CREATE INDEX data_2010_11_valid_idx on data_2010_11(valid);
GRANT SELECT on data_2010_11 to nobody,apache;


 create table data_2010_12( 
  CONSTRAINT __data_2010_12_check 
  CHECK(valid >= '2010-12-01 00:00+00'::timestamptz 
        and valid < '2011-01-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2010_12_grid_idx on data_2010_12(grid_idx);
CREATE INDEX data_2010_12_valid_idx on data_2010_12(valid);
GRANT SELECT on data_2010_12 to nobody,apache;


 create table data_2011_01( 
  CONSTRAINT __data_2011_01_check 
  CHECK(valid >= '2011-01-01 00:00+00'::timestamptz 
        and valid < '2011-02-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_01_grid_idx on data_2011_01(grid_idx);
CREATE INDEX data_2011_01_valid_idx on data_2011_01(valid);
GRANT SELECT on data_2011_01 to nobody,apache;


 create table data_2011_02( 
  CONSTRAINT __data_2011_02_check 
  CHECK(valid >= '2011-02-01 00:00+00'::timestamptz 
        and valid < '2011-03-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_02_grid_idx on data_2011_02(grid_idx);
CREATE INDEX data_2011_02_valid_idx on data_2011_02(valid);
GRANT SELECT on data_2011_02 to nobody,apache;


 create table data_2011_03( 
  CONSTRAINT __data_2011_03_check 
  CHECK(valid >= '2011-03-01 00:00+00'::timestamptz 
        and valid < '2011-04-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_03_grid_idx on data_2011_03(grid_idx);
CREATE INDEX data_2011_03_valid_idx on data_2011_03(valid);
GRANT SELECT on data_2011_03 to nobody,apache;


 create table data_2011_04( 
  CONSTRAINT __data_2011_04_check 
  CHECK(valid >= '2011-04-01 00:00+00'::timestamptz 
        and valid < '2011-05-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_04_grid_idx on data_2011_04(grid_idx);
CREATE INDEX data_2011_04_valid_idx on data_2011_04(valid);
GRANT SELECT on data_2011_04 to nobody,apache;


 create table data_2011_05( 
  CONSTRAINT __data_2011_05_check 
  CHECK(valid >= '2011-05-01 00:00+00'::timestamptz 
        and valid < '2011-06-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_05_grid_idx on data_2011_05(grid_idx);
CREATE INDEX data_2011_05_valid_idx on data_2011_05(valid);
GRANT SELECT on data_2011_05 to nobody,apache;


 create table data_2011_06( 
  CONSTRAINT __data_2011_06_check 
  CHECK(valid >= '2011-06-01 00:00+00'::timestamptz 
        and valid < '2011-07-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_06_grid_idx on data_2011_06(grid_idx);
CREATE INDEX data_2011_06_valid_idx on data_2011_06(valid);
GRANT SELECT on data_2011_06 to nobody,apache;


 create table data_2011_07( 
  CONSTRAINT __data_2011_07_check 
  CHECK(valid >= '2011-07-01 00:00+00'::timestamptz 
        and valid < '2011-08-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_07_grid_idx on data_2011_07(grid_idx);
CREATE INDEX data_2011_07_valid_idx on data_2011_07(valid);
GRANT SELECT on data_2011_07 to nobody,apache;


 create table data_2011_08( 
  CONSTRAINT __data_2011_08_check 
  CHECK(valid >= '2011-08-01 00:00+00'::timestamptz 
        and valid < '2011-09-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_08_grid_idx on data_2011_08(grid_idx);
CREATE INDEX data_2011_08_valid_idx on data_2011_08(valid);
GRANT SELECT on data_2011_08 to nobody,apache;


 create table data_2011_09( 
  CONSTRAINT __data_2011_09_check 
  CHECK(valid >= '2011-09-01 00:00+00'::timestamptz 
        and valid < '2011-10-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_09_grid_idx on data_2011_09(grid_idx);
CREATE INDEX data_2011_09_valid_idx on data_2011_09(valid);
GRANT SELECT on data_2011_09 to nobody,apache;


 create table data_2011_10( 
  CONSTRAINT __data_2011_10_check 
  CHECK(valid >= '2011-10-01 00:00+00'::timestamptz 
        and valid < '2011-11-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_10_grid_idx on data_2011_10(grid_idx);
CREATE INDEX data_2011_10_valid_idx on data_2011_10(valid);
GRANT SELECT on data_2011_10 to nobody,apache;


 create table data_2011_11( 
  CONSTRAINT __data_2011_11_check 
  CHECK(valid >= '2011-11-01 00:00+00'::timestamptz 
        and valid < '2011-12-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_11_grid_idx on data_2011_11(grid_idx);
CREATE INDEX data_2011_11_valid_idx on data_2011_11(valid);
GRANT SELECT on data_2011_11 to nobody,apache;


 create table data_2011_12( 
  CONSTRAINT __data_2011_12_check 
  CHECK(valid >= '2011-12-01 00:00+00'::timestamptz 
        and valid < '2012-01-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2011_12_grid_idx on data_2011_12(grid_idx);
CREATE INDEX data_2011_12_valid_idx on data_2011_12(valid);
GRANT SELECT on data_2011_12 to nobody,apache;


 create table data_2012_01( 
  CONSTRAINT __data_2012_01_check 
  CHECK(valid >= '2012-01-01 00:00+00'::timestamptz 
        and valid < '2012-02-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_01_grid_idx on data_2012_01(grid_idx);
CREATE INDEX data_2012_01_valid_idx on data_2012_01(valid);
GRANT SELECT on data_2012_01 to nobody,apache;


 create table data_2012_02( 
  CONSTRAINT __data_2012_02_check 
  CHECK(valid >= '2012-02-01 00:00+00'::timestamptz 
        and valid < '2012-03-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_02_grid_idx on data_2012_02(grid_idx);
CREATE INDEX data_2012_02_valid_idx on data_2012_02(valid);
GRANT SELECT on data_2012_02 to nobody,apache;


 create table data_2012_03( 
  CONSTRAINT __data_2012_03_check 
  CHECK(valid >= '2012-03-01 00:00+00'::timestamptz 
        and valid < '2012-04-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_03_grid_idx on data_2012_03(grid_idx);
CREATE INDEX data_2012_03_valid_idx on data_2012_03(valid);
GRANT SELECT on data_2012_03 to nobody,apache;


 create table data_2012_04( 
  CONSTRAINT __data_2012_04_check 
  CHECK(valid >= '2012-04-01 00:00+00'::timestamptz 
        and valid < '2012-05-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_04_grid_idx on data_2012_04(grid_idx);
CREATE INDEX data_2012_04_valid_idx on data_2012_04(valid);
GRANT SELECT on data_2012_04 to nobody,apache;


 create table data_2012_05( 
  CONSTRAINT __data_2012_05_check 
  CHECK(valid >= '2012-05-01 00:00+00'::timestamptz 
        and valid < '2012-06-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_05_grid_idx on data_2012_05(grid_idx);
CREATE INDEX data_2012_05_valid_idx on data_2012_05(valid);
GRANT SELECT on data_2012_05 to nobody,apache;


 create table data_2012_06( 
  CONSTRAINT __data_2012_06_check 
  CHECK(valid >= '2012-06-01 00:00+00'::timestamptz 
        and valid < '2012-07-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_06_grid_idx on data_2012_06(grid_idx);
CREATE INDEX data_2012_06_valid_idx on data_2012_06(valid);
GRANT SELECT on data_2012_06 to nobody,apache;


 create table data_2012_07( 
  CONSTRAINT __data_2012_07_check 
  CHECK(valid >= '2012-07-01 00:00+00'::timestamptz 
        and valid < '2012-08-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_07_grid_idx on data_2012_07(grid_idx);
CREATE INDEX data_2012_07_valid_idx on data_2012_07(valid);
GRANT SELECT on data_2012_07 to nobody,apache;


 create table data_2012_08( 
  CONSTRAINT __data_2012_08_check 
  CHECK(valid >= '2012-08-01 00:00+00'::timestamptz 
        and valid < '2012-09-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_08_grid_idx on data_2012_08(grid_idx);
CREATE INDEX data_2012_08_valid_idx on data_2012_08(valid);
GRANT SELECT on data_2012_08 to nobody,apache;


 create table data_2012_09( 
  CONSTRAINT __data_2012_09_check 
  CHECK(valid >= '2012-09-01 00:00+00'::timestamptz 
        and valid < '2012-10-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_09_grid_idx on data_2012_09(grid_idx);
CREATE INDEX data_2012_09_valid_idx on data_2012_09(valid);
GRANT SELECT on data_2012_09 to nobody,apache;


 create table data_2012_10( 
  CONSTRAINT __data_2012_10_check 
  CHECK(valid >= '2012-10-01 00:00+00'::timestamptz 
        and valid < '2012-11-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_10_grid_idx on data_2012_10(grid_idx);
CREATE INDEX data_2012_10_valid_idx on data_2012_10(valid);
GRANT SELECT on data_2012_10 to nobody,apache;


 create table data_2012_11( 
  CONSTRAINT __data_2012_11_check 
  CHECK(valid >= '2012-11-01 00:00+00'::timestamptz 
        and valid < '2012-12-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_11_grid_idx on data_2012_11(grid_idx);
CREATE INDEX data_2012_11_valid_idx on data_2012_11(valid);
GRANT SELECT on data_2012_11 to nobody,apache;


 create table data_2012_12( 
  CONSTRAINT __data_2012_12_check 
  CHECK(valid >= '2012-12-01 00:00+00'::timestamptz 
        and valid < '2013-01-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2012_12_grid_idx on data_2012_12(grid_idx);
CREATE INDEX data_2012_12_valid_idx on data_2012_12(valid);
GRANT SELECT on data_2012_12 to nobody,apache;


 create table data_2013_01( 
  CONSTRAINT __data_2013_01_check 
  CHECK(valid >= '2013-01-01 00:00+00'::timestamptz 
        and valid < '2013-02-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_01_grid_idx on data_2013_01(grid_idx);
CREATE INDEX data_2013_01_valid_idx on data_2013_01(valid);
GRANT SELECT on data_2013_01 to nobody,apache;


 create table data_2013_02( 
  CONSTRAINT __data_2013_02_check 
  CHECK(valid >= '2013-02-01 00:00+00'::timestamptz 
        and valid < '2013-03-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_02_grid_idx on data_2013_02(grid_idx);
CREATE INDEX data_2013_02_valid_idx on data_2013_02(valid);
GRANT SELECT on data_2013_02 to nobody,apache;


 create table data_2013_03( 
  CONSTRAINT __data_2013_03_check 
  CHECK(valid >= '2013-03-01 00:00+00'::timestamptz 
        and valid < '2013-04-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_03_grid_idx on data_2013_03(grid_idx);
CREATE INDEX data_2013_03_valid_idx on data_2013_03(valid);
GRANT SELECT on data_2013_03 to nobody,apache;


 create table data_2013_04( 
  CONSTRAINT __data_2013_04_check 
  CHECK(valid >= '2013-04-01 00:00+00'::timestamptz 
        and valid < '2013-05-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_04_grid_idx on data_2013_04(grid_idx);
CREATE INDEX data_2013_04_valid_idx on data_2013_04(valid);
GRANT SELECT on data_2013_04 to nobody,apache;


 create table data_2013_05( 
  CONSTRAINT __data_2013_05_check 
  CHECK(valid >= '2013-05-01 00:00+00'::timestamptz 
        and valid < '2013-06-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_05_grid_idx on data_2013_05(grid_idx);
CREATE INDEX data_2013_05_valid_idx on data_2013_05(valid);
GRANT SELECT on data_2013_05 to nobody,apache;


 create table data_2013_06( 
  CONSTRAINT __data_2013_06_check 
  CHECK(valid >= '2013-06-01 00:00+00'::timestamptz 
        and valid < '2013-07-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_06_grid_idx on data_2013_06(grid_idx);
CREATE INDEX data_2013_06_valid_idx on data_2013_06(valid);
GRANT SELECT on data_2013_06 to nobody,apache;


 create table data_2013_07( 
  CONSTRAINT __data_2013_07_check 
  CHECK(valid >= '2013-07-01 00:00+00'::timestamptz 
        and valid < '2013-08-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_07_grid_idx on data_2013_07(grid_idx);
CREATE INDEX data_2013_07_valid_idx on data_2013_07(valid);
GRANT SELECT on data_2013_07 to nobody,apache;


 create table data_2013_08( 
  CONSTRAINT __data_2013_08_check 
  CHECK(valid >= '2013-08-01 00:00+00'::timestamptz 
        and valid < '2013-09-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_08_grid_idx on data_2013_08(grid_idx);
CREATE INDEX data_2013_08_valid_idx on data_2013_08(valid);
GRANT SELECT on data_2013_08 to nobody,apache;


 create table data_2013_09( 
  CONSTRAINT __data_2013_09_check 
  CHECK(valid >= '2013-09-01 00:00+00'::timestamptz 
        and valid < '2013-10-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_09_grid_idx on data_2013_09(grid_idx);
CREATE INDEX data_2013_09_valid_idx on data_2013_09(valid);
GRANT SELECT on data_2013_09 to nobody,apache;


 create table data_2013_10( 
  CONSTRAINT __data_2013_10_check 
  CHECK(valid >= '2013-10-01 00:00+00'::timestamptz 
        and valid < '2013-11-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_10_grid_idx on data_2013_10(grid_idx);
CREATE INDEX data_2013_10_valid_idx on data_2013_10(valid);
GRANT SELECT on data_2013_10 to nobody,apache;


 create table data_2013_11( 
  CONSTRAINT __data_2013_11_check 
  CHECK(valid >= '2013-11-01 00:00+00'::timestamptz 
        and valid < '2013-12-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_11_grid_idx on data_2013_11(grid_idx);
CREATE INDEX data_2013_11_valid_idx on data_2013_11(valid);
GRANT SELECT on data_2013_11 to nobody,apache;


 create table data_2013_12( 
  CONSTRAINT __data_2013_12_check 
  CHECK(valid >= '2013-12-01 00:00+00'::timestamptz 
        and valid < '2014-01-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2013_12_grid_idx on data_2013_12(grid_idx);
CREATE INDEX data_2013_12_valid_idx on data_2013_12(valid);
GRANT SELECT on data_2013_12 to nobody,apache;


 create table data_2014_01( 
  CONSTRAINT __data_2014_01_check 
  CHECK(valid >= '2014-01-01 00:00+00'::timestamptz 
        and valid < '2014-02-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_01_grid_idx on data_2014_01(grid_idx);
CREATE INDEX data_2014_01_valid_idx on data_2014_01(valid);
GRANT SELECT on data_2014_01 to nobody,apache;


 create table data_2014_02( 
  CONSTRAINT __data_2014_02_check 
  CHECK(valid >= '2014-02-01 00:00+00'::timestamptz 
        and valid < '2014-03-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_02_grid_idx on data_2014_02(grid_idx);
CREATE INDEX data_2014_02_valid_idx on data_2014_02(valid);
GRANT SELECT on data_2014_02 to nobody,apache;


 create table data_2014_03( 
  CONSTRAINT __data_2014_03_check 
  CHECK(valid >= '2014-03-01 00:00+00'::timestamptz 
        and valid < '2014-04-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_03_grid_idx on data_2014_03(grid_idx);
CREATE INDEX data_2014_03_valid_idx on data_2014_03(valid);
GRANT SELECT on data_2014_03 to nobody,apache;


 create table data_2014_04( 
  CONSTRAINT __data_2014_04_check 
  CHECK(valid >= '2014-04-01 00:00+00'::timestamptz 
        and valid < '2014-05-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_04_grid_idx on data_2014_04(grid_idx);
CREATE INDEX data_2014_04_valid_idx on data_2014_04(valid);
GRANT SELECT on data_2014_04 to nobody,apache;


 create table data_2014_05( 
  CONSTRAINT __data_2014_05_check 
  CHECK(valid >= '2014-05-01 00:00+00'::timestamptz 
        and valid < '2014-06-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_05_grid_idx on data_2014_05(grid_idx);
CREATE INDEX data_2014_05_valid_idx on data_2014_05(valid);
GRANT SELECT on data_2014_05 to nobody,apache;


 create table data_2014_06( 
  CONSTRAINT __data_2014_06_check 
  CHECK(valid >= '2014-06-01 00:00+00'::timestamptz 
        and valid < '2014-07-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_06_grid_idx on data_2014_06(grid_idx);
CREATE INDEX data_2014_06_valid_idx on data_2014_06(valid);
GRANT SELECT on data_2014_06 to nobody,apache;


 create table data_2014_07( 
  CONSTRAINT __data_2014_07_check 
  CHECK(valid >= '2014-07-01 00:00+00'::timestamptz 
        and valid < '2014-08-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_07_grid_idx on data_2014_07(grid_idx);
CREATE INDEX data_2014_07_valid_idx on data_2014_07(valid);
GRANT SELECT on data_2014_07 to nobody,apache;


 create table data_2014_08( 
  CONSTRAINT __data_2014_08_check 
  CHECK(valid >= '2014-08-01 00:00+00'::timestamptz 
        and valid < '2014-09-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_08_grid_idx on data_2014_08(grid_idx);
CREATE INDEX data_2014_08_valid_idx on data_2014_08(valid);
GRANT SELECT on data_2014_08 to nobody,apache;


 create table data_2014_09( 
  CONSTRAINT __data_2014_09_check 
  CHECK(valid >= '2014-09-01 00:00+00'::timestamptz 
        and valid < '2014-10-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_09_grid_idx on data_2014_09(grid_idx);
CREATE INDEX data_2014_09_valid_idx on data_2014_09(valid);
GRANT SELECT on data_2014_09 to nobody,apache;


 create table data_2014_10( 
  CONSTRAINT __data_2014_10_check 
  CHECK(valid >= '2014-10-01 00:00+00'::timestamptz 
        and valid < '2014-11-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_10_grid_idx on data_2014_10(grid_idx);
CREATE INDEX data_2014_10_valid_idx on data_2014_10(valid);
GRANT SELECT on data_2014_10 to nobody,apache;


 create table data_2014_11( 
  CONSTRAINT __data_2014_11_check 
  CHECK(valid >= '2014-11-01 00:00+00'::timestamptz 
        and valid < '2014-12-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_11_grid_idx on data_2014_11(grid_idx);
CREATE INDEX data_2014_11_valid_idx on data_2014_11(valid);
GRANT SELECT on data_2014_11 to nobody,apache;


 create table data_2014_12( 
  CONSTRAINT __data_2014_12_check 
  CHECK(valid >= '2014-12-01 00:00+00'::timestamptz 
        and valid < '2015-01-01 00:00+00')) 
  INHERITS (data);
CREATE INDEX data_2014_12_grid_idx on data_2014_12(grid_idx);
CREATE INDEX data_2014_12_valid_idx on data_2014_12(valid);
GRANT SELECT on data_2014_12 to nobody,apache;
 