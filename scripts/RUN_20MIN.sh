# Run every 20 minutes please

# Wait two minutes please
sleep 120
cd current
#python 24h_change.py

cd ../ingestors/rwis
#python process_clarus.py

cd ../madis
python to_iemaccess.py
python network2wxc.py APRSWXNET bogus

cd ../../snet
./RUN.csh
