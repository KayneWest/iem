#!/bin/bash
# Cache the weather bureau's WWA map for folks to use
# http://mesonet.agron.iastate.edu/timemachine/#59.0

DSTAMP=$(date -u +'%Y%m%d%H%M')

wget -q --timeout=60 -O /tmp/wwa_${DSTAMP}.png http://forecast.weather.gov/wwamap/png/US.png

/home/ldm/bin/pqinsert -p "plot a ${DSTAMP} bogus wwa/wwa_${DSTAMP}.png png" /tmp/wwa_${DSTAMP}.png >& /dev/null

rm -f /tmp/wwa_${DSTAMP}.png >& /dev/null
exit 0