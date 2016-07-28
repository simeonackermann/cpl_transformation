#!/bin/bash

# rm export_cpl.php?part*

# 21430
for i in {1..43} # all: 1..43
do
	echo -e "Part ${i}...\n-----------------------------------------------------------------------------"
	wget -O ./data/part-${i}.ttl http://localhost/cpl_transformation/index.php?part=${i}
	rapper -g -o ntriples ./data/part-${i}.ttl > ./data/part-${i}.nt  | grep Error
done


echo -e "\n-----------------------------------------------------------------------------\nDone!\n"

echo -e "Merging all Data to ./data/data.nt and ./data/data.ttl"

cat ./data/part-*.nt >> ./data/data_tmp.nt
sort -u ./data/data_tmp.nt > ./data/data.nt
rm ./data/data_tmp.nt
rapper -i ntriples -o turtle ./data/data.nt > ./data/data.ttl  | grep Error

echo -e "\n-----------------------------------------------------------------------------\n"

echo "Createing lipsiensium.ttl with lipsiensium model..."

cat ./lipsiensium_model.ttl ./data/data.ttl > ./lipsiensium.ttl