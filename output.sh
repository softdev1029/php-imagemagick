rm -rf ../doc/2-release.output-v1.zip
php main.php
zip -r ../doc/2-release/output-v1.zip csv_output/ image_converted/ image_origin image_origin_tmp/
sudo cp ../doc/2-release/output-v1.zip /mnt/share/

