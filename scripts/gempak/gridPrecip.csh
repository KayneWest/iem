#!/bin/csh

source /mesonet/nawips/Gemenviron

set mm=`date +%M`
if ($mm > 45) then
  set realmm="45"
else if ($mm > 30) then
  set realmm="30"
else if ($mm > 15) then
  set realmm="15"
else
  set realmm="00"
endif


set gdfile="/mesonet/data/gempak/precip/`date -u +'%Y%m%d'`_prec.grd"

if (! -e ${gdfile} ) then
  cp /mesonet/data/gempak/precip/template.grd ${gdfile}
endif

set tim0=`date -u +'%y%m%d/%H'`${realmm}

gdradr << EOF > /tmp/gdradr_precip.log
 GRDAREA  = IA+
 PROJ     = MER
 KXKY     = 380;250
 GDFILE   = ${gdfile}
 RADTIM   = ${tim0}
 GDPFUN   = N1P
 RADDUR   = 15
 RADFRQ   =
 CPYFIL   =
 MAXGRD   = 200
 RADMODE  = PC
 NDVAL    = 
 STNFIL   = nexrad.tbl
 list
 run

 GDPFUN	= NTP
 list
 run

 exit
EOF

./plotPrecip.csh
