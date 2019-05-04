rm -rf ../doc/2-release/output-v2.zip
php main.php
zip -r ../doc/2-release/output-v2.zip csv_output/ image_converted/ image_origin image_origin_tmp/ image_mockup/
sudo cp ../doc/2-release/output-v2.zip /mnt/share/

