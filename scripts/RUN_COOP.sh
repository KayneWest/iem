
cd current
python coop_precip.py
python coop_snow.py

cd ../outgoing
/mesonet/python/bin/python wxc_azos_gdd.py
php wxc_coop.php

cd ../coop
./PREC.csh
/mesonet/python/bin/python 12z_precip.py
